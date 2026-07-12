<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Services;

use Crumbls\HelpDesk\Events\TicketAssigned;
use Crumbls\HelpDesk\Models;
use Crumbls\HelpDesk\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class AssignmentService
{
    public function assign(Ticket $ticket, int|string $userId, string $role = 'assignee'): object
    {
        $assignmentClass = Models::ticketAssignment();

        $assignment = $assignmentClass::updateOrCreate([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'role' => $role,
        ]);

        $ticket->touchActivity();

        if (config('helpdesk.events.enabled') && config('helpdesk.events.dispatch.ticket_assigned')) {
            event(new TicketAssigned($ticket->fresh() ?? $ticket, $this->resolveUser($userId), $role));
        }

        return $assignment;
    }

    public function unassign(Ticket $ticket, int|string $userId, string $role = 'assignee'): int
    {
        $assignmentClass = Models::ticketAssignment();

        $deleted = $assignmentClass::query()
            ->where('ticket_id', $ticket->id)
            ->where('user_id', $userId)
            ->where('role', $role)
            ->delete();

        if ($deleted > 0) {
            $ticket->touchActivity();
        }

        return $deleted;
    }

    public function leastLoaded(iterable $userIds): int|string|null
    {
        $ids = $this->normalizeUserIds($userIds);

        if ($ids === []) {
            return null;
        }

        $assignmentClass = Models::ticketAssignment();
        $counts = $assignmentClass::query()
            ->whereIn('user_id', $ids)
            ->where('role', 'assignee')
            ->selectRaw('user_id, count(*) as assignment_count')
            ->groupBy('user_id')
            ->pluck('assignment_count', 'user_id')
            ->all();

        usort($ids, function (int|string $left, int|string $right) use ($counts): int {
            $leftCount = (int) ($counts[$left] ?? 0);
            $rightCount = (int) ($counts[$right] ?? 0);

            return $leftCount <=> $rightCount ?: ((string) $left <=> (string) $right);
        });

        return $ids[0];
    }

    public function roundRobin(iterable $userIds, string $key = 'default'): int|string|null
    {
        $ids = $this->normalizeUserIds($userIds);

        if ($ids === []) {
            return null;
        }

        sort($ids);

        $position = Cache::increment('helpdesk:assignment:round-robin:'.sha1($key));

        return $ids[($position - 1) % count($ids)];
    }

    protected function resolveUser(int|string $userId): object|int|string
    {
        $userClass = Models::user();

        return $userClass::query()->find($userId) ?? $userId;
    }

    protected function normalizeUserIds(iterable $userIds): array
    {
        $ids = [];

        foreach ($userIds as $userId) {
            if ($userId !== null && $userId !== '') {
                $ids[] = $userId;
            }
        }

        return array_values(array_unique($ids));
    }
}
