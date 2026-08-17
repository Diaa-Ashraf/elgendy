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
        $users = User::all();

        foreach ($users as $user) {
            $notificationData = [
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
            ];

            \Illuminate\Notifications\DatabaseNotification::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'data' => $notificationData,
            ]);
        }
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
        $methodText = $method === 'instapay' ? 'انستاباي ⚡' : 'فودافون كاش 📱';
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
}

