<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\GroupSession;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Generate sessions for a given group between two dates based on its schedule.
     */
    public function generateSessions(int $groupId, string $fromDate, string $toDate): int
    {
        $group = Group::withoutGlobalScope('tenant')->with(['schedules' => function ($q) {
            $q->withoutGlobalScope('tenant');
        }])->findOrFail($groupId);

        $schedules = $group->schedules;

        if ($schedules->isEmpty()) {
            return 0;
        }

        // Map short days to Carbon dayOfWeek numbers (0: Sun, 1: Mon, ..., 6: Sat)
        $dayMapping = [
            'sun' => Carbon::SUNDAY,
            'mon' => Carbon::MONDAY,
            'tue' => Carbon::TUESDAY,
            'wed' => Carbon::WEDNESDAY,
            'thu' => Carbon::THURSDAY,
            'fri' => Carbon::FRIDAY,
            'sat' => Carbon::SATURDAY,
        ];

        $targetDays = $schedules->pluck('day_of_week')
            ->map(fn ($day) => $dayMapping[strtolower($day)] ?? null)
            ->filter()
            ->unique()
            ->toArray();

        $period = CarbonPeriod::create($fromDate, $toDate);
        $createdCount = 0;

        foreach ($period as $date) {
            if (in_array($date->dayOfWeek, $targetDays)) {
                $session = GroupSession::withoutGlobalScope('tenant')->firstOrCreate(
                    [
                        'group_id' => $groupId,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'tenant_id' => $group->tenant_id,
                        'status' => 'scheduled',
                    ]
                );

                if ($session->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        }

        return $createdCount;
    }

    /**
     * Save bulk attendance records for a group session.
     *
     * @param int $groupSessionId
     * @param array $studentsStatuses Array of ['student_id' => int, 'status' => string, 'notes' => ?string]
     */
    public function markBulkAttendance(int $groupSessionId, array $studentsStatuses): void
    {
        DB::transaction(function () use ($groupSessionId, $studentsStatuses) {
            foreach ($studentsStatuses as $record) {
                if (!isset($record['student_id'])) {
                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'group_session_id' => $groupSessionId,
                        'student_id' => $record['student_id'],
                    ],
                    [
                        'status' => $record['status'] ?? 'present',
                        'notes' => $record['notes'] ?? null,
                    ]
                );
            }

            // Update session status to held if marked
            $session = GroupSession::find($groupSessionId);
            if ($session && $session->status === 'scheduled') {
                $session->update(['status' => 'held']);
            }
        });
    }
}
