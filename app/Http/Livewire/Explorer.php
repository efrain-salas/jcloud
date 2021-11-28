<?php

namespace App\Http\Livewire;

use App\Models\File;
use App\Models\Folder;
use App\Services\StorageService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Explorer extends Component
{
    use WithFileUploads;

    public $folderId = null;

    public ?Folder $folder = null;
    public $uploadedFiles = null;
    public array $selected = [];
    public string $sortBy = 'name';
    public string $view = 'list';
    public bool $withTrashed = false;
    public $uploadQueue = [];

    // New Folder Modal
    public bool $showNewFolderModal = false;
    public ?string $newFolderName = null;

    // Rename Modal
    public bool $showRenameModal = false;
    public ?string $renameModalObjectKey = null;
    public ?string $renameModalNewName = null;

    // Permissions Modal
    public bool $showPermissionsModal = false;
    public ?string $permissionsModalObjectKey = null;
    public ?bool $permissionsModalIsFolder = null;
    public ?array $permissionsModalValues = null;

    protected $queryString = ['folderId'];

    protected $listeners = [
        'file-uploaded' => '$refresh',
        'folder-created' => '$refresh',
        'folder-opened' => '$refresh',
        'trashed-toggled' => '$refresh',
    ];

    public function mount()
    {
        if ($this->folderId) {
            $this->folder = Folder::withTrashed()->find($this->folderId);
        }
    }

    public function render()
    {
        return view('livewire.explorer');
    }

    public function list(): Collection
    {
        return (new StorageService())->list($this->folder, $this->sortBy, $this->withTrashed);
    }

    public function toggleSelect(string $objectKey)
    {
        $this->selected[$objectKey] = isset($this->selected[$objectKey]) ? ! $this->selected[$objectKey] : true;
    }

    public function isSelected(string $objectKey): bool
    {
        return $this->selected[$objectKey] ?? false;
    }

    public function getSelected(): array
    {
        return array_keys(array_filter($this->selected));
    }

    public function getSelectedCount(): int
    {
        return count($this->selected);
    }

    public function isAnySelected(): bool
    {
        return !! $this->getSelected();
    }

    public function selectAll()
    {
        $files = $this->list();
        $this->selected = $files->keyBy('key')->map(fn () => true)->all();
    }

    public function unselectAll()
    {
        $this->selected = [];
    }

    public function download()
    {
        $selected = $this->getSelected();
        if (count($selected) > 0) {
            if (count($selected) > 1) {
                $files = collect($selected)->map(fn ($objectKey) => object($objectKey));
                return (new StorageService())->archive($files);
            } else {
                $objectKey = $selected[0];
                $object = object($objectKey);

                if ($object->isFile()) {
                    $this->open($objectKey);
                } else {
                    return (new StorageService())->archive($object->list());
                }
            }
        }
    }

    public function open(string $objectKey = null)
    {
        if ($objectKey) {
            $object = object($objectKey);

            if ($object->isFile()) {
                $this->openFile($object);
            } else {
                $this->openFolder($object);
            }
        } else {
            $this->openFolder(null);
        }
    }

    public function openFile(File $file)
    {
        $this->redirect($file->download_url);
    }

    public function openFolder(?Folder $folder)
    {
        $this->folder = $folder;
        $this->folderId = $folder->id ?? null;

        $this->unselectAll();

        $this->emit('folder-opened');
    }

    public function toggleTrashed()
    {
        $this->withTrashed = ! $this->withTrashed;
        $this->unselectAll();
        $this->emit('trashed-toggled');
    }

    public function uploadFiles()
    {
        foreach ($this->uploadedFiles as $uploadedFile) {
            (new StorageService())->upload($this->folder, $uploadedFile);
        }

        $this->emit('file-uploaded');
    }

    public function addToQueue(string $fileName)
    {
        $this->uploadQueue[] = $fileName;
    }

    public function removeFromQueue(string $fileName)
    {
        $this->uploadQueue = collect($this->uploadQueue)->reject($fileName)->all();
    }

    public function openNewFolderModal()
    {
        $this->showNewFolderModal = true;
        $this->newFolderName = null;
    }

    public function closeNewFolderModal()
    {
        $this->showNewFolderModal = false;
    }

    public function createFolder()
    {
        $this->validate([
            'newFolderName' => ['required'],
        ]);

        (new StorageService())->createFolder($this->newFolderName, $this->folder);
        $this->showNewFolderModal = false;

        $this->emit('folder-created');
    }

    public function openPermissionsModal(string $objectKey)
    {
        $this->showPermissionsModal = true;
        $this->permissionsModalObjectKey = $objectKey;
        $object = object($objectKey);
        $this->permissionsModalIsFolder = $object->isFolder();

        $this->permissionsModalValues = (new StorageService())->getPermissions($object);
    }

    public function closePermissionsModal()
    {
        $this->showPermissionsModal = false;
    }

    public function updatePermissions()
    {
        $object = object($this->permissionsModalObjectKey);

        (new StorageService())->setPermissions($object, $this->permissionsModalValues);

        $this->closePermissionsModal();
    }

    public function openRenameModal(string $objectKey)
    {
        $this->showRenameModal = true;
        $this->renameModalObjectKey = $objectKey;
        $object = object($objectKey);
        $this->renameModalNewName = $object->name;
    }

    public function closeRenameModal()
    {
        $this->showRenameModal = false;
    }

    public function rename()
    {
        $this->validate([
            'renameModalNewName' => ['required'],
        ]);

        $object = object($this->renameModalObjectKey);
        (new StorageService())->rename($object, $this->renameModalNewName);

        $this->closeRenameModal();
    }

    public function getShareUrl(string $objectKey): string
    {
        $object = object($objectKey);
        return $object->getShareUrl(now()->addDays(7));
    }

    public function delete(string $objectKey)
    {
        (new StorageService())->delete(object($objectKey));
    }

    public function deleteSelected()
    {
        foreach ($this->getSelected() as $objectKey) {
            (new StorageService())->delete(object($objectKey));
        }

        $this->unselectAll();
    }

    public function restore(string $objectKey)
    {
        (new StorageService())->restore(object($objectKey));
    }
}
