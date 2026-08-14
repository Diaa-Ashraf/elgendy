<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * إرسال رسالة واتساب
     */
    public function sendMessage(string $phone, string $message): bool
    {
        // تنظيف رقم الهاتف وإضافة كود الدولة
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '01')) {
            $cleanPhone = '2' . $cleanPhone;
        }

        $apiUrl = Setting::get('whatsapp_api_url');
        $apiKey = Setting::get('whatsapp_api_key');
        $instanceId = Setting::get('whatsapp_instance_id');

        if (! $apiUrl || ! $apiKey) {
            Log::info("WhatsApp API non configured. Message to {$cleanPhone}: {$message}");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'instance_id' => $instanceId,
                'phone' => $cleanPhone,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("WhatsApp Notification Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * إشعار حضور الطالب
     */
    public function notifyAttendance(string $parentPhone, string $studentName, string $status, string $date, string $groupName): bool
    {
        $statusText = $status === 'present' ? 'حضر ✅' : ($status === 'late' ? 'حضر متأخراً ⏰' : 'غائب ❌');
        
        $msg = "المكرم ولي أمر الطالب/ة: {$studentName}\n";
        $msg .= "حيّاكم الله من نظام الأستاذ محمد الغندي التعليمي.\n\n";
        $msg .= "نود إحاطتكم بحالة حضور ابنكم اليوم ({$date}):\n";
        $msg .= "المجموعة: {$groupName}\n";
        $msg .= "الحالة: {$statusText}\n\n";
        $msg .= "شاكرين حسن تعاونكم وحرصكم الدائم.";

        return $this->sendMessage($parentPhone, $msg);
    }

    /**
     * إشعار نتيجة امتحان
     */
    public function notifyExamResult(string $parentPhone, string $studentName, string $examTitle, float $mark, float $totalMarks): bool
    {
        $percentage = round(($mark / max($totalMarks, 1)) * 100, 1);
        $rating = $percentage >= 85 ? 'ممتاز ⭐⭐⭐' : ($percentage >= 70 ? 'جيد جداً 👍' : 'يحتاج لمتابعة 📝');

        $msg = "ولي أمر الطالب/ة: {$studentName}\n";
        $msg .= "نتيجة اختبار: {$examTitle}\n";
        $msg .= "الدرجة المحصلة: {$mark} من {$totalMarks} ({$percentage}%)\n";
        $msg .= "التقييم: {$rating}\n\n";
        $msg .= "نتمنى له دوام التوفيق والنجاح.";

        return $this->sendMessage($parentPhone, $msg);
    }

    /**
     * إشعار تذكير بالسداد
     */
    public function notifyPaymentReminder(string $parentPhone, string $studentName, float $amount, string $month): bool
    {
        $msg = "تذكير برسم الاشتراك 💰\n";
        $msg .= "ولي أمر الطالب/ة: {$studentName}\n";
        $msg .= "نود تذكيركم بلطف بموعد سداد رسوم شهر ({$month}) بمبلغ {$amount} ج.م.\n";
        $msg .= "يرجى التكرم بالسداد في أقرب وقت لتفادي انقطاع الجلسات.\n\n";
        $msg .= "شكراً لتعاونكم معنا.";

        return $this->sendMessage($parentPhone, $msg);
    }
}
