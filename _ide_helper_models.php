<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\File
 *
 * @property int $id
 * @property int $user_id
 * @property int $folder_id
 * @property string $name
 * @property string $storage_name
 * @property string $mime
 * @property int $size
 * @property \App\Enums\Permission|null $read
 * @property array|null $read_users
 * @property \App\Enums\Permission|null $write
 * @property array|null $write_users
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $fileCreatedBy
 * @property-read \App\Models\User $fileDeletedBy
 * @property-read \App\Models\User $fileIncludedBy
 * @property-read \App\Models\User $fileUpdatedBy
 * @property-read \App\Models\Folder $folder
 * @property-read string $download_url
 * @property-read string $human_size
 * @property-read string $key
 * @property-read string $path
 * @property-read \App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File newQuery()
 * @method static \Illuminate\Database\Query\Builder|File onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|File query()
 * @method static \Illuminate\Database\Eloquent\Builder|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereFolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereReadUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereStorageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereWrite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereWriteUsers($value)
 * @method static \Illuminate\Database\Query\Builder|File withTrashed()
 * @method static \Illuminate\Database\Query\Builder|File withoutTrashed()
 */
	class File extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Folder
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $folder_id
 * @property string $name
 * @property \App\Enums\Permission|null $read
 * @property array|null $read_users
 * @property \App\Enums\Permission|null $write
 * @property array|null $write_users
 * @property \App\Enums\Permission|null $upload
 * @property array|null $upload_users
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\File[] $files
 * @property-read int|null $files_count
 * @property-read \App\Models\User $folderCreatedBy
 * @property-read \App\Models\User $folderDeletedBy
 * @property-read \App\Models\User $folderIncludedBy
 * @property-read \App\Models\User $folderUpdatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection|Folder[] $folders
 * @property-read int|null $folders_count
 * @property-read string $key
 * @property-read string $path
 * @property-read \App\Models\User $owner
 * @property-read Folder|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|Folder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Folder newQuery()
 * @method static \Illuminate\Database\Query\Builder|Folder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Folder query()
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereFolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereReadUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereUpload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereUploadUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereWrite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Folder whereWriteUsers($value)
 * @method static \Illuminate\Database\Query\Builder|Folder withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Folder withoutTrashed()
 */
	class Folder extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

