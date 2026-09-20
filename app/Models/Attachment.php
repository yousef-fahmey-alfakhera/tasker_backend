<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'file',
        'path',
        'type',
        'size',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    /**
     * Morph relation to owning model (Task, etc.).
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Creator (user) of the attachment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the direct openable full URL for the file.
     */
    public function getFilePath(): ?string
    {
        if (empty($this->path)) {
            return null;
        }

        return Storage::disk('public')->url($this->path);
    }

    /**
     * Accessor for full file path / URL.
     */
    protected function filePath(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFilePath()
        );
    }

    /**
     * Backwards-compatible accessor for file_path.
     */
    public function getFilePathAttribute(): ?string
    {
        return $this->getFilePath();
    }
}
