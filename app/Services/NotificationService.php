<?php

namespace App\Services;

use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class NotificationService
{
    /**
     * إرسال إشعار للنظام الداخلي (Filament Bell Icon)
     */
    public static function sendSystemNotification(string $title, string $body, string $type = 'info', ?string $url = null): void
    {
        // إرسال الإشعار للمستخدمين النشطين فقط دفعة واحدة
        $users = User::select('id')->get();
        if ($users->isEmpty()) {
            return;
        }

        $notificationData = json_encode([
            'actions' => $url ? [
                [
                    'name' => 'view',
                    'label' => 'عرض التفاصيل',
                    'url' => $url,
                ]
            ] : [],
            'body' => $body,
            'color' => match ($type) {
                'success' => 'success',
                'warning' => 'warning',
                'danger' => 'danger',
                default => 'info',
            },
            'duration' => 'persistent',
            'icon' => match ($type) {
                'success' => 'heroicon-o-check-circle',
                'warning' => 'heroicon-o-exclamation-triangle',
                'danger' => 'heroicon-o-x-circle',
                default => 'heroicon-o-information-circle',
            },
            'iconColor' => match ($type) {
                'success' => 'success',
                'warning' => 'warning',
                'danger' => 'danger',
                default => 'info',
            },
            'status' => $type,
            'title' => $title,
            'view' => 'filament-notifications::notification',
            'viewData' => [],
            'format' => 'filament',
        ]);

        $now = now();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notificationData,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        \Illuminate\Notifications\DatabaseNotification::insert($notifications);
    }

    /**
     * إشعار بطلب تقديم أونلاين جديد 🌐
     */
    public static function notifyNewOnlineApplication(string $studentName, string $stageName, int $applicationId): void
    {
        $url = url('/admin/student-applications');
        self::sendSystemNotification(
            "طلب جديد من {$studentName} برقم #{$applicationId}",
            "المرحلة: {$stageName}",
            'info',
            $url
        );
    }

    /**
     * إشعار بتحديد موعد مقابلة لطالب أونلاين 📅
     */
    public static function notifyInterviewScheduled(string $studentName, string $datetime): void
    {
        self::sendSystemNotification(
            'تحديد موعد مقابلة 📅',
            "تم تحديد موعد مقابلة واختبار قبولي للطالب {$studentName} في تاريخ: {$datetime}.",
            'warning',
            url('/admin/student-applications')
        );
    }

    /**
     * إشعار بتسجيل طالب جديد
     */
    public static function notifyNewStudent(string $studentName): void
    {
        self::sendSystemNotification(
            'طالب جديد 🎓',
            "تم تسجيل الطالب {$studentName} بنجاح في النظام وتوليد كود الـ QR.",
            'success'
        );
    }

    /**
     * إشعار بسداد رسوم
     */
    public static function notifyPaymentReceived(string $studentName, float $amount): void
    {
        self::sendSystemNotification(
            'تحصيل رسوم 💰',
            "تم تحصيل مبلغ {$amount} ج.م من الطالب {$studentName}.",
            'success'
        );
    }

    /**
     * إشعار بتأخر سداد
     */
    public static function notifyLatePayment(int $lateCount): void
    {
        if ($lateCount <= 0) {
            return;
        }

        self::sendSystemNotification(
            'تأخر سداد ⚠️',
            "يوجد عدد {$lateCount} طالب متأخر في سداد الرسوم المطلوبة.",
            'warning'
        );
    }

    /**
     * إشعار بطلب سداد إلكتروني جديد (InstaPay / Vodafone Cash) 💳
     */
    public static function notifyNewOnlinePaymentRequest(string $studentName, float $amount, string $method, int $requestId): void
    {
        $methodText = $method === 'instapay' ? 'انستاباي' : 'فودافون كاش';
        $url = url('/admin/online-payment-requests');

        self::sendSystemNotification(
            "💳 طلب سداد جديد ({$amount} ج.م)",
            "قام ولي أمر الطالب ({$studentName}) برفع إيصال سداد عبر ({$methodText}).",
            'warning',
            $url
        );
    }

    /**
     * إشعار بأداء طالب لاختبار إلكتروني وحصوله على نتيجة فورية 📝
     */
    public static function notifyStudentCompletedOnlineExam(string $studentName, string $examTitle, float $score, float $maxScore, float $percentage, int $examId): void
    {
        $passedText = $percentage >= 50 ? 'ناجح ✅' : 'راسب ⚠️';
        $url = \App\Filament\Resources\ExamResource::getUrl('analytics', ['record' => $examId]);

        self::sendSystemNotification(
            "📝 إتمام اختبار أونلاين ({$studentName})",
            "أتم الطالب ({$studentName}) اختبار ({$examTitle}) وحصل على ({$score}/{$maxScore}) بنسبة {$percentage}% ({$passedText}).",
            $percentage >= 50 ? 'success' : 'warning',
            $url
        );
    }

    /**
     * إرسال إشعار لولي الأمر داخل لوحة ولي الأمر (Parent Portal)
     */
    public static function notifyParent(int $studentId, string $type, string $title, string $message, ?string $actionUrl = null): ?\App\Models\ParentNotification
    {
        try {
            return \App\Models\ParentNotification::create([
                'student_id' => $studentId,
                'type' => $type, // attendance, exam, payment, homework, warning, general
                'title' => $title,
                'message' => $message,
                'is_read' => false,
                'action_url' => $actionUrl,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ParentNotification Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * إشعار بتسجيل حضور أو غياب طالب 📋 (للنظام الداخلي وبوابة ولي الأمر)
     */
    public static function notifyAttendance(string $studentName, string $groupName, string $datetime, string $status, ?int $studentId = null): void
    {
        $statusText = $status === 'present' ? 'حاضر ✅' : ($status === 'late' ? 'متأخر ⏰' : 'غائب ❌');
        $type = $status === 'present' ? 'success' : ($status === 'late' ? 'warning' : 'danger');

        self::sendSystemNotification(
            "تسجيل {$statusText} ({$studentName})",
            "تم تسجيل حالة ({$statusText}) للطالب ({$studentName}) في مجموعة ({$groupName}) بتاريخ ووقت: {$datetime}.",
            $type,
            url('/admin/attendances')
        );

        if ($studentId) {
            $parentTitle = $status === 'present' ? 'تسجيل حضور الحصة ✅' : ($status === 'late' ? 'تسجيل حضور متأخر ⏰' : 'تنبيه غياب عن الحصة ❌');
            $parentMessage = $status === 'present'
                ? "تم تسجيل حضور الطالب ({$studentName}) في حصة مجموعة ({$groupName}) بنجاح في تمام {$datetime}."
                : ($status === 'late'
                    ? "تم تسجيل حضور الطالب ({$studentName}) متأخراً عن حصة ({$groupName}) في تمام {$datetime}."
                    : "نحيطكم علماً بغياب الطالب ({$studentName}) عن حصة مجموعة ({$groupName}) المقررة في {$datetime}.");

            self::notifyParent($studentId, 'attendance', $parentTitle, $parentMessage, '/parent/dashboard');
        }
    }
}


