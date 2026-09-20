<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    public const ATTACHMENT_PATH = 'tasks/attachments';

    protected $fillable = [
        'created_by',
        'fixed_by',
        'title',
        'description',
        'priority',
        'parent_task_id',
        'project_id',
        'workspace_id',
        'status_id',
        'task_type_id',
        'start_date',
        'due_date',
        'position',
        'working_at',
        'completed_at',
        'actual_minutes',
    ];

    protected function casts(): array
    {
        return [
            'start_date'     => 'datetime',
            'due_date'       => 'datetime',
            'working_at'     => 'datetime',
            'completed_at'   => 'datetime',
            'actual_minutes' => 'integer',
            'position'       => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fixedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fixed_by');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
