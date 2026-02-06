<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Contracts;

use Carbon\Carbon;

interface SlaCalculator
{
    /**
     * Calculate the response due date based on priority and creation time.
     */
    public function calculateResponseDue(int $priorityId, Carbon $createdAt): Carbon;

    /**
     * Calculate the resolution due date based on priority and creation time.
     */
    public function calculateResolutionDue(int $priorityId, Carbon $createdAt): Carbon;

    /**
     * Check if the response SLA has been breached.
     */
    public function isResponseBreached(Carbon $responseDue, ?Carbon $firstResponseAt): bool;

    /**
     * Check if the resolution SLA has been breached.
     */
    public function isResolutionBreached(Carbon $resolutionDue, ?Carbon $closedAt): bool;
}
