<?php

namespace App\Models;

use App\Enums\Permission;
use App\Services\StorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

class File extends Model
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
        'file_created_at' => 'datetime',
        'file_updated_at' => 'datetime',
        'file_deleted_at' => 'datetime',
        'file_included_at' => 'datetime',
    ];

    public function prunable()
    {
        return static::where('deleted_at', '<=', now()->subMonth());
    }

    protected function pruning()
    {
        (new StorageService())->deleteRealFile($this);
    }

    // Relationships

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function fileCreatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'file_created_by');
    }

    public function fileUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'file_updated_by');
    }

    public function fileDeletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'file_deleted_by');
    }

    public function fileIncludedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'file_included_by');
    }

    // Accessors & Mutators

    public function getKeyAttribute(): string
    {
        return 'file_' . $this->id;
    }

    public function getPathAttribute(): string
    {
        return (new StorageService())->getPath($this);
    }

    public function getDownloadUrlAttribute(): string
    {
        return (new StorageService())->getDownloadUrl($this);
    }

    public function getHumanSizeAttribute(): string
    {
        $precision = 0;
        $base = log($this->size) / log(1024);
        $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    // Helpers

    public function isFile(): bool { return true; }
    public function isFolder(): bool { return false; }

    public function getShareUrl(Carbon $expiryDate = null): string
    {
        return (new StorageService())->getDownloadUrl($this, $expiryDate);
    }

    public function canRead(?User $user = null): bool
    {
        return (new StorageService())->canRead($this, $user);
    }

    public function canWrite(?User $user = null): bool
    {
        return (new StorageService())->canWrite($this, $user);
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
