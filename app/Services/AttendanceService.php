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
        $group = Group::with('schedules')->findOrFail($groupId);
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
                $session = GroupSession::firstOrCreate(
                    [
                        'group_id' => $groupId,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
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
     * Save bulk attendance records for a group session and dispatch notifications.
     *
     * @param int $groupSessionId
     * @param array $studentsStatuses Array of ['student_id' => int, 'status' => string, 'notes' => ?string]
     */
    public function markBulkAttendance(int $groupSessionId, array $studentsStatuses): void
    {
        $session = GroupSession::with(['group.subject', 'group.educationalStage'])->find($groupSessionId);
        if ($session && Carbon::parse($session->date)->startOfDay()->isFuture()) {
            throw new \InvalidArgumentException("لا يمكن تسجيل الحضور لحصة في تاريخ مستقبلي ({$session->date}).");
        }

        DB::transaction(function () use ($groupSessionId, $studentsStatuses, $session) {
            foreach ($studentsStatuses as $record) {
                if (!isset($record['student_id'])) {
                    continue;
                }

                $status = $record['status'] ?? 'present';
                $studentId = $record['student_id'];

                Attendance::updateOrCreate(
                    [
                        'group_session_id' => $groupSessionId,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => $status,
                        'notes' => $record['notes'] ?? null,
                    ]
                );

                // إرسال إشعار فوري لولي الأمر (في البوابة + واتساب) إذا كان الطالب غائباً أو متأخراً
                $student = \App\Models\Student::find($studentId);
                if ($student) {
                    $groupName = $session?->group?->name ?? 'المجموعة';
                    $sessionDate = $session?->date ?? now()->toDateString();

                    if ($status === 'absent') {
                        // 1. إشعار في بوابة ولي الأمر
                        \App\Models\ParentNotification::create([
                            'student_id' => $student->id,
                            'type' => 'warning',
                            'title' => "⚠️ تنبيه غياب الطالب عن حصة {$groupName}",
                            'message' => "نحيطكم علماً بأن الطالب ({$student->name}) تغيب عن حضور حصة اليوم ({$sessionDate}) في مادة ({$session?->group?->subject?->name}). يرجى متابعة الطالب للاطمئنان.",
                        ]);
                    } elseif ($status === 'present') {
                        // إشعار حضور في البوابة إذا رغب
                        \App\Models\ParentNotification::firstOrCreate(
                            [
                                'student_id' => $student->id,
                                'title' => "✅ تم تسجيل حضور حصة {$groupName}",
                                'created_at' => now()->toDateString(),
                            ],
                            [
                                'type' => 'general',
                                'message' => "تم حضور الطالب ({$student->name}) لحصة اليوم ({$sessionDate}) بنجاح.",
                            ]
                        );
                    }
                }
            }

            // Update session status to held if marked
            if ($session && $session->status === 'scheduled') {
                $session->update(['status' => 'held']);
            }
        });
    }

    /**
     * الحصول على قائمة الطلاب الغائبين في الجلسة (المقيدين بالمجموعة ولم يسجلوا حضوراً)
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, name: string, code: string, phone: ?string, parent_phone: ?string, attendance_id: ?int, status: string, whatsapp_sent_at: ?string, group_name: string, session_date: string, session_title: string}>
     */
    public function getAbsenteesForSession(int $sessionId): \Illuminate\Support\Collection
    {
        $session = GroupSession::with('group.students')->find($sessionId);
        if (! $session || ! $session->group) {
            return collect();
        }

        $allStudents = $session->group->students()
            ->wherePivot('status', 'active')
            ->get();

        $attendances = Attendance::where('group_session_id', $sessionId)
            ->get()
            ->keyBy('student_id');

        return $allStudents->map(function ($student) use ($attendances, $session) {
            $att = $attendances->get($student->id);
            $status = $att?->status ?? 'absent';
            $isPresent = in_array($status, ['present', 'late'], true);

            if ($isPresent) {
                return null;
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'code' => $student->qr_code ?: ('STD-' . $student->id),
                'phone' => $student->phone,
                'parent_phone' => $student->parent_phone ?? $student->phone,
                'attendance_id' => $att?->id,
                'status' => $status,
                'whatsapp_sent_at' => $att?->whatsapp_sent_at ? Carbon::parse($att->whatsapp_sent_at)->format('h:i A') : null,
                'group_name' => $session->group->name,
                'session_date' => $session->date,
                'session_title' => $session->topic ?? 'حصة عادية',
            ];
        })->filter()->values();
    }

    /**
     * الحصول على قائمة الطلاب الحاضرين في الجلسة
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, name: string, code: string, phone: ?string, parent_phone: ?string, attendance_id: int, status: string, time: string, whatsapp_sent_at: ?string, group_name: string, session_date: string}>
     */
    public function getAttendeesForSession(int $sessionId): \Illuminate\Support\Collection
    {
        $session = GroupSession::with('group')->find($sessionId);
        if (! $session || ! $session->group) {
            return collect();
        }

        $attendances = Attendance::where('group_session_id', $sessionId)
            ->whereIn('status', ['present', 'late'])
            ->with('student')
            ->orderBy('id', 'desc')
            ->get();

        return $attendances->map(function ($att) use ($session) {
            $student = $att->student;
            if (! $student) {
                return null;
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'code' => $student->qr_code ?: ('STD-' . $student->id),
                'phone' => $student->phone,
                'parent_phone' => $student->parent_phone ?? $student->phone,
                'attendance_id' => $att->id,
                'status' => $att->status,
                'time' => $att->checked_in_at ? Carbon::parse($att->checked_in_at)->format('h:i:s A') : Carbon::parse($att->updated_at)->format('h:i:s A'),
                'whatsapp_sent_at' => $att->whatsapp_sent_at ? Carbon::parse($att->whatsapp_sent_at)->format('h:i A') : null,
                'group_name' => $session->group->name,
                'session_date' => $session->date,
            ];
        })->filter()->values();
    }

    /**
     * إرسال إشعارات جماعية لبوابة ولي الأمر (للغائبين أو الحاضرين)
     */
    public function notifyBulkParentPortal(int $sessionId, string $target = 'absent'): int
    {
        $session = GroupSession::with(['group.subject'])->find($sessionId);
        if (! $session) {
            return 0;
        }

        $groupName = $session->group?->name ?? 'المجموعة';
        $subjectName = $session->group?->subject?->name ?? 'المادة';
        $sessionDate = Carbon::parse($session->date)->format('Y-m-d');
        $count = 0;

        if ($target === 'absent') {
            $absentees = $this->getAbsenteesForSession($sessionId);
            foreach ($absentees as $item) {
                Attendance::updateOrCreate(
                    [
                        'group_session_id' => $sessionId,
                        'student_id' => $item['id'],
                    ],
                    [
                        'status' => 'absent',
                    ]
                );

                \App\Models\ParentNotification::create([
                    'student_id' => $item['id'],
                    'type' => 'warning',
                    'title' => "⚠️ تنبيه غياب عن حصة {$groupName}",
                    'message' => "نحيطكم علماً بغياب الطالب ({$item['name']}) عن حصة مادة ({$subjectName}) المقررة بتاريخ: {$sessionDate}.",
                    'action_url' => '/parent/dashboard',
                ]);
                $count++;
            }
        } else {
            $attendees = $this->getAttendeesForSession($sessionId);
            foreach ($attendees as $item) {
                \App\Models\ParentNotification::create([
                    'student_id' => $item['id'],
                    'type' => 'attendance',
                    'title' => "✅ تأكيد حضور حصة {$groupName}",
                    'message' => "تم تسجيل حضور الطالب ({$item['name']}) لحصة ({$subjectName}) بتاريخ: {$sessionDate}.",
                    'action_url' => '/parent/dashboard',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * إرسال رسائل واتساب جماعية عبر النظام (للغائبين أو الحاضرين)
     *
     * @return array{success: int, failed: int, total: int}
     */
    public function sendBulkWhatsApp(int $sessionId, string $target = 'absent', ?string $customMessage = null): array
    {
        $session = GroupSession::with(['group.subject'])->find($sessionId);
        if (! $session) {
            return ['success' => 0, 'failed' => 0, 'total' => 0];
        }

        $centerName = app(SettingService::class)->get('center_name', 'المنظومة التعليمية');
        $groupName = $session->group?->name ?? 'المجموعة';
        $subjectName = $session->group?->subject?->name ?? 'المادة';
        $sessionDate = Carbon::parse($session->date)->format('Y-m-d');
        $whatsappService = app(WhatsAppNotificationService::class);

        $students = $target === 'absent'
            ? $this->getAbsenteesForSession($sessionId)
            : $this->getAttendeesForSession($sessionId);

        $success = 0;
        $failed = 0;

        foreach ($students as $student) {
            $phone = $student['parent_phone'];
            if (empty($phone)) {
                $failed++;
                continue;
            }

            if (! empty($customMessage)) {
                $message = str_replace(
                    ['{name}', '{student_name}', '{group}', '{subject}', '{date}', '{center}'],
                    [$student['name'], $student['name'], $groupName, $subjectName, $sessionDate, $centerName],
                    $customMessage
                );
            } elseif ($target === 'absent') {
                $message = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$student['name']} ⚠️\n"
                    . "نحيطكم علماً بعدم حضور ابنكم لحصة اليوم ({$groupName}) في مادة ({$subjectName}) بتاريخ: {$sessionDate}.\n"
                    . "يرجى التواصل مع إدارة المركز للمتابعة والتنسيق.\n\n"
                    . "— {$centerName}";
            } else {
                $message = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$student['name']} ✅\n"
                    . "تم تسجيل حضور ابنكم بنجاح لحصة ({$groupName}) بتاريخ: {$sessionDate}.\n\n"
                    . "— {$centerName}";
            }

            $attendance = Attendance::updateOrCreate(
                [
                    'group_session_id' => $sessionId,
                    'student_id' => $student['id'],
                ],
                [
                    'status' => $target === 'absent' ? 'absent' : 'present',
                ]
            );

            $sent = $whatsappService->sendMessage($phone, $message);
            if ($sent) {
                $attendance->update(['whatsapp_sent_at' => now()]);
                $success++;
            } else {
                $failed++;
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'total' => count($students),
        ];
    }
}
