<?php

namespace App\Http\Controllers\Api\V1\Attachment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Attachment\StoreAttachmentRequest;
use App\Http\Requests\Api\V1\Attachment\UpdateAttachmentRequest;
use App\Http\Resources\Api\V1\Attachment\AttachmentResource;
use App\Models\Attachment;
use App\Services\Attachment\AttachmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AttachmentController extends Controller implements HasMiddleware
{
    use ApiResponse;

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:show_attachments', only: ['index', 'show']),
            new Middleware('permission:create_attachments', only: ['store']),
            new Middleware('permission:update_attachments', only: ['update']),
            new Middleware('permission:delete_attachments', only: ['destroy']),
        ];
    }

    public function __construct(
        protected AttachmentService $attachmentService
    ) {}

    /**
     * Display a listing of attachments.
     */
    public function index(Request $request): JsonResponse
    {
        $attachments = $this->attachmentService->getAll($request->query());

        return $this->successResponse(
            AttachmentResource::collection($attachments),
            __('messages.attachment_list')
        );
    }

    /**
     * Store a newly created attachment.
     */
    public function store(StoreAttachmentRequest $request): JsonResponse
    {
        $attachment = $this->attachmentService->create(
            $request->validated(),
            $request->file('file'),
            $request->user()->id
        );

        return $this->successResponse(
            new AttachmentResource($attachment),
            __('messages.attachment_created'),
            201
        );
    }

    /**
     * Display the specified attachment.
     */
    public function show(Attachment $attachment): JsonResponse
    {
        $loadedAttachment = $this->attachmentService->getById($attachment);

        return $this->successResponse(
            new AttachmentResource($loadedAttachment),
            __('messages.attachment_retrieved')
        );
    }

    /**
     * Update the specified attachment.
     */
    public function update(UpdateAttachmentRequest $request, Attachment $attachment): JsonResponse
    {
        $updated = $this->attachmentService->update(
            $attachment,
            $request->validated(),
            $request->file('file')
        );

        return $this->successResponse(
            new AttachmentResource($updated),
            __('messages.attachment_updated')
        );
    }

    /**
     * Remove the specified attachment and file.
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        $this->attachmentService->delete($attachment);

        return $this->successResponse(
            null,
            __('messages.attachment_deleted')
        );
    }
}
