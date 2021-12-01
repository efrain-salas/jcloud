<?php

namespace App\Services;

use App\Enums\Permission;
use App\Jobs\MoveToRemoteStorage;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Rolandstarke\Thumbnail\Facades\Thumbnail;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageService
{
    public function upload(Folder $folder, UploadedFile $uploadedFile, string $name = null): File
    {
        if (!$this->canUpload($folder)) {
            throw new \Exception('Insufficient permissions');
        }

        $name = $name ?: $uploadedFile->getClientOriginalName();
        $storageName = (string) Uuid::uuid4();

        $file = new File();
        $file->user_id = $folder->user_id;
        $file->created_by = auth()->id();

        $file->read = Permission::OWNER();
        $file->write = Permission::OWNER();

        $file->folder()->associate($folder);
        $file->name = $name;
        $file->storage_name = $storageName;
        $file->mime = $uploadedFile->getMimeType();
        $file->size = $uploadedFile->getSize();

        $file->save();

        MoveToRemoteStorage::dispatch($file, $uploadedFile->getRealPath());

        return $file;
    }

    public function delete(File|Folder $file)
    {
        if ($file instanceof File) {
            $this->deleteFile($file);
        } else {
            $this->deleteFolder($file);
        }
    }

    public function restore(File|Folder $file)
    {
        if ($file instanceof File) {
            $this->restoreFile($file);
        } else {
            $this->restoreFolder($file);
        }
    }

    public function deleteRealFile(File $file)
    {
        Storage::delete($file->storage_name);
    }

    public function renameFile(File $file, string $newName): File
    {
        return $this->rename($file, $newName);
    }

    public function renameFolder(Folder $folder, string $newName): Folder
    {
        return $this->rename($folder, $newName);
    }

    public function rename(File|Folder $file, string $newName): File|Folder
    {
        if (!$this->canWrite($file)) {
            throw new \Exception('Insufficient permissions');
        }

        $file->name = $newName;
        $file->save();

        return $file;
    }

    public function moveFile(File $file, Folder $newFolder): File
    {
        $file->folder()->associate($newFolder);
        $file->save();

        return $file;
    }

    public function deleteFile(File $file)
    {
        if (!$this->canWrite($file)) {
            throw new \Exception('Insufficient permissions');
        }

        $file->delete();
    }

    public function restoreFile(File $file)
    {
        $file->restore();
    }

    public function createFolder(string $name, ?Folder $parentFolder): Folder
    {
        if ($parentFolder && ! $this->canWrite($parentFolder)) {
            throw new \Exception('Insufficient permissions');
        }

        $folder = new Folder();
        $folder->user_id = $parentFolder ? $parentFolder->user_id : auth()->id();
        $folder->created_by = auth()->id();

        $folder->read = Permission::OWNER();
        $folder->write = Permission::OWNER();
        $folder->upload = Permission::OWNER();

        $folder->parent()->associate($parentFolder);
        $folder->name = $name;

        $folder->save();

        return $folder;
    }

    public function list(?Folder $folder, string $sortBy = 'name', bool $withTrashed = false): Collection
    {
        $sortBy = $sortBy == 'date' ? 'created_at' : $sortBy;
        $sortDirection = $sortBy == 'date' ? 'desc' : 'asc';

        if ($folder) {
            $childFolders = $folder->folders()->withTrashed($withTrashed)->orderBy($sortBy, $sortDirection)->get();
            $childFiles = $folder->files()->withTrashed($withTrashed)->orderBy($sortBy, $sortDirection)->get();
            $allChildren = collect()->merge($childFolders)->merge($childFiles);

            return $allChildren->filter(function (File|Folder $file) {
                return $file->canRead();
            });
        } else {
            $allFolders = Folder::query()->withTrashed($withTrashed)->get();

            $allWithPermissions = $allFolders->filter(function (Folder $folder) {
                return $folder->canRead();
            });

            // We filter out only those folders that do not have any folder higher up in the tree with access
            // permissions for this user. That is to say, those that do not have their parent folder among
            // the previous results.
            return $allWithPermissions->filter(function (Folder $folder) use ($allWithPermissions) {
                return $folder->folder_id == null || $allWithPermissions->where('id', $folder->folder_id)->isEmpty();
            });
        }
    }

    public function moveFolder(Folder $folder, Folder $newParentFolder): Folder
    {
        $folder->parent()->associate($newParentFolder);
        $folder->save();

        return $folder;
    }

    public function deleteFolder(Folder $folder)
    {
        if (!$this->canWrite($folder)) {
            throw new \Exception('Insufficient permissions');
        }

        $folder->folders->each(function (Folder $folder) {
            $this->deleteFolder($folder);
        });

        $folder->files->each(function (File $file) {
            $this->deleteFile($file);
        });

        $folder->delete();
    }

    public function restoreFolder(Folder $folder)
    {
        $folder->folders()->withTrashed()->get()->each(function (Folder $folder) {
            $this->restoreFolder($folder);
        });

        $folder->files()->withTrashed()->get()->each(function (File $file) {
            $this->restoreFile($file);
        });

        $folder->restore();
    }

    public function getPermissions(File|Folder $file): array
    {
        $values = [
            'read' => $file->read->value,
            'read_users' => $file->read_users ?: [],
            'write' => $file->write->value,
            'write_users' => $file->write_users ?: [],
        ];

        if ($file instanceof Folder) {
            $values = array_merge($values, [
                'upload' => $file->upload->value,
                'upload_users' => $file->upload_users ?: [],
            ]);
        }

        return $values;
    }

    public function setPermissions(File|Folder $file, array $permissions): File|Folder
    {
        $this->setReadPermission($file, Permission::from($permissions['read']), $permissions['read_users']);
        $this->setWritePermission($file, Permission::from($permissions['write']), $permissions['write_users']);

        if ($file instanceof Folder) {
            $this->setUploadPermission($file, Permission::from($permissions['upload']), $permissions['upload_users']);
        }

        Cache::tags('permissions')->flush();

        return $file;
    }

    public function setReadPermission(File|Folder $file, Permission $permission, ?array $userIds)
    {
        if (!$this->canEditPermissions($file)) {
            throw new \Exception('Insufficient permissions');
        }

        $file->read = $permission;
        $file->read_users = collect($userIds)->push(auth()->id())->map(fn ($id) => intval($id))->unique()->all();
        $file->save();
    }

    public function setWritePermission(File|Folder $file, Permission $permission, ?array $userIds)
    {
        if (!$this->canEditPermissions($file)) {
            throw new \Exception('Insufficient permissions');
        }

        $file->write = $permission;
        $file->write_users = collect($userIds)->push(auth()->id())->map(fn ($id) => intval($id))->unique()->all();
        $file->save();
    }

    public function setUploadPermission(Folder $folder, Permission $permission, ?array $userIds)
    {
        if (!$this->canEditPermissions($folder)) {
            throw new \Exception('Insufficient permissions');
        }

        $folder->upload = $permission;
        $folder->upload_users = collect($userIds)->push(auth()->id())->map(fn ($id) => intval($id))->unique()->all();
        $folder->save();
    }

    public function search(string $query): Collection
    {
        $files = File::search($query)->take(10)->get();
        $folders = Folder::search($query)->take(10)->get();
        return collect()->merge($files)->merge($folders);
    }

    public function getRootFolder(File|Folder $file): Folder
    {
        $root = $file;
        while ($root->isFile() || $root->parent) {
            $root = $root->isFile() ? $root->folder : $root->parent;
        }

        return $root;
    }

    public function getPath(File|Folder $file): string
    {
        if ($file instanceof File) {
            return $this->getPath($file->folder) . '/' . $file->name;
        } else {
            $folder = $file;
            return $folder->parent ? $this->getPath($folder->parent) . '/' . $folder->name : $folder->name;
        }
    }

    public function getDownloadUrl(File $file, ?Carbon $expiryDate = null, bool $inline = false): string
    {
        $expiryDate = $expiryDate ?: now()->addMinutes(5);
        $options = [
            'ResponseContentType' => $file->mime,
        ];

        if (!$inline) {
            $options['ResponseContentDisposition'] = 'attachment; filename="' . $file->name . '"';
        }

        return Storage::temporaryUrl($file->storage_name, $expiryDate, $options);
    }

    public function isImage(File $file): bool
    {
        return str_contains($file->mime, 'image');
    }

    public function getThumbnailUrl(File $file, int $size = 64): string
    {
        return Thumbnail::src($this->getDownloadUrl($file))->smartcrop($size, $size)->url();
    }

    public function archive(Collection $files, \ZipArchive $existingZip = null, string $zipPath = null): ?BinaryFileResponse
    {
        $zipPath = $zipPath ?: '';
        $tempFiles = [];

        if ($existingZip) {
            $zip = $existingZip;
        } else {
            $zipFilePath = tempnam(sys_get_temp_dir(), 'jcloud_archive_');
            $tempFiles[] = $zipFilePath;
            $zip = new \ZipArchive();
            $zip->open($zipFilePath);
        }

        foreach ($files as $file) {
            if ($file instanceof File) {
                $localFilePath = tempnam(sys_get_temp_dir(), 'jcloud_archive_file_');
                $tempFiles[] = $localFilePath;
                $stream = Storage::readStream($file->storage_name);
                file_put_contents($localFilePath, $stream);
                $zip->addFile($localFilePath, $zipPath . $file->name);
            } else if ($file instanceof Folder) {
                $folder = $file;
                $zip->addEmptyDir($folder->name);
                $this->archive($folder->list(), $zip, $zipPath . $folder->name . '/');
            }
        }

        if (!$existingZip) {
            $zip->close();
        }

        register_shutdown_function(function () use ($tempFiles) {
            foreach ($tempFiles as $tempFile) {
                unlink($tempFile);
            }
        });

        if ($existingZip) {
            return null;
        } else {
            return response()->download($zipFilePath, 'jCloud_' . now()->format('Y-m-d_H-i-s') . '.zip');
        }
    }

    public function canEditPermissions(File|Folder $file, ?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        return $file->user_id == $user->id;
    }

    public function canRead(File|Folder $file, ?User $user = null): bool
    {
        $user = $user ?: auth()->user();

        return Cache::tags(['permissions', $file->key, $user->key])
            ->rememberForever("canRead-$file->key-$user->key", function () use ($file, $user) {
                $folder = $file->isFile() ? $file->folder : $file;

                $canRead = (
                    $folder->owner->id == $user->id
                    || $folder->read->value >= Permission::ALL_USERS()->value
                    || ($folder->read == Permission::SOME_USERS() && in_array($user->id, $folder->read_users))
                );

                $canRead = $canRead ?: $this->canWrite($folder, $user);
                $canRead = $canRead ?: $this->canUpload($folder, $user);

                if (!$canRead) {
                    $parentFolder = $folder;
                    while (!$canRead && $parentFolder = $parentFolder->parent) {
                        $canRead = $this->canRead($parentFolder, $user);
                    }
                }

                return $canRead;
            });
    }

    public function canWrite(File|Folder $file, ?User $user = null): bool
    {
        $user = $user ?: auth()->user();

        return Cache::tags(['permissions', $file->key, $user->key])
            ->rememberForever("canWrite-$file->key-$user->key", function () use ($file, $user) {
                $folder = $file->isFile() ? $file->folder : $file;

                $canWrite = (
                    $folder->owner->id == $user->id
                    || $folder->write->value >= Permission::ALL_USERS()->value
                    || ($folder->write == Permission::SOME_USERS() && in_array($user->id, $folder->write_users))
                );

                if (!$canWrite) {
                    $parentFolder = $folder;
                    while (!$canWrite && $parentFolder = $parentFolder->parent) {
                        $canWrite = $this->canWrite($parentFolder, $user);
                    }
                }

                return $canWrite;
            });
    }

    public function canUpload(Folder $folder, ?User $user = null): bool
    {
        $user = $user ?: auth()->user();

        return Cache::tags(['permissions', $folder->key, $user->key])
            ->rememberForever("canUpload-$folder->key-$user->key", function () use ($folder, $user) {
                $canUpload = (
                    $folder->owner->id == $user->id
                    || $folder->upload->value >= Permission::ALL_USERS()->value
                    || ($folder->upload == Permission::SOME_USERS() && in_array($user->id, $folder->upload_users))
                );

                $canUpload = $canUpload ?: $this->canWrite($folder, $user);

                if (!$canUpload) {
                    $parentFolder = $folder;
                    while (!$canUpload && $parentFolder = $parentFolder->parent) {
                        $canUpload = $this->canUpload($parentFolder, $user);
                    }
                }

                return $canUpload;
            });
    }
}
