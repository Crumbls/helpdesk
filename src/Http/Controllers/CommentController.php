<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends ApiController
{
    public function getModel(): string
    {
        return Models::comment();
    }

    /**
     * Display a listing of comments.
     * Supports filtering by ticket_id query parameter.
     */
    public function index(Request $request): Response
    {
        $modelClass = $this->getModel();

        $query = $modelClass::query()->with('user');

        if ($request->has('ticket_id')) {
            $query->where('ticket_id', $request->input('ticket_id'));
        }

        return $this->buildResponse($query->paginate()->toArray(), $request, 200);
    }

    /**
     * Display the specified comment.
     */
    public function show(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();

        $record = $modelClass::with('user')->find($id);

        if (!$record) {
            return $this->buildResponse([
                'error' => [
                    'message' => 'Record not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($record->toArray(), $request, 200);
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'ticket_id' => ['required', 'exists:helpdesk_tickets,id'],
            'user_id' => ['required', 'exists:users,id'],
            'content' => ['required', 'string'],
            'is_private' => ['boolean'],
            'is_resolution' => ['boolean'],
        ]);

        $modelClass = $this->getModel();

        if (!empty($validated['is_resolution'])) {
            $modelClass::where('ticket_id', $validated['ticket_id'])
                ->where('is_resolution', true)
                ->update(['is_resolution' => false]);
        }

        $record = $modelClass::create($validated);

        return $this->buildResponse($record->load('user')->toArray(), $request, Response::HTTP_CREATED);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();

        $record = $modelClass::find($id);

        if (!$record) {
            return $this->buildResponse([
                'error' => [
                    'message' => 'Record not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'ticket_id' => ['sometimes', 'required', 'exists:helpdesk_tickets,id'],
            'user_id' => ['sometimes', 'required', 'exists:users,id'],
            'content' => ['sometimes', 'required', 'string'],
            'is_private' => ['sometimes', 'boolean'],
            'is_resolution' => ['sometimes', 'boolean'],
        ]);

        $ticketId = $validated['ticket_id'] ?? $record->ticket_id;

        if (!empty($validated['is_resolution'])) {
            $modelClass::where('ticket_id', $ticketId)
                ->where('is_resolution', true)
                ->where('id', '!=', $record->id)
                ->update(['is_resolution' => false]);
        }

        $record->update($validated);

        return $this->buildResponse($record->fresh()->load('user')->toArray(), $request, 200);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();

        $record = $modelClass::find($id);

        if (!$record) {
            return $this->buildResponse([
                'error' => [
                    'message' => 'Record not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $record->delete();

        return $this->buildResponse(['message' => 'Comment deleted'], $request, 200);
    }
}
