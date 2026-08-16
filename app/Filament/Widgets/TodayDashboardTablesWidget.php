<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\GroupSchedule;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\StudentApplication;

class TodayDashboardTablesWidget extends Widget
{
    protected static string $view = 'filament.widgets.today-dashboard-tables-widget';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $dayKey = strtolower(now()->format('D'));
        $dayMap = [
            'sat' => 'sat', 'sun' => 'sun', 'mon' => 'mon', 'tue' => 'tue', 'wed' => 'wed', 'thu' => 'thu', 'fri' => 'fri'
        ];
        
        $currentDay = $dayMap[$dayKey] ?? 'sat';

        // جدول حصص اليوم
        $todaySchedules = GroupSchedule::whereHas('group')
            ->with(['group.subject', 'group.educationalStage', 'group.students'])
            ->where('day_of_week', $currentDay)
            ->get();

        // الطلاب المتأخرين عن الدفع
        $paidStudentIds = StudentPayment::whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->pluck('student_id')
            ->unique();

        $lateStudents = Student::with('educationalStage')
            ->whereNotIn('id', $paidStudentIds)
            ->take(5)
            ->get();

        // طلبات التقديم أونلاين الحديثة المعلقة
        $pendingApplications = StudentApplication::with('educationalStage')
            ->where('status', 'pending')
            ->orderBy('id', 'desc')
            ->take(4)
            ->get();

        // جلب الإشعارات الأخيرة من قاعدة البيانات
        $recentNotifications = \Illuminate\Support\Facades\DB::table('notifications')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($notification) {
                $data = json_decode($notification->data, true);
                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'إشعار جديد',
                    'body' => $data['body'] ?? '',
                    'created_at' => \Carbon\Carbon::parse($notification->created_at)->diffForHumans(),
                    'read_at' => $notification->read_at,
                ];
            });

        return [
            'todaySchedules' => $todaySchedules,
            'lateStudents' => $lateStudents,
            'pendingApplications' => $pendingApplications,
            'recentNotifications' => $recentNotifications,
        ];
    }
}
