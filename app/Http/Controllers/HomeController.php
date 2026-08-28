<?php

namespace App\Http\Controllers;

use App\Models\EducationalStage;
use App\Models\Group;
use App\Models\StudentApplication;
use App\Services\SettingService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(SettingService $settingService)
    {
        $settings = $settingService->allWithDefaults([
            // 1. الهوية والسنتر
            'center_name' => 'سنتر الأستاذ محمد الجندي التعليمي',
            'center_phone' => '01000000000',
            'center_whatsapp' => '01000000000',
            'center_address' => 'القاهرة - مصر',
            'academic_year' => '2026/2027',
            'currency_symbol' => 'ج.م',
            'center_logo' => '',
            'site_favicon' => '',

            // 2. ملف ونبذة المعلم
            'teacher_name' => 'الأستاذ محمد الجندي',
            'teacher_title' => 'كبير معلمي المادة والمستشار التربوي المعتمد',
            'teacher_subject' => 'المادة الأكاديمية التخصصية',
            'teacher_experience_years' => '+14',
            'teacher_students_count' => '+10,000',
            'teacher_image' => '',
            'teacher_quote' => 'الدرجة النهائية ليست وليدة الصدفة، بل هي نتيجة نظام محكم يربط الشرح الوافي بالمتابعة الحازمة والاختبارات الأسبوعية.',
            'teacher_bio_heading' => 'رحلة أكاديمية هدفها الأول تحويل صعوبة المادة إلى شغف وتفوق دائم',
            'teacher_bio' => 'صاحب مسيرة تعليمية ممتدة لأكثر من 14 عاماً خرّجت آلاف الطلاب المتفوقين وأوائل الجمهورية والمحافظات، معتمدين على أسلوب الفهم التحليلي والخرائط الذهنية وتدريبات الامتحانات الشاملة.',

            // منهجية التدريس
            'methodology_1_title' => 'الربط المنطقي والمفاهيمي',
            'methodology_1_desc' => 'شرح القوانين والقواعد من جذورها حتى يرسخ المفهوم في ذهن الطالب دون نسيان.',
            'methodology_2_title' => 'حل آلاف المسائل المتدرجة',
            'methodology_2_desc' => 'تدريب مستمر على مستويات التفكير العليا والأنماط الامتحانية الجديدة بدقة تامة.',
            'methodology_3_title' => 'متابعة فردية صارمة',
            'methodology_3_desc' => 'فريق مساعدين مؤهل يصحح الواجبات ويرصد الحضور والامتحانات لحظياً.',
            'methodology_4_title' => 'تقييمات أسبوعية ومحاكاة',
            'methodology_4_desc' => 'امتحانات محاكاة مطابقة لنظام الوزارة لكسر رهبة الامتحانات النهائية مبكراً.',

            // 3. نصوص الهيرو والمميزات
            'hero_badge_text' => 'المنظومة التعليمية الرائدة للتميز والتفوق الأكاديمي',
            'hero_title_prefix' => 'صناعة التفوق تبدأ مع',
            'hero_description' => 'منهجية تدريس مبسطة ترتكز على استيعاب المفاهيم، حل آلاف الأسئلة الامتحانية، ونظام متابعة ذكي ودقيق لكل طالب.',
            'trust_stat_1' => 'متابعة وتقييم أسبوعي',
            'trust_stat_2' => 'بوابة رقمية لولي الأمر',
            'trust_stat_3' => 'بنوك أسئلة واختبارات دورية',

            // ركائز الهيرو
            'hero_pillar_1_title' => 'تفوق مستمر',
            'hero_pillar_1_desc' => 'خطط دراسية محكمة ونتائج ملموسة',
            'hero_pillar_2_title' => 'تقارير فورية',
            'hero_pillar_2_desc' => 'إشعار ولي الأمر بعد كل حصة',
            'hero_pillar_3_title' => 'حضور ذكي QR',
            'hero_pillar_3_desc' => 'حضور وانصراف ذكي بدون تأخير',
            'hero_pillar_4_title' => 'بنوك أسئلة',
            'hero_pillar_4_desc' => 'تغطية لكافة أفكار امتحانات الوزارة',

            // كروت المميزات
            'feature_1_title' => 'مذكرات شرح وتدريبات احترافية',
            'feature_1_desc' => 'مذكرات مطبوعة بجودة عالية تشمل خرائط مفاهيمية، أمثلة محلولة بالتفصيل، وتدريبات متدرجة من الأساسيات حتى مستوى الامتحانات التنافسية.',
            'feature_1_tag' => 'تحديث دوري ومستمر للمناهج',

            'feature_2_title' => 'بوابة إلكترونية لولي الأمر',
            'feature_2_desc' => 'تسجيل دخول سهل للاطلاع على نسبة حضور الطالب، درجات الامتحانات الشهرية والأسبوعية، ورصد الرصيد المالي وحالة تسليم المذكرات.',
            'feature_2_tag' => 'شفافية كاملة ومتابعة مباشرة',

            'feature_3_title' => 'اختبارات دورية وتحفيز دائم',
            'feature_3_desc' => 'امتحان أسبوعي بعد كل درس، مع لوحة شرف للمتميزين وخصومات تشجيعية للطلاب الحاصلين على الدرجات النهائية تحفيزاً للاستمرار.',
            'feature_3_tag' => 'نظام حوافز ومكافآت التميز',

            // 4. السوشيال ميديا والفوتر
            'facebook_url' => '',
            'youtube_url' => '',
            'telegram_url' => '',
            'footer_copyright_text' => 'جميع الحقوق محفوظة — المنظومة التعليمية الرسمية',
        ]);

        $stages = EducationalStage::withCount('students')->get();
        $groups = Group::with(['educationalStage', 'subject', 'schedules'])
            ->where('status', 'active')
            ->get();

        return view('landing', compact('settings', 'stages', 'groups'));
    }

    public function submitEnrollment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stage_id' => 'required|exists:educational_stages,id',
            'group_id' => 'nullable|exists:groups,id',
            'gender' => 'required|in:male,female',
            'parent_phone' => 'required|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'name.required' => 'يرجى إدخال اسم الطالب ثلاثي أو رباعي',
            'stage_id.required' => 'يرجى اختيار المرحلة الدراسية',
            'gender.required' => 'يرجى تحديد الجنس',
            'parent_phone.required' => 'يرجى إدخال رقم هاتف ولي الأمر',
        ]);

        $application = StudentApplication::create($validated);

        // إرسال إشعار لحظي في أعلا اللوحة (Bell Icon Notification)
        try {
            $stageName = $application->educationalStage?->name ?? 'غير محددة';
            \App\Services\NotificationService::notifyNewOnlineApplication(
                $application->name,
                $stageName,
                $application->id
            );
        } catch (\Throwable $e) {
            // Log notification fail if any
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم طلب الالتحاق بنجاح! سيتم التواصل معكم قريباً بعد مراجعة الطلب.',
            'application_id' => $application->id,
        ]);
    }

    public function getGroupsByStage($stageId)
    {
        $groups = Group::where('stage_id', $stageId)
            ->where('status', 'active')
            ->get(['id', 'name', 'price_per_month']);

        return response()->json($groups);
    }
}
