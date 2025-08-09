<?php

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;

class File extends Model
{
    use HasCreatorAndUpdater, NodeTrait, SoftDeletes;

    protected $fillable = ['parent_id', 'name', 'path', 'size', 'created_by', 'updated_by', '_lft', '_rgt', 'is_folder', 'mime', 'storage_path'];

    protected $appends = ['file_size'];

    public function isOwnedBy(): bool
    {
        return $this->created_by === Auth::id();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(File::class, 'parent_id');
    }

    protected function fileSize(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatSize($this->size),
        );
    }

    protected function formatSize(?int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if (is_null($size) || $size === 0) {
            return '0 B';
        }

        $power = floor(log($size, 1024));
        $power = min($power, count($units) - 1);

        return number_format($size / pow(1024, $power), 2, '.', ' ').' '.$units[$power];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function owner(): Attribute
    {
        return Attribute::make(
            get: fn (null $model, array $attributes) => $attributes['created_by'] === Auth::id() ? 'me' : $this->user->name,
        );
    }

    private static function deleteStorageFiles($children): void
    {
        foreach ($children as $child) {
            if ($child->is_folder) {
                static::deleteStorageFiles($child->children()->withTrashed()->get());
            } else {
                Storage::delete($child->storage_path);
            }
        }
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (File $file) {

            if (! $file->parent) {
                return;
            }

            $file->path = (! $file->parent->isRoot() ? $file->parent->path.'/' : '').Str::slug($file->name);
        });

        static::deleted(static function (File $file) {
            if (! $file->is_folder) {
                if (Storage::exists($file->storage_path)) {
                    Storage::delete($file->storage_path);
                }
            }
        });
    }
}
