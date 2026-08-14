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
        $settings = [
            'center_name' => $settingService->get('center_name', 'سنتر الأستاذ محمد الغندي التعليمي'),
            'center_phone' => $settingService->get('center_phone', '01000000000'),
            'center_address' => $settingService->get('center_address', 'القاهرة - مصر'),
            'academic_year' => $settingService->get('academic_year', '2026/2027'),
            'center_logo' => $settingService->get('center_logo', ''),
            'site_favicon' => $settingService->get('site_favicon', ''),
        ];

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
