<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AttachmentController extends ApiController
{
    public function getModel(): string
    {
        return Models::attachment();
    }

    /**
     * Upload a file attachment.
     *
     * POST /api/helpdesk/attachments
     */
    public function store(Request $request): Response
    {
        if (! config('helpdesk.attachments.enabled')) {
            return $this->buildResponse([
                'error' => ['message' => 'Attachments are disabled', 'status' => 403],
            ], $request, Response::HTTP_FORBIDDEN);
        }

        $maxSizeKb = config('helpdesk.attachments.max_size_kb', 10240);
        $allowedMimes = config('helpdesk.attachments.allowed_mimes', []);

        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'max:'.$maxSizeKb,
                'mimes:'.implode(',', $allowedMimes),
            ],
            'attachable_type' => ['required', 'string', 'in:ticket,comment'],
            'attachable_id' => ['required', 'integer'],
        ]);

        $attachableClass = $validated['attachable_type'] === 'ticket'
            ? Models::ticket()
            : Models::comment();

        $attachable = $attachableClass::find($validated['attachable_id']);

        if (! $attachable) {
            return $this->buildResponse([
                'error' => ['message' => 'Attachable record not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $file = $request->file('file');
        $disk = config('helpdesk.attachments.disk', 'local');
        $path = config('helpdesk.attachments.path', 'helpdesk-attachments');

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $filePath = $file->storeAs($path, $filename, $disk);

        $modelClass = $this->getModel();

        $attachment = $modelClass::create([
            'attachable_type' => $attachableClass,
            'attachable_id' => $validated['attachable_id'],
            'user_id' => $request->user()?->id,
            'filename' => $filePath,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $disk,
        ]);

        return $this->buildResponse($attachment->toArray(), $request, Response::HTTP_CREATED);
    }

    /**
     * Get attachment info.
     *
     * GET /api/helpdesk/attachments/{id}
     */
    public function show(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();
        $attachment = $modelClass::with(['attachable', 'user'])->find($id);

        if (! $attachment) {
            return $this->buildResponse([
                'error' => ['message' => 'Attachment not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        if (! $this->canAccessAttachment($request, $attachment)) {
            return $this->buildResponse([
                'error' => ['message' => 'Unauthorized', 'status' => 403],
            ], $request, Response::HTTP_FORBIDDEN);
        }

        return $this->buildResponse($attachment->toArray(), $request, Response::HTTP_OK);
    }

    /**
     * Download an attachment file.
     *
     * GET /api/helpdesk/attachments/{id}/download
     */
    public function download(Request $request, string $id): mixed
    {
        $modelClass = $this->getModel();
        $attachment = $modelClass::with('attachable')->find($id);

        if (! $attachment) {
            return $this->buildResponse([
                'error' => ['message' => 'Attachment not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        if (! $this->canAccessAttachment($request, $attachment)) {
            return $this->buildResponse([
                'error' => ['message' => 'Unauthorized', 'status' => 403],
            ], $request, Response::HTTP_FORBIDDEN);
        }

        $disk = Storage::disk($attachment->disk);

        if (! $disk->exists($attachment->filename)) {
            return $this->buildResponse([
                'error' => ['message' => 'Attachment file not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        return $disk->download($attachment->filename, $attachment->original_filename);
    }

    /**
     * Delete an attachment.
     *
     * DELETE /api/helpdesk/attachments/{id}
     */
    public function destroy(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();
        $attachment = $modelClass::find($id);

        if (! $attachment) {
            return $this->buildResponse([
                'error' => ['message' => 'Attachment not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        if (! $this->canAccessAttachment($request, $attachment)) {
            return $this->buildResponse([
                'error' => ['message' => 'Unauthorized', 'status' => 403],
            ], $request, Response::HTTP_FORBIDDEN);
        }

        // Delete file from storage.
        if (Storage::disk($attachment->disk)->exists($attachment->filename)) {
            Storage::disk($attachment->disk)->delete($attachment->filename);
        }

        $attachment->delete();

        return $this->buildResponse(['message' => 'Attachment deleted'], $request, Response::HTTP_OK);
    }

    protected function canAccessAttachment(Request $request, Model $attachment): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        $attachable = $attachment->attachable;

        if (! $attachable) {
            return false;
        }

        $ticketClass = Models::ticket();
        $commentClass = Models::comment();

        if ($attachable instanceof $ticketClass) {
            return $this->canAccessTicket($attachable, $user->id);
        }

        if ($attachable instanceof $commentClass) {
            if ($attachable->is_private && (int) $attachable->user_id !== (int) $user->id) {
                return $attachable->ticket?->assignees()->where('user_id', $user->id)->exists() ?? false;
            }

            return $this->canAccessTicket($attachable->ticket, $user->id);
        }

        return false;
    }

    protected function canAccessTicket(?Model $ticket, int|string $userId): bool
    {
        if (! $ticket) {
            return false;
        }

        return (int) $ticket->submitter_id === (int) $userId
            || $ticket->assignees()->where('user_id', $userId)->exists()
            || $ticket->watchers()->where('user_id', $userId)->exists();
    }
}
