<?php

namespace App\Models;

use App\Enums\Permission;
use App\Services\StorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

class Folder extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Searchable;
    use Prunable;

    protected $casts = [
        'read' => Permission::class,
        'read_users' => 'array',
        'write' => Permission::class,
        'write_users' => 'array',
        'upload' => Permission::class,
        'upload_users' => 'array',
        'folder_created_at' => 'datetime',
        'folder_updated_at' => 'datetime',
        'folder_deleted_at' => 'datetime',
        'folder_included_at' => 'datetime',
    ];

    public function prunable()
    {
        return static::where('deleted_at', '<=', now()->subMonth());
    }

    // Relationships

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function folderCreatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'folder_created_by');
    }

    public function folderUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'folder_updated_by');
    }

    public function folderDeletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'folder_deleted_by');
    }

    public function folderIncludedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'folder_included_by');
    }

    // Accessors & Mutators

    public function getKeyAttribute(): string
    {
        return 'folder_' . $this->id;
    }

    public function getPathAttribute(): string
    {
        return (new StorageService())->getPath($this);
    }

    // Helpers

    public function isFile(): bool { return false; }
    public function isFolder(): bool { return true; }

    public function isRootFolder(): bool
    {
        return ! $this->parent;
    }

    public function list(string $sortBy = 'name'): Collection
    {
        return (new StorageService())->list($this, $sortBy);
    }

    public function breadcrumbs(): array
    {
        $crumbs = [];

        $crumbs[$this->key] = $this->name;
        $folder = $this;

        while ($folder = $folder->parent) {
            $crumbs[$folder->key] = $folder->name;
        }

        return array_reverse($crumbs);
    }

    public function canRead(?User $user = null): bool
    {
        return (new StorageService())->canRead($this, $user);
    }

    public function canWrite(?User $user = null): bool
    {
        return (new StorageService())->canWrite($this, $user);
    }

    public function canUpload(?User $user = null): bool
    {
        return (new StorageService())->canUpload($this, $user);
    }

    public function canEditPermissions(?User $user = null): bool
    {
        return (new StorageService())->canEditPermissions($this, $user);
    }

    // Scout

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
