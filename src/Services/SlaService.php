<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Services;

use Carbon\Carbon;
use Crumbls\HelpDesk\Contracts\SlaCalculator;
use Crumbls\HelpDesk\Models;

class SlaService implements SlaCalculator
{
    public function calculateResponseDue(int $priorityId, Carbon $createdAt): Carbon
    {
        $hours = $this->getResponseHours($priorityId);

        if (config('helpdesk.sla.business_hours.enabled', false)) {
            return $this->addBusinessHours($createdAt->copy(), $hours);
        }

        return $createdAt->copy()->addHours($hours);
    }

    public function calculateResolutionDue(int $priorityId, Carbon $createdAt): Carbon
    {
        $hours = $this->getResolutionHours($priorityId);

        if (config('helpdesk.sla.business_hours.enabled', false)) {
            return $this->addBusinessHours($createdAt->copy(), $hours);
        }

        return $createdAt->copy()->addHours($hours);
    }

    public function isResponseBreached(Carbon $responseDue, ?Carbon $firstResponseAt): bool
    {
        if ($firstResponseAt === null) {
            return Carbon::now()->gt($responseDue);
        }

        return $firstResponseAt->gt($responseDue);
    }

    public function isResolutionBreached(Carbon $resolutionDue, ?Carbon $closedAt): bool
    {
        if ($closedAt === null) {
            return Carbon::now()->gt($resolutionDue);
        }

        return $closedAt->gt($resolutionDue);
    }

    protected function getResponseHours(int $priorityId): int
    {
        $priorityClass = Models::priority();
        $priority = $priorityClass::find($priorityId);

        if ($priority && $priority->sla_response_hours !== null) {
            return $priority->sla_response_hours;
        }

        return (int) config('helpdesk.sla.defaults.response_hours', 24);
    }

    protected function getResolutionHours(int $priorityId): int
    {
        $priorityClass = Models::priority();
        $priority = $priorityClass::find($priorityId);

        if ($priority && $priority->sla_resolution_hours !== null) {
            return $priority->sla_resolution_hours;
        }

        return (int) config('helpdesk.sla.defaults.resolution_hours', 72);
    }

    protected function addBusinessHours(Carbon $date, int $hours): Carbon
    {
        $config = config('helpdesk.sla.business_hours');
        $timezone = $config['timezone'] ?? 'UTC';
        $startTime = $config['start'] ?? '09:00';
        $endTime = $config['end'] ?? '17:00';
        $workDays = $config['days'] ?? [1, 2, 3, 4, 5];

        $date = $date->setTimezone($timezone);

        $startParts = explode(':', $startTime);
        $endParts = explode(':', $endTime);
        $dayStartHour = (int) $startParts[0];
        $dayStartMinute = (int) ($startParts[1] ?? 0);
        $dayEndHour = (int) $endParts[0];
        $dayEndMinute = (int) ($endParts[1] ?? 0);

        $hoursPerDay = ($dayEndHour * 60 + $dayEndMinute - $dayStartHour * 60 - $dayStartMinute) / 60;

        $remainingHours = $hours;

        while ($remainingHours > 0) {
            if (!in_array($date->dayOfWeek, $workDays)) {
                $date->addDay()->setTime($dayStartHour, $dayStartMinute);
                continue;
            }

            $currentDayStart = $date->copy()->setTime($dayStartHour, $dayStartMinute);
            $currentDayEnd = $date->copy()->setTime($dayEndHour, $dayEndMinute);

            if ($date->lt($currentDayStart)) {
                $date->setTime($dayStartHour, $dayStartMinute);
            }

            if ($date->gte($currentDayEnd)) {
                $date->addDay()->setTime($dayStartHour, $dayStartMinute);
                continue;
            }

            $availableHours = $date->diffInMinutes($currentDayEnd) / 60;

            if ($availableHours >= $remainingHours) {
                $date->addMinutes((int) ($remainingHours * 60));
                $remainingHours = 0;
            } else {
                $remainingHours -= $availableHours;
                $date->addDay()->setTime($dayStartHour, $dayStartMinute);
            }
        }

        return $date;
    }
}
