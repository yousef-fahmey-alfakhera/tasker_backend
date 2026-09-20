<?php

namespace Tests\Feature\Api\V1\Attachment;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        $project = Project::create([
            'name'       => 'Attachment Project',
            'created_by' => $this->user->id,
        ]);

        $workspace = Workspace::create([
            'project_id' => $project->id,
            'name'       => 'Attachment Workspace',
            'created_by' => $this->user->id,
        ]);

        $status = TaskStatus::create([
            'name'  => 'Backlog',
            'stage' => 'pending',
            'order' => 1,
        ]);

        $this->task = Task::create([
            'created_by'   => $this->user->id,
            'project_id'   => $project->id,
            'workspace_id' => $workspace->id,
            'status_id'    => $status->id,
            'title'        => 'Attachment Feature Task',
            'position'     => 1,
        ]);
    }

    public function test_guest_cannot_access_attachments(): void
    {
        $response = $this->getJson('/api/attachments');
        $response->assertStatus(401);
    }

    public function test_store_validation_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/attachments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file', 'attachable_type', 'attachable_id']);
    }

    public function test_user_can_upload_attachment_for_task(): void
    {
        $file = UploadedFile::fake()->create('specification.pdf', 2048, 'application/pdf');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/attachments', [
                'file'            => $file,
                'attachable_type' => 'task',
                'attachable_id'   => $this->task->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'attachable_type',
                    'attachable_id',
                    'file',
                    'path',
                    'file_path',
                    'type',
                    'size',
                    'created_by',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals($this->task->id, $data['attachable_id']);
        $this->assertEquals(Task::class, $data['attachable_type']);
        $this->assertEquals('specification.pdf', $data['file']);
        $this->assertStringStartsWith(Task::ATTACHMENT_PATH . '/', $data['path']);

        // Direct openable URL check
        $this->assertNotEmpty($data['file_path']);
        $this->assertStringContainsString('/storage/' . $data['path'], $data['file_path']);

        // Assert file exists on public disk
        Storage::disk('public')->assertExists($data['path']);

        // Assert relation on Task model
        $this->assertEquals(1, $this->task->attachments()->count());
    }

    public function test_user_can_show_attachment_with_direct_path(): void
    {
        $file = UploadedFile::fake()->image('screenshot.png');
        $storedPath = $file->store(Task::ATTACHMENT_PATH, 'public');

        $attachment = Attachment::create([
            'attachable_type' => Task::class,
            'attachable_id'   => $this->task->id,
            'file'            => 'screenshot.png',
            'path'            => $storedPath,
            'type'            => 'image/png',
            'size'            => 1024,
            'created_by'      => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/attachments/' . $attachment->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'id'        => $attachment->id,
                    'file'      => 'screenshot.png',
                    'file_path' => $attachment->getFilePath(),
                ],
            ]);
    }

    public function test_user_can_filter_attachments_list(): void
    {
        $file1 = UploadedFile::fake()->create('doc1.pdf', 500);
        $path1 = $file1->store(Task::ATTACHMENT_PATH, 'public');

        Attachment::create([
            'attachable_type' => Task::class,
            'attachable_id'   => $this->task->id,
            'file'            => 'doc1.pdf',
            'path'            => $path1,
            'type'            => 'application/pdf',
            'size'            => 500,
            'created_by'      => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/attachments?attachable_type=task&attachable_id=' . $this->task->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_user_can_update_and_replace_attachment_file(): void
    {
        $oldFile = UploadedFile::fake()->create('v1.pdf', 500);
        $oldPath = $oldFile->store(Task::ATTACHMENT_PATH, 'public');

        $attachment = Attachment::create([
            'attachable_type' => Task::class,
            'attachable_id'   => $this->task->id,
            'file'            => 'v1.pdf',
            'path'            => $oldPath,
            'type'            => 'application/pdf',
            'size'            => 500,
            'created_by'      => $this->user->id,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->create('v2_revised.pdf', 800, 'application/pdf');

        // Test multipart update via POST /api/attachments/{id}
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/attachments/' . $attachment->id, [
                'file' => $newFile,
            ]);

        $response->assertStatus(200);
        $updatedData = $response->json('data');

        $this->assertEquals('v2_revised.pdf', $updatedData['file']);
        $this->assertNotEquals($oldPath, $updatedData['path']);

        // Old file must be removed, new file must exist
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($updatedData['path']);
    }

    public function test_user_can_delete_attachment_and_remove_file(): void
    {
        $file = UploadedFile::fake()->image('delete_me.png');
        $storedPath = $file->store(Task::ATTACHMENT_PATH, 'public');

        $attachment = Attachment::create([
            'attachable_type' => Task::class,
            'attachable_id'   => $this->task->id,
            'file'            => 'delete_me.png',
            'path'            => $storedPath,
            'type'            => 'image/png',
            'size'            => 1024,
            'created_by'      => $this->user->id,
        ]);

        Storage::disk('public')->assertExists($storedPath);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/attachments/' . $attachment->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attachment deleted successfully.',
            ]);

        // Database record soft-deleted
        $this->assertSoftDeleted('attachments', ['id' => $attachment->id]);

        // Physical file removed from disk
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_bilingual_arabic_response_for_attachments(): void
    {
        $file = UploadedFile::fake()->create('arabic_doc.pdf', 300);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeader('Accept-Language', 'ar')
            ->postJson('/api/attachments', [
                'file'            => $file,
                'attachable_type' => 'task',
                'attachable_id'   => $this->task->id,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'تم رفع المرفق بنجاح.',
            ]);
    }
}
