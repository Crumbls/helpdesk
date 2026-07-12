<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Crumbls\HelpDesk\Models\CustomField;
use Crumbls\HelpDesk\Services\AssignmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends ApiController
{
    public function getModel(): string
    {
        return Models::ticket();
    }

    public function index(Request $request): Response
    {
        $modelClass = $this->getModel();

        $query = $modelClass::query()
            ->orderByDesc('last_activity_at')
            ->orderByDesc('id');

        foreach (['department_id', 'priority_id', 'ticket_status_id', 'ticket_type_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $assignedToUserId = $request->input('assigned_to');

        if ($request->input('queue') === 'assigned_to_me') {
            $assignedToUserId = $request->input('user_id', $request->user()?->getAuthIdentifier());
        }

        if ($assignedToUserId !== null && $assignedToUserId !== '') {
            $userClass = Models::user();
            $user = new $userClass;
            $qualifiedUserKey = $user->getTable().'.'.$user->getKeyName();

            $query->whereHas('assignedUsers', function (Builder $assignedQuery) use ($assignedToUserId, $qualifiedUserKey) {
                $assignedQuery->where($qualifiedUserKey, $assignedToUserId);
            });
        } elseif ($request->input('queue') === 'assigned_to_me') {
            $query->whereRaw('1 = 0');
        }

        match ($request->input('queue')) {
            'open' => $query->whereHas('status', function (Builder $statusQuery) {
                $statusQuery->where('is_closed', false);
            }),
            'closed' => $query->whereHas('status', function (Builder $statusQuery) {
                $statusQuery->where('is_closed', true);
            }),
            'unassigned' => $query->whereDoesntHave('assignees'),
            default => null,
        };

        if ($request->filled('unread_for')) {
            $userId = $request->input('unread_for');

            $query->where(function (Builder $query) use ($userId) {
                $query
                    ->whereDoesntHave('reads', function (Builder $readQuery) use ($userId) {
                        $readQuery->where('user_id', $userId);
                    })
                    ->orWhereHas('reads', function (Builder $readQuery) use ($userId) {
                        $readQuery
                            ->where('user_id', $userId)
                            ->whereColumn('last_read_at', '<', 'helpdesk_tickets.last_activity_at');
                    });
            });
        }

        return $this->buildResponse($query->paginate()->toArray(), $request, Response::HTTP_OK);
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
            'custom_fields' => ['nullable', 'array'],
            'due_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
        ]);

        if (empty($validated['ticket_status_id'])) {
            $statusClass = Models::status();
            $defaultStatus = $statusClass::where('is_default', true)->first();

            if (! $defaultStatus) {
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

            if (! $defaultType) {
                return $this->buildResponse([
                    'error' => [
                        'message' => 'No default type configured',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    ],
                ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated['ticket_type_id'] = $defaultType->id;
        }

        CustomField::validateTicketValues(
            $validated['department_id'] ?? null,
            $validated['ticket_type_id'] ?? null,
            $validated,
        );

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

        if (! $record) {
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
            'metadata' => ['nullable', 'array'],
            'custom_fields' => ['nullable', 'array'],
            'due_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
        ]);

        $contextChanged = array_key_exists('department_id', $validated)
            || array_key_exists('ticket_type_id', $validated);
        $customFieldsChanged = array_key_exists('custom_fields', $validated);

        if ($contextChanged || $customFieldsChanged) {
            $validated['custom_fields'] = $customFieldsChanged
                ? array_replace($record->custom_fields ?? [], $validated['custom_fields'] ?? [])
                : ($record->custom_fields ?? []);

            CustomField::validateTicketValues(
                $validated['department_id'] ?? $record->department_id,
                $validated['ticket_type_id'] ?? $record->ticket_type_id,
                $validated,
            );
        }

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

        if (! $record) {
            return $this->buildResponse([
                'error' => ['message' => 'Record not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $log = $record->activityLog()->with('user')->get();

        return $this->buildResponse($log->toArray(), $request, Response::HTTP_OK);
    }

    public function markRead(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();
        $record = $modelClass::find($id);

        if (! $record) {
            return $this->buildResponse([
                'error' => ['message' => 'Record not found', 'status' => Response::HTTP_NOT_FOUND],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $userId = $request->input('user_id', $request->user()?->getAuthIdentifier());

        if ($userId === null || $userId === '') {
            return $this->buildResponse([
                'error' => [
                    'message' => 'A user_id is required when no authenticated user is available',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                ],
            ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $read = $record->markReadBy($userId);

        return $this->buildResponse([
            'message' => 'Ticket marked read',
            'ticket_id' => $record->id,
            'user_id' => $userId,
            'last_read_at' => $read->last_read_at?->toISOString(),
        ], $request, Response::HTTP_OK);
    }

    public function markUnread(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();
        $record = $modelClass::find($id);

        if (! $record) {
            return $this->buildResponse([
                'error' => ['message' => 'Record not found', 'status' => Response::HTTP_NOT_FOUND],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $userId = $request->input('user_id', $request->user()?->getAuthIdentifier());

        if ($userId === null || $userId === '') {
            return $this->buildResponse([
                'error' => [
                    'message' => 'A user_id is required when no authenticated user is available',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                ],
            ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $record->markUnreadBy($userId);

        return $this->buildResponse([
            'message' => 'Ticket marked unread',
            'ticket_id' => $record->id,
            'user_id' => $userId,
        ], $request, Response::HTTP_OK);
    }

    public function assign(Request $request, string $id, AssignmentService $assignments): Response
    {
        $modelClass = $this->getModel();
        $record = $modelClass::find($id);

        if (! $record) {
            return $this->buildResponse([
                'error' => ['message' => 'Record not found', 'status' => Response::HTTP_NOT_FOUND],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'user_id' => ['nullable'],
            'role' => ['sometimes', 'string', 'max:50'],
            'strategy' => ['sometimes', Rule::in(['direct', 'least_loaded', 'round_robin'])],
            'department_id' => ['nullable', 'integer', 'exists:helpdesk_departments,id'],
        ]);

        $strategy = $validated['strategy'] ?? 'direct';
        $role = $validated['role'] ?? 'assignee';
        $userId = $validated['user_id'] ?? null;

        if ($strategy !== 'direct') {
            $departmentId = $validated['department_id'] ?? $record->department_id;
            $departmentClass = Models::department();
            $department = $departmentId ? $departmentClass::find($departmentId) : null;
            $userIds = $department
                ? $department->users()->get()->map(fn (object $user): mixed => $user->getKey())->all()
                : [];

            $userId = $strategy === 'least_loaded'
                ? $assignments->leastLoaded($userIds)
                : $assignments->roundRobin($userIds, "department:{$departmentId}");
        }

        if ($userId === null || $userId === '') {
            return $this->buildResponse([
                'error' => [
                    'message' => 'No assignable user was found',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                ],
            ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $assignment = $assignments->assign($record, $userId, $role);

        return $this->buildResponse([
            'message' => 'Ticket assigned',
            'assignment' => method_exists($assignment, 'toArray') ? $assignment->toArray() : [],
        ], $request, Response::HTTP_OK);
    }

    public function unassign(Request $request, string $id, AssignmentService $assignments): Response
    {
        $modelClass = $this->getModel();
        $record = $modelClass::find($id);

        if (! $record) {
            return $this->buildResponse([
                'error' => ['message' => 'Record not found', 'status' => Response::HTTP_NOT_FOUND],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'user_id' => ['required'],
            'role' => ['sometimes', 'string', 'max:50'],
        ]);

        $assignments->unassign($record, $validated['user_id'], $validated['role'] ?? 'assignee');

        return $this->buildResponse([
            'message' => 'Ticket unassigned',
            'ticket_id' => $record->id,
            'user_id' => $validated['user_id'],
        ], $request, Response::HTTP_OK);
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

        if (! $source || ! $target) {
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

        if (! $record) {
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
