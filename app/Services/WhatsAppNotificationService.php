<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * التحقق مما إذا كانت بوابة الواتساب التلقائية مفعلة ومضبوطة في الإعدادات
     */
    public static function isBulkEnabled(): bool
    {
        $settingService = app(\App\Services\SettingService::class);
        $enabled = (bool) $settingService->get('whatsapp_gateway_enabled', false);
        $apiUrl = $settingService->get('whatsapp_api_url');
        $apiKey = $settingService->get('whatsapp_api_key');

        return $enabled && !empty($apiUrl) && !empty($apiKey);
    }

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

        $settingService = app(\App\Services\SettingService::class);
        $apiUrl = $settingService->get('whatsapp_api_url');
        $apiKey = $settingService->get('whatsapp_api_key');
        $instanceId = $settingService->get('whatsapp_instance_id');

        if (! $apiUrl || ! $apiKey) {
            Log::info("WhatsApp API not configured. Message to {$cleanPhone}: {$message}");
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
     * إرسال رسالة واتساب يدوية
     */
    public function sendManualMessage(string $to, string $message): bool
    {
        return $this->sendMessage($to, $message);
    }

    /**
     * توليد رابط واتساب المباشر المجاني (wa.me)
     */
    public static function getWhatsAppUrl(string $phone, string $message): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '01')) {
            $cleanPhone = '2' . $cleanPhone;
        }

        return 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($message);
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
        $rating = $percentage >= 85 ? 'ممتاز ' : ($percentage >= 70 ? 'جيد جداً 👍' : 'يحتاج لمتابعة 📝');

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

    /**
     * إشعار تأكيد واعتماد الدفع الإلكتروني
     */
    public function notifyOnlinePaymentApproved(string $parentPhone, string $studentName, float $amount, string $methodName, ?string $groupName = null): bool
    {
        $msg = "إشعار سداد إلكتروني ناجح ✅\n\n";
        $msg .= "المكرم ولي أمر الطالب/ة: {$studentName}\n";
        $msg .= "نحيطكم علماً بأنه تم استلام وتأكيد سداد مبلغ ({$amount} ج.م) بنجاح عبر ({$methodName}).\n";
        if ($groupName) {
            $msg .= "المجموعة: {$groupName}\n";
        }
        $msg .= "تم تسجيل الدفعة وتحديث كشف حساب الطالب في النظام فوراً.\n\n";
        $msg .= "شاكرين لكم حسن تعاونكم وحرصكم الدائم.";

        return $this->sendMessage($parentPhone, $msg);
    }

    /**
     * إشعار رفض إيصال الدفع الإلكتروني
     */
    public function notifyOnlinePaymentRejected(string $parentPhone, string $studentName, float $amount, string $reason): bool
    {
        $msg = "تنبيه بخصوص إيصال السداد الإلكتروني ⚠️\n\n";
        $msg .= "المكرم ولي أمر الطالب/ة: {$studentName}\n";
        $msg .= "نود إحاطتكم بأنه تعذر قبول إيصال السداد بقيمة ({$amount} ج.م) للأسباب التالية:\n";
        $msg .= "❌ السبب: {$reason}\n\n";
        $msg .= "يرجى مراجعة إدارة السنتر أو إعادة رفع إشعار التحويل الصحيح عبر بوابة ولي الأمر.\n";
        $msg .= "شاكرين تفهمكم.";

        return $this->sendMessage($parentPhone, $msg);
    }

    /**
     * إشعار حضور/غياب تلقائي عند مسح QR (مع Rate Limiting)
     * يرسل رسالة واحدة لكل طالب لكل حصة فقط لمنع البلوك
     */
    public function sendAttendanceNotification(
        \App\Models\Student $student,
        \App\Models\GroupSession $session,
        string $status = 'present'
    ): bool {
        // Rate Limiting: رسالة واحدة لكل طالب لكل حصة
        $cacheKey = "wa_attendance_{$student->id}_{$session->id}";
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return false; // تم الإرسال مسبقاً
        }

        $phone = $student->parent_phone;
        if (empty($phone)) {
            return false;
        }

        $settingService = app(SettingService::class);
        $centerName = $settingService->get('center_name', 'المنظومة التعليمية');
        $groupName = $session->group?->name ?? 'غير محدد';
        $date = now()->format('Y-m-d');
        $time = now()->format('h:i A');

        if ($status === 'present') {
            $msg = "السلام عليكم ورحمة الله ✅\n\n";
            $msg .= "ولي أمر الطالب/ة: *{$student->name}*\n";
            $msg .= "نفيدكم بأن ابنكم قد حضر الحصة بنجاح.\n\n";
            $msg .= "📚 المجموعة: {$groupName}\n";
            $msg .= "📅 التاريخ: {$date}\n";
            $msg .= "⏰ وقت الحضور: {$time}\n\n";
            $msg .= "— {$centerName}";
        } else {
            $msg = "السلام عليكم ورحمة الله ⚠️\n\n";
            $msg .= "ولي أمر الطالب/ة: *{$student->name}*\n";
            $msg .= "نود إحاطتكم بأن ابنكم لم يحضر حصة اليوم.\n\n";
            $msg .= "📚 المجموعة: {$groupName}\n";
            $msg .= "📅 التاريخ: {$date}\n\n";
            $msg .= "يرجى التواصل معنا في حالة وجود عذر.\n";
            $msg .= "— {$centerName}";
        }

        // Rate Limit: تأخير بين الرسائل (3 ثوان بين كل رسالة)
        $globalKey = 'wa_last_sent_at';
        $lastSent = \Illuminate\Support\Facades\Cache::get($globalKey, 0);
        $now = microtime(true);
        $diff = $now - $lastSent;
        if ($diff < 3) {
            usleep((int) ((3 - $diff) * 1000000));
        }

        $result = $this->sendMessage($phone, $msg);

        // تسجيل الإرسال لمنع التكرار (صالح لمدة 24 ساعة)
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(24));
        \Illuminate\Support\Facades\Cache::put($globalKey, microtime(true), now()->addMinutes(5));

        return $result;
    }
}
