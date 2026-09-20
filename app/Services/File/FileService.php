<?php

namespace App\Services\File;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Default storage disk.
     */
    protected string $defaultDisk = 'public';

    /**
     * 1. Upload a file to a specified directory path on the given disk.
     *
     * @param UploadedFile|File $file The file to store
     * @param string $path Target directory path (e.g. 'tasks/attachments')
     * @param string|null $disk The storage disk (defaults to 'public')
     * @return string Relative stored file path
     */
    public function upload(UploadedFile|File $file, string $path, ?string $disk = null): string
    {
        $disk = $disk ?? $this->defaultDisk;

        return $file->store($path, $disk);
    }

    /**
     * 2. Remove a file from the specified disk.
     *
     * @param string|null $path Relative path to the file
     * @param string|null $disk The storage disk (defaults to 'public')
     * @return bool True if deleted or did not exist, false if removal failed
     */
    public function remove(?string $path, ?string $disk = null): bool
    {
        if (empty($path)) {
            return false;
        }

        $disk = $disk ?? $this->defaultDisk;

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return true;
    }

    /**
     * 3. Replace an existing file with a new file using remove() and upload().
     *
     * @param UploadedFile|File $file The replacement file
     * @param string|null $oldPath The existing file path to remove
     * @param string $path Target directory path for the new file
     * @param string|null $disk The storage disk (defaults to 'public')
     * @return string Relative stored path of the new file
     */
    public function replace(UploadedFile|File $file, ?string $oldPath, string $path, ?string $disk = null): string
    {
        if (!empty($oldPath)) {
            $this->remove($oldPath, $disk);
        }

        return $this->upload($file, $path, $disk);
    }

    /**
     * Get the direct openable full URL for a stored file path.
     *
     * @param string|null $path
     * @param string|null $disk
     * @return string|null
     */
    public function url(?string $path, ?string $disk = null): ?string
    {
        if (empty($path)) {
            return null;
        }

        $disk = $disk ?? $this->defaultDisk;

        return Storage::disk($disk)->url($path);
    }
}
