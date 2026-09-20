<?php

namespace App\Services\Attachment;

use App\Models\Attachment;
use App\Models\Task;
use App\Services\File\FileService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class AttachmentService
{
    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * Get all attachments with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Attachment::with('creator')->latest();

        if (!empty($filters['attachable_type'])) {
            $type = $this->resolveAttachableClass($filters['attachable_type']);
            $query->where('attachable_type', $type);
        }

        if (!empty($filters['attachable_id'])) {
            $query->where('attachable_id', $filters['attachable_id']);
        }

        return $query->get();
    }

    /**
     * Get attachment by instance.
     */
    public function getById(Attachment $attachment): Attachment
    {
        return $attachment->loadMissing('creator');
    }

    /**
     * Create an attachment record and upload file.
     */
    public function create(array $data, UploadedFile $file, int $userId): Attachment
    {
        $attachableType = $this->resolveAttachableClass($data['attachable_type']);
        $targetPath = $this->determineStoragePath($attachableType);

        $storedPath = $this->fileService->upload($file, $targetPath);

        $attachment = Attachment::create([
            'attachable_type' => $attachableType,
            'attachable_id'   => $data['attachable_id'],
            'file'            => $file->getClientOriginalName(),
            'path'            => $storedPath,
            'type'            => $file->getClientMimeType() ?: ($file->getClientOriginalExtension() ?: 'unknown'),
            'size'            => $file->getSize() ?: 0,
            'created_by'      => $userId,
        ]);

        return $attachment->loadMissing('creator');
    }

    /**
     * Convenience method to store an attachment directly for a Task using Task::ATTACHMENT_PATH.
     */
    public function storeForTask(Task $task, UploadedFile $file, int $userId): Attachment
    {
        $storedPath = $this->fileService->upload($file, Task::ATTACHMENT_PATH);

        return $task->attachments()->create([
            'file'       => $file->getClientOriginalName(),
            'path'       => $storedPath,
            'type'       => $file->getClientMimeType() ?: ($file->getClientOriginalExtension() ?: 'unknown'),
            'size'       => $file->getSize() ?: 0,
            'created_by' => $userId,
        ]);
    }

    /**
     * Update attachment attributes and optionally replace the file.
     */
    public function update(Attachment $attachment, array $data, ?UploadedFile $file = null): Attachment
    {
        $updateData = [];

        if (isset($data['attachable_type'])) {
            $updateData['attachable_type'] = $this->resolveAttachableClass($data['attachable_type']);
        }

        if (isset($data['attachable_id'])) {
            $updateData['attachable_id'] = $data['attachable_id'];
        }

        if ($file instanceof UploadedFile) {
            $targetType = $updateData['attachable_type'] ?? $attachment->attachable_type;
            $targetPath = $this->determineStoragePath($targetType);

            $newPath = $this->fileService->replace($file, $attachment->path, $targetPath);

            $updateData['path'] = $newPath;
            $updateData['file'] = $file->getClientOriginalName();
            $updateData['type'] = $file->getClientMimeType() ?: ($file->getClientOriginalExtension() ?: 'unknown');
            $updateData['size'] = $file->getSize() ?: 0;
        }

        $attachment->update($updateData);

        return $attachment->fresh('creator');
    }

    /**
     * Delete attachment and remove physical file from disk.
     */
    public function delete(Attachment $attachment): bool
    {
        $this->fileService->remove($attachment->path);

        return (bool) $attachment->delete();
    }

    /**
     * Resolve string alias to full model class.
     */
    public function resolveAttachableClass(string $type): string
    {
        return match (strtolower($type)) {
            'task', 'tasks', Task::class => Task::class,
            default => $type,
        };
    }

    /**
     * Determine storage path based on attachable class.
     */
    public function determineStoragePath(string $attachableType): string
    {
        if ($attachableType === Task::class) {
            return Task::ATTACHMENT_PATH;
        }

        $baseName = class_basename($attachableType);
        return 'attachments/' . strtolower($baseName);
    }
}
