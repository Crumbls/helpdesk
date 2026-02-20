<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends ApiController
{
    public function getModel(): string
    {
        return Models::ticket();
    }

    /**
     * Store a newly created ticket.
     *
     * @see docs/api-tickets.md for curl examples
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'ticket_type_id' => ['nullable', 'integer', 'exists:helpdesk_ticket_types,id'],
            'ticket_status_id' => ['nullable', 'integer', 'exists:helpdesk_ticket_statuses,id'],
            'submitter_id' => ['required', 'integer', 'exists:users,id'],
            'submitter_name' => ['nullable', 'string', 'max:255'],
            'submitter_email' => ['nullable', 'email', 'max:255'],
            'submitter_phone' => ['nullable', 'string', 'max:50'],
            'submitter_company' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:helpdesk_departments,id'],
            'priority_id' => ['nullable', 'integer', 'exists:helpdesk_priorities,id'],
            'parent_ticket_id' => ['nullable', 'integer', 'exists:helpdesk_tickets,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'resolution' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'due_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
        ]);

        if (empty($validated['ticket_status_id'])) {
            $statusClass = Models::status();
            $defaultStatus = $statusClass::where('is_default', true)->first();

            if (!$defaultStatus) {
                return $this->buildResponse([
                    'error' => [
                        'message' => 'No default status configured',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    ],
                ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated['ticket_status_id'] = $defaultStatus->id;
        }

        if (empty($validated['ticket_type_id'])) {
            $typeClass = Models::type();
            $defaultType = $typeClass::where('is_default', true)->first();

            if (!$defaultType) {
                return $this->buildResponse([
                    'error' => [
                        'message' => 'No default type configured',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    ],
                ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated['ticket_type_id'] = $defaultType->id;
        }

        $modelClass = $this->getModel();

        $record = $modelClass::create($validated);

        return $this->buildResponse($record->toArray(), $request, Response::HTTP_CREATED);
    }

    /**
     * Update the specified ticket.
     *
     * @see docs/api-tickets.md for curl examples
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
            'ticket_type_id' => ['sometimes', 'required', 'integer', 'exists:helpdesk_ticket_types,id'],
            'ticket_status_id' => ['sometimes', 'required', 'integer', 'exists:helpdesk_ticket_statuses,id'],
            'submitter_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'department_id' => ['nullable', 'integer', 'exists:helpdesk_departments,id'],
            'priority_id' => ['nullable', 'integer', 'exists:helpdesk_priorities,id'],
            'parent_ticket_id' => ['nullable', 'integer', 'exists:helpdesk_tickets,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'resolution' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:255'],
            'due_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
        ]);

        $record->update($validated);

        return $this->buildResponse($record->fresh()->toArray(), $request, 200);
    }

    /**
     * Get the activity timeline for a ticket.
     *
     * GET /api/helpdesk/tickets/{id}/activity
     */
    public function activity(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();
        $record = $modelClass::find($id);

        if (!$record) {
            return $this->buildResponse([
                'error' => ['message' => 'Record not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $log = $record->activityLog()->with('user')->get();

        return $this->buildResponse($log->toArray(), $request, Response::HTTP_OK);
    }

    /**
     * Merge a ticket into another ticket.
     *
     * POST /api/helpdesk/tickets/{id}/merge
     */
    public function merge(Request $request, string $id): Response
    {
        $validated = $request->validate([
            'target_ticket_id' => ['required', 'integer', 'exists:helpdesk_tickets,id'],
        ]);

        $modelClass = $this->getModel();

        $source = $modelClass::find($id);
        $target = $modelClass::find($validated['target_ticket_id']);

        if (!$source || !$target) {
            return $this->buildResponse([
                'error' => ['message' => 'Ticket not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        if ($source->id === $target->id) {
            return $this->buildResponse([
                'error' => ['message' => 'Cannot merge a ticket into itself', 'status' => 422],
            ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($source->isMerged()) {
            return $this->buildResponse([
                'error' => ['message' => 'Ticket has already been merged', 'status' => 422],
            ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $source->mergeInto($target);

        return $this->buildResponse([
            'message' => "Ticket {$source->reference} merged into {$target->reference}",
            'source' => $source->fresh()->toArray(),
            'target' => $target->fresh()->toArray(),
        ], $request, Response::HTTP_OK);
    }

    /**
     * Remove the specified ticket.
     *
     * @see docs/api-tickets.md for curl examples
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

        return $this->buildResponse(['message' => 'Ticket deleted'], $request, 200);
    }
}
