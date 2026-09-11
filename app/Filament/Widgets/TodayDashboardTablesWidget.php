<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\GroupSchedule;
use App\Models\GroupSession;
use App\Models\OnlinePaymentRequest;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\StudentApplication;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TodayDashboardTablesWidget extends Widget
{
    protected static string $view = 'filament.widgets.today-dashboard-tables-widget';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $today = now()->toDateString();
        $dayKey = strtolower(now()->format('D'));
        $dayMap = [
            'sat' => 'sat', 'sun' => 'sun', 'mon' => 'mon', 'tue' => 'tue', 'wed' => 'wed', 'thu' => 'thu', 'fri' => 'fri'
        ];
        
        $currentDay = $dayMap[$dayKey] ?? 'sat';
        $year = now()->year;
        $month = now()->month;

        return Cache::remember("today_dashboard_tables_data_{$today}_{$currentDay}", 60, function () use ($currentDay, $today, $year, $month) {
            // 1. جدول حصص اليوم
            $todaySchedules = GroupSchedule::whereHas('group')
                ->select('id', 'group_id', 'day_of_week', 'time', 'room')
                ->with([
                    'group:id,name,stage_id,subject_id',
                    'group.subject:id,name',
                    'group.educationalStage:id,name',
                    'group.students:id,name,phone',
                ])
                ->where('day_of_week', $currentDay)
                ->get();


            // 2. الطلاب المتأخرين عن الدفع
            $paidStudentIds = StudentPayment::whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->pluck('student_id')
                ->unique()
                ->toArray();

            $lateStudents = Student::select('id', 'name', 'phone', 'parent_phone', 'stage_id')
                ->with('educationalStage:id,name')
                ->whereNotIn('id', $paidStudentIds)
                ->take(5)
                ->get();

            // 3. طلبات التقديم أونلاين الحديثة المعلقة
            $pendingApplications = StudentApplication::select('id', 'name', 'phone', 'parent_phone', 'stage_id', 'created_at')
                ->with('educationalStage:id,name')
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->take(4)
                ->get();


            // 4. تنبيهات الطلاب متكرري الغياب (غائب أكثر من حصتين)
            $frequentAbsentees = Student::select('id', 'name', 'phone', 'stage_id')
                ->with('educationalStage:id,name')
                ->whereHas('attendances', function ($q) {
                    $q->where('status', 'absent');
                }, '>=', 2)
                ->take(4)
                ->get();

            // 5. حصص سابقة مضى موعدها ولم يتم رصد حضورها بعد (تنبيه للمدرس)
            $unrecordedSessions = GroupSession::select('id', 'group_id', 'date', 'status')
                ->with([
                    'group:id,name,stage_id,subject_id',
                    'group.subject:id,name',
                    'group.educationalStage:id,name',
                ])
                ->where('status', 'scheduled')
                ->where('date', '<=', $today)
                ->whereDoesntHave('attendances')
                ->orderBy('date', 'desc')
                ->take(4)
                ->get();

            // 6. طلبات الدفع الإلكتروني المعلقة للاعتماد
            $pendingPayments = OnlinePaymentRequest::select('id', 'student_id', 'amount', 'payment_method', 'created_at')
                ->with('student:id,name,phone')
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->take(4)
                ->get();

            // 7. جلب الإشعارات الأخيرة من قاعدة البيانات
            $recentNotifications = DB::table('notifications')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($notification) {
                    $data = json_decode($notification->data, true);
                    return [
                        'id' => $notification->id,
                        'title' => $data['title'] ?? 'إشعار جديد',
                        'body' => $data['body'] ?? '',
                        'created_at' => Carbon::parse($notification->created_at)->diffForHumans(),
                        'read_at' => $notification->read_at,
                    ];
                });

            return [
                'todaySchedules' => $todaySchedules,
                'lateStudents' => $lateStudents,
                'pendingApplications' => $pendingApplications,
                'frequentAbsentees' => $frequentAbsentees,
                'unrecordedSessions' => $unrecordedSessions,
                'pendingPayments' => $pendingPayments,
                'recentNotifications' => $recentNotifications,
            ];
        });
    }
}

