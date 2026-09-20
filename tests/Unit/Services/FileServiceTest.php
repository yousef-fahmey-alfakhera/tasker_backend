<?php

namespace Tests\Unit\Services;

use App\Services\File\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileServiceTest extends TestCase
{
    protected FileService $fileService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->fileService = new FileService();
    }

    public function test_upload_stores_file_and_returns_path(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $storedPath = $this->fileService->upload($file, 'tasks/attachments');

        $this->assertNotEmpty($storedPath);
        $this->assertStringStartsWith('tasks/attachments/', $storedPath);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_remove_deletes_file_from_disk(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');
        $storedPath = $this->fileService->upload($file, 'uploads');

        Storage::disk('public')->assertExists($storedPath);

        $removed = $this->fileService->remove($storedPath);

        $this->assertTrue($removed);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_replace_removes_old_file_and_uploads_new_file(): void
    {
        $oldFile = UploadedFile::fake()->create('old.txt', 100);
        $oldPath = $this->fileService->upload($oldFile, 'docs');

        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->create('new.txt', 200);
        $newPath = $this->fileService->replace($newFile, $oldPath, 'docs');

        $this->assertNotEquals($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_url_returns_direct_openable_url(): void
    {
        $file = UploadedFile::fake()->image('preview.png');
        $storedPath = $this->fileService->upload($file, 'images');

        $url = $this->fileService->url($storedPath);

        $this->assertNotNull($url);
        $this->assertStringContainsString('/storage/' . $storedPath, $url);
    }
}
