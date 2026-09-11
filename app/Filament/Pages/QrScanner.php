<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\GroupSession;
use App\Models\Student;
use App\Services\NotificationService;
use App\Services\SettingService;
use App\Services\WhatsAppNotificationService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class QrScanner extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'ماسح الـ QR لحضور الطلاب';

    protected static ?string $title = 'محطة تسجيل الحضور الذكية';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.qr-scanner';

    public ?int $selected_session_id = null;

    public string $scanned_code = '';

    public string $mode = 'auto';

    public ?int $lastAttendanceId = null;

    /** @var array{id: int, name: string, code: string, parent_phone: ?string, group_name: string, time: string, status: string}|null */
    public ?array $lastScannedStudent = null;

    public string $manualMessage = '';

    public bool $sound_enabled = true;

    public bool $fullscreen = false;

    public string $activeTab = 'attendees';

    /** @var array<int, array{id: int, student_id: int, name: string, code: string, parent_phone: ?string, time: string, status: string, whatsapp_sent_at: ?string}> */
    public array $attendance_log = [];

    /** @var array<int, array{student_id: int, name: string, code: string, parent_phone: ?string, attendance_id: ?int, whatsapp_sent_at: ?string}> */
    public array $absentees_log = [];

    public static function canAccess(): bool
    {
        return (bool) Auth::user();
    }

    public function mount(): void
    {
        // محاولة تحديد حصة اليوم أولاً، أو أحدث حصة سابقة
        $session = GroupSession::with('group.subject')
            ->whereDate('date', now()->toDateString())
            ->orderBy('date', 'desc')
            ->first();

        if (! $session) {
            $session = GroupSession::with('group.subject')
                ->whereDate('date', '<=', now()->toDateString())
                ->orderBy('date', 'desc')
                ->first();
        }

        if (! $session) {
            $session = GroupSession::with('group.subject')
                ->orderBy('date', 'asc')
                ->first();
        }

        if ($session) {
            $this->selected_session_id = $session->id;
            $this->loadAttendanceLog();
            $this->loadAbsenteesLog();
        }
    }

    /**
     * تغيير التبويب النشط (حاضرين / غائبين)
     */
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        if ($tab === 'absentees') {
            $this->loadAbsenteesLog();
        } else {
            $this->loadAttendanceLog();
        }
    }

    /**
     * تحميل سجل الحضور للجلسة المحددة
     */
    public function loadAttendanceLog(): void
    {
        if (! $this->selected_session_id) {
            $this->attendance_log = [];

            return;
        }

        $records = Attendance::select('id', 'student_id', 'status', 'checked_in_at', 'whatsapp_sent_at', 'updated_at')
            ->where('group_session_id', $this->selected_session_id)
            ->with('student:id,name,qr_code,parent_phone,phone')
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();

        $this->attendance_log = $records->map(fn($att) => [
            'id' => $att->id,
            'student_id' => $att->student_id,
            'name' => $att->student?->name ?? 'غير معروف',
            'code' => $att->student?->qr_code ?? '',
            'parent_phone' => $att->student?->parent_phone ?? $att->student?->phone ?? '',
            'time' => $att->checked_in_at
                ? \Carbon\Carbon::parse($att->checked_in_at)->format('h:i:s A')
                : \Carbon\Carbon::parse($att->updated_at)->format('h:i:s A'),
            'status' => $att->status,
            'whatsapp_sent_at' => $att->whatsapp_sent_at ? \Carbon\Carbon::parse($att->whatsapp_sent_at)->format('h:i A') : null,
        ])->toArray();
    }

    /**
     * تحميل قائمة الطلاب الغائبين عن الجلسة الحالية
     */
    public function loadAbsenteesLog(): void
    {
        if (! $this->selected_session_id) {
            $this->absentees_log = [];

            return;
        }

        $absentees = app(\App\Services\AttendanceService::class)->getAbsenteesForSession($this->selected_session_id);

        $this->absentees_log = $absentees->map(function ($student) {
            $isArr = is_array($student);
            $parentPhone = $isArr
                ? ($student['parent_phone'] ?? $student['phone'] ?? '')
                : ($student->parent_phone ?? $student->phone ?? '');

            return [
                'student_id' => $isArr ? $student['id'] : $student->id,
                'name' => $isArr ? $student['name'] : $student->name,
                'code' => $isArr ? ($student['code'] ?? '') : ($student->qr_code ?? (string) $student->id),
                'parent_phone' => $parentPhone,
                'attendance_id' => $isArr ? ($student['attendance_id'] ?? null) : ($student->attendance_id ?? null),
                'whatsapp_sent_at' => $isArr
                    ? ($student['whatsapp_sent_at'] ?? null)
                    : ($student->whatsapp_sent_at ? \Carbon\Carbon::parse($student->whatsapp_sent_at)->format('h:i A') : null),
            ];
        })->toArray();
    }

    /**
     * عند تغيير الجلسة المختارة يدوياً
     */
    public function updatedSelectedSessionId(): void
    {
        $this->loadAttendanceLog();
        $this->loadAbsenteesLog();
    }

    /**
     * حساب إحصائيات الحضور للجلسة الحالية
     *
     * @return array{present: int, absent: int, total: int, rate: float}
     */
    public function getSessionStats(): array
    {
        if (! $this->selected_session_id) {
            return ['present' => 0, 'absent' => 0, 'total' => 0, 'rate' => 0];
        }

        $session = GroupSession::with('group')->find($this->selected_session_id);
        if (! $session || ! $session->group) {
            return ['present' => 0, 'absent' => 0, 'total' => 0, 'rate' => 0];
        }

        $totalStudents = $session->group->students()->count();
        $presentCount = Attendance::where('group_session_id', $this->selected_session_id)
            ->where('status', 'present')
            ->count();

        $absentCount = max(0, $totalStudents - $presentCount);
        $rate = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0;

        return [
            'present' => $presentCount,
            'absent' => $absentCount,
            'total' => $totalStudents,
            'rate' => $rate,
        ];
    }

    /**
     * الحصول على بيانات الجلسة الحالية
     *
     * @return array{name: string, subject: string, date: string, title: string}|null
     */
    public function getCurrentSessionInfo(): ?array
    {
        if (! $this->selected_session_id) {
            return null;
        }

        $session = GroupSession::with('group.subject')->find($this->selected_session_id);
        if (! $session) {
            return null;
        }

        return [
            'name' => $session->group?->name ?? 'غير محدد',
            'subject' => $session->group?->subject?->name ?? 'عام',
            'date' => \Carbon\Carbon::parse($session->date)->format('Y-m-d'),
            'title' => $session->title ?? 'حصة عادية',
            'is_future' => \Carbon\Carbon::parse($session->date)->startOfDay()->isFuture(),
        ];
    }

    /**
     * معالجة مسح QR Code وتسجيل الحضور
     */
    public function processScan(): void
    {
        $code = trim($this->scanned_code);

        if (empty($code)) {
            Notification::make()
                ->title('تنبيه!')
                ->body('يرجى إدخال كود الطالب أو مسح الـ QR Code.')
                ->warning()
                ->send();

            return;
        }

        // 1. البحث عن الطالب بواسطة QR code أو رقم الهاتف أو المعرف ID
        $student = Student::select('id', 'name', 'qr_code', 'phone', 'parent_phone')
            ->where('qr_code', $code)
            ->orWhere('qr_code', 'STD-' . $code)
            ->first();

        if (! $student && is_numeric($code)) {
            $student = Student::select('id', 'name', 'qr_code', 'phone', 'parent_phone')
                ->where('id', (int) $code)
                ->orWhere('parent_phone', 'like', '%' . $code)
                ->orWhere('phone', 'like', '%' . $code)
                ->first();
        }

        if (! $student) {
            Notification::make()
                ->title('خطأ في المسح ❌')
                ->body("رمز الـ QR أو الكود غير مسجل بالنظام ({$code}).")
                ->danger()
                ->send();
            $this->scanned_code = '';
            $this->dispatch('scan-error');

            return;
        }

        $targetSession = null;

        if ($this->mode === 'auto') {
            $studentGroupIds = $student->groups()->pluck('groups.id')->toArray();

            // أ) إذا كان السكرتير قد اختار جلسة بالفعل والطالب منضم لمجموعتها
            if ($this->selected_session_id) {
                $chosenSession = GroupSession::with('group.subject')->find($this->selected_session_id);
                if ($chosenSession && in_array($chosenSession->group_id, $studentGroupIds, true)) {
                    $targetSession = $chosenSession;
                }
            }

            // ب) البحث عن جلسة اليوم الخاصة بمجموعات الطالب
            if (! $targetSession && ! empty($studentGroupIds)) {
                $targetSession = GroupSession::with('group.subject')
                    ->whereIn('group_id', $studentGroupIds)
                    ->whereDate('date', now()->toDateString())
                    ->orderBy('date', 'desc')
                    ->first();
            }

            // ج) البحث عن أحدث جلسة لمجموعات الطالب (حتى اليوم)
            if (! $targetSession && ! empty($studentGroupIds)) {
                $targetSession = GroupSession::with('group.subject')
                    ->whereIn('group_id', $studentGroupIds)
                    ->whereDate('date', '<=', now()->toDateString())
                    ->orderBy('date', 'desc')
                    ->first();
            }

            // د) fallback إذا تم تحديد جلسة حالياً
            if (! $targetSession && $this->selected_session_id) {
                $targetSession = GroupSession::with('group.subject')->find($this->selected_session_id);
            }

            if (! $targetSession) {
                Notification::make()
                    ->title('لم يتم العثور على حصة ⚠️')
                    ->body("الطالب ({$student->name}) غير مسجل في أي مجموعة بها حصص نشطة. يرجى اختيار الحصة يدوياً.")
                    ->warning()
                    ->send();
                $this->scanned_code = '';
                $this->dispatch('scan-error');

                return;
            }

            $this->selected_session_id = $targetSession->id;
        } else {
            // الوضع اليدوي: استخدام الجلسة المحددة من القائمة
            if (! $this->selected_session_id) {
                Notification::make()
                    ->title('تنبيه!')
                    ->body('يرجى اختيار الجلسة / الحصة الدراسية أولاً من القائمة.')
                    ->warning()
                    ->send();

                return;
            }

            $targetSession = GroupSession::with('group.subject')->find($this->selected_session_id);
        }

        if (! $targetSession) {
            Notification::make()->title('الجلسة غير موجودة')->danger()->send();
            $this->dispatch('scan-error');

            return;
        }

        // التحقق من تاريخ الحصة: منع تسجيل الحضور للحصص المستقبلية
        if (\Carbon\Carbon::parse($targetSession->date)->startOfDay()->isFuture()) {
            Notification::make()
                ->title('تنبيه: الحصة في تاريخ مستقبلي! ⚠️')
                ->body("لا يمكن تسجيل الحضور لحصة مجدولة في تاريخ مستقبلي ({$targetSession->date}). الحضور متاح فقط في يوم الحصة أو بعدها.")
                ->warning()
                ->send();
            $this->scanned_code = '';
            $this->dispatch('scan-error');

            return;
        }

        // 2. التحقق من عدم تسجيل الحضور مسبقاً
        $existing = Attendance::where('group_session_id', $targetSession->id)
            ->where('student_id', $student->id)
            ->where('status', 'present')
            ->first();

        if ($existing) {
            Notification::make()
                ->title("{$student->name} مسجل مسبقاً!")
                ->body('تم تسجيل حضور هذا الطالب في هذه الحصة من قبل.')
                ->info()
                ->send();
            $this->scanned_code = '';
            $this->dispatch('scan-duplicate');

            return;
        }

        // 3. تسجيل الحضور
        $attendance = Attendance::updateOrCreate(
            [
                'group_session_id' => $targetSession->id,
                'student_id' => $student->id,
            ],
            [
                'status' => 'present',
                'checked_in_at' => now(),
            ]
        );

        $this->lastAttendanceId = $attendance->id;

        if ($targetSession->status === 'scheduled') {
            $targetSession->update(['status' => 'held']);
        }

        $parentPhone = $student->parent_phone ?? $student->phone ?? '';
        $groupName = $targetSession->group?->name ?? 'غير محدد';
        $centerName = app(SettingService::class)->get('center_name', 'منظومة الأستاذ التعليمية');

        // 4. حفظ بيانات آخر طالب تم مسحه للواجهة
        $this->lastScannedStudent = [
            'id' => $student->id,
            'name' => $student->name,
            'code' => $student->qr_code ?? (string) $student->id,
            'parent_phone' => $parentPhone,
            'group_name' => $groupName,
            'time' => now()->format('h:i A'),
            'status' => 'present',
        ];

        // تجهيز نص رسالة الواتساب اليدوية التلقائية
        $this->manualMessage = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$student->name} ✅\n"
            . "نحيطكم علماً بأنه تم تسجيل حضور ابنكم في حصة ({$groupName}) بنجاح اليوم في تمام " . now()->format('h:i A') . ".\n\n"
            . "— {$centerName}";

        // 5. إضافة للسجل اللحظي
        array_unshift($this->attendance_log, [
            'id' => $attendance->id,
            'student_id' => $student->id,
            'name' => $student->name,
            'code' => $student->qr_code ?? '',
            'parent_phone' => $parentPhone,
            'time' => now()->format('h:i:s A'),
            'status' => 'present',
            'whatsapp_sent_at' => null,
        ]);

        // 6. إرسال إشعار داخلي للنظام وبوابة ولي الأمر
        NotificationService::notifyAttendance(
            $student->name,
            $groupName,
            now()->format('Y-m-d H:i'),
            'present',
            $student->id
        );

        Notification::make()
            ->title("✅ تم تسجيل حضور: {$student->name}")
            ->body("المجموعة: {$groupName} — الوقت: " . now()->format('h:i A'))
            ->success()
            ->send();

        $this->scanned_code = '';
        $this->loadAbsenteesLog();
        $this->dispatch('scan-success', name: $student->name);
    }

    /**
     * إرسال إشعار جماعي لجميع الطلاب الغائبين عبر بوابة ولي الأمر
     */
    public function notifyAllAbsenteesPortal(): void
    {
        if (! $this->selected_session_id) {
            Notification::make()->title('يرجى تحديد حصة أولاً')->warning()->send();

            return;
        }

        $session = GroupSession::find($this->selected_session_id);
        if ($session && \Carbon\Carbon::parse($session->date)->startOfDay()->isFuture()) {
            Notification::make()
                ->title('تنبيه: الحصة في تاريخ مستقبلي! ⚠️')
                ->body("لا يمكن إرسال إشعارات لحصة لم يحن تاريخها بعد ({$session->date}).")
                ->warning()
                ->send();

            return;
        }

        $count = app(\App\Services\AttendanceService::class)->notifyBulkParentPortal($this->selected_session_id, 'absent');
        $this->loadAbsenteesLog();
        $this->loadAttendanceLog();

        Notification::make()
            ->title("تم إرسال {$count} إشعار غياب عبر بوابة ولي الأمر بنجاح")
            ->success()
            ->send();
    }

    /**
     * إرسال رسائل واتساب جماعية لجميع الطلاب الغائبين
     */
    public function sendAllAbsenteesWhatsApp(): void
    {
        if (! $this->selected_session_id) {
            Notification::make()->title('يرجى تحديد حصة أولاً')->warning()->send();

            return;
        }

        $session = GroupSession::find($this->selected_session_id);
        if ($session && \Carbon\Carbon::parse($session->date)->startOfDay()->isFuture()) {
            Notification::make()
                ->title('تنبيه: الحصة في تاريخ مستقبلي! ⚠️')
                ->body("لا يمكن إرسال رسائل لحصة لم يحن تاريخها بعد ({$session->date}).")
                ->warning()
                ->send();

            return;
        }

        $res = app(\App\Services\AttendanceService::class)->sendBulkWhatsApp($this->selected_session_id, 'absent');
        $this->loadAbsenteesLog();

        Notification::make()
            ->title("تم إرسال {$res['success']} رسالة غياب عبر الواتساب")
            ->success()
            ->send();
    }

    /**
     * إرسال إشعار جماعي لجميع الطلاب الحاضرين عبر بوابة ولي الأمر
     */
    public function notifyAllAttendeesPortal(): void
    {
        if (! $this->selected_session_id) {
            Notification::make()->title('يرجى تحديد حصة أولاً')->warning()->send();

            return;
        }

        $session = GroupSession::find($this->selected_session_id);
        if ($session && \Carbon\Carbon::parse($session->date)->startOfDay()->isFuture()) {
            Notification::make()
                ->title('تنبيه: الحصة في تاريخ مستقبلي! ⚠️')
                ->body("لا يمكن إرسال إشعارات لحصة لم يحن تاريخها بعد ({$session->date}).")
                ->warning()
                ->send();

            return;
        }

        $count = app(\App\Services\AttendanceService::class)->notifyBulkParentPortal($this->selected_session_id, 'present');

        Notification::make()
            ->title("تم إرسال {$count} إشعار حضور عبر بوابة ولي الأمر بنجاح")
            ->success()
            ->send();
    }

    /**
     * إرسال رسائل واتساب جماعية لجميع الطلاب الحاضرين
     */
    public function sendAllAttendeesWhatsApp(): void
    {
        if (! $this->selected_session_id) {
            Notification::make()->title('يرجى تحديد حصة أولاً')->warning()->send();

            return;
        }

        $session = GroupSession::find($this->selected_session_id);
        if ($session && \Carbon\Carbon::parse($session->date)->startOfDay()->isFuture()) {
            Notification::make()
                ->title('تنبيه: الحصة في تاريخ مستقبلي! ⚠️')
                ->body("لا يمكن إرسال رسائل لحصة لم يحن تاريخها بعد ({$session->date}).")
                ->warning()
                ->send();

            return;
        }

        $res = app(\App\Services\AttendanceService::class)->sendBulkWhatsApp($this->selected_session_id, 'present');
        $this->loadAttendanceLog();

        Notification::make()
            ->title("تم إرسال {$res['success']} رسالة تأكيد حضور عبر الواتساب")
            ->success()
            ->send();
    }

    /**
     * تسجيل حضور طالب غائب يدوياً بنقرة واحدة من قائمة الغائبين
     */
    public function markStudentPresent(int $studentId): void
    {
        if (! $this->selected_session_id) {
            return;
        }

        $session = GroupSession::find($this->selected_session_id);
        if ($session && \Carbon\Carbon::parse($session->date)->startOfDay()->isFuture()) {
            Notification::make()
                ->title('تنبيه: الحصة في تاريخ مستقبلي! ⚠️')
                ->body("لا يمكن تسجيل الحضور لحصة مجدولة في تاريخ مستقبلي ({$session->date}).")
                ->warning()
                ->send();

            return;
        }

        $student = Student::find($studentId);
        if (! $student) {
            return;
        }

        $attendance = Attendance::updateOrCreate(
            [
                'group_session_id' => $this->selected_session_id,
                'student_id' => $student->id,
            ],
            [
                'status' => 'present',
                'checked_in_at' => now(),
            ]
        );

        $sessionInfo = $this->getCurrentSessionInfo();
        NotificationService::notifyAttendance(
            $student->name,
            $sessionInfo['name'] ?? 'الحصة الدراسية',
            now()->format('Y-m-d H:i'),
            'present',
            $student->id
        );

        $this->loadAttendanceLog();
        $this->loadAbsenteesLog();

        Notification::make()
            ->title("تم تسجيل حضور: {$student->name} بنجاح ✅")
            ->success()
            ->send();
    }

    /**
     * إرسال رسالة واتساب يدوية
     */
    public function sendManualWhatsApp(?int $attendanceId = null): void
    {
        $id = $attendanceId ?: $this->lastAttendanceId;
        if (! $id) {
            return;
        }

        $attendance = Attendance::with('student')->find($id);
        if (! $attendance || ! $attendance->student) {
            Notification::make()->title('سجل الحضور غير موجود')->danger()->send();

            return;
        }

        $parentPhone = $attendance->student->parent_phone ?? $attendance->student->phone;
        if (empty($parentPhone)) {
            Notification::make()
                ->title('لا يوجد رقم هاتف مسجل')
                ->body('يرجى تحديث رقم هاتف ولي الأمر في ملف الطالب أولاً.')
                ->warning()
                ->send();

            return;
        }

        $message = trim($this->manualMessage);
        if (empty($message)) {
            $centerName = app(SettingService::class)->get('center_name', 'منظومة الأستاذ التعليمية');
            $message = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$attendance->student->name} ✅\n"
                . "نحيطكم علماً بحضور ابنكم الحصة بنجاح.\n\n— {$centerName}";
        }

        // إرسال عبر WhatsAppNotificationService (إذا كان الـ API مهيأ)
        app(WhatsAppNotificationService::class)->sendManualMessage($parentPhone, $message);

        // تسجيل تاريخ الإرسال
        $attendance->update(['whatsapp_sent_at' => now()]);

        // تحديث السجل المحلي
        $this->loadAttendanceLog();

        Notification::make()
            ->title('تم إرسال رسالة الواتساب لولي الأمر ✅')
            ->body("الهاتف: {$parentPhone}")
            ->success()
            ->send();
    }

    /**
     * تحديد أن رسالة الواتساب تم إرسالها (عند الضغط على رابط wa.me المباشر)
     */
    public function markWhatsAppSent(int $attendanceId): void
    {
        $attendance = Attendance::find($attendanceId);
        if ($attendance) {
            $attendance->update(['whatsapp_sent_at' => now()]);
            $this->loadAttendanceLog();

            Notification::make()
                ->title('تم تسجيل إرسال رسالة الواتساب بنجاح 📱')
                ->success()
                ->send();
        }
    }
}
