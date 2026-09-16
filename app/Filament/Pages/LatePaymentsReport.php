<?php

namespace App\Filament\Pages;

use App\Models\EducationalStage;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Services\NotificationService;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class LatePaymentsReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'متأخرات الاشتراكات';

    protected static ?string $title = 'كشف الطلاب المتأخرين في سداد الاشتراكات';

    protected static ?string $navigationGroup = 'الإدارة المالية';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'late-payments';

    protected static string $view = 'filament.pages.late-payments-report';

    public ?int $selectedStage = null;
    public ?int $selectedGroup = null;
    public ?string $selectedMonth = null;
    public ?string $statusFilter = 'all'; // all, unpaid, partial
    public ?string $search = '';

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public static function canAccess(): bool
    {
        return (bool) Auth::user();
    }

    public function updatedSelectedStage(): void
    {
        $this->selectedGroup = null;
    }

    public function getStagesProperty(): Collection
    {
        return EducationalStage::select('id', 'name')->orderBy('id')->get();
    }

    public function getGroupsProperty(): Collection
    {
        $query = Group::select('id', 'name', 'stage_id', 'subject_id', 'price_per_month')
            ->with(['subject:id,name', 'educationalStage:id,name'])
            ->where('status', 'active');

        if ($this->selectedStage) {
            $query->where('stage_id', $this->selectedStage);
        }

        return $query->get();
    }

    public function getAvailableMonthsProperty(): array
    {
        $months = [];
        $current = now()->startOfMonth();
        for ($i = 0; $i < 6; $i++) {
            $m = $current->copy()->subMonths($i);
            $key = $m->format('Y-m');
            $label = $this->getArabicMonthLabel($m);
            $months[$key] = $label;
        }
        return $months;
    }

    public function getArabicMonthLabel(Carbon $date): string
    {
        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        return ($arabicMonths[$date->month] ?? $date->month) . ' ' . $date->year;
    }

    public function getDefaultersDataProperty(): array
    {
        $monthStr = $this->selectedMonth ?: now()->format('Y-m');
        $targetDate = Carbon::parse($monthStr . '-01');
        $year = (int) $targetDate->year;
        $month = (int) $targetDate->month;

        // استعلام الطلاب مع العلاقات الأساسية
        $studentsQuery = Student::select('id', 'name', 'phone', 'parent_phone', 'stage_id', 'discount_id', 'created_at')
            ->with([
                'educationalStage:id,name',
                'discount:id,name,type,value,is_active',
                'groups' => function ($q) {
                    $q->select('groups.id', 'groups.name', 'groups.stage_id', 'groups.subject_id', 'groups.price_per_month')
                        ->with('subject:id,name');
                },
            ]);

        if ($this->selectedStage) {
            $studentsQuery->where('stage_id', $this->selectedStage);
        }

        if ($this->selectedGroup) {
            $studentsQuery->whereHas('groups', function ($q) {
                $q->where('groups.id', $this->selectedGroup);
            });
        }

        if (! empty($this->search)) {
            $term = trim($this->search);
            $studentsQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('id', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('parent_phone', 'like', "%{$term}%");
            });
        }

        $students = $studentsQuery->get();

        // جلب كل مدفوعات هذا الشهر دفعة واحدة لتقليل الاستعلامات (O(1) lookup)
        $payments = StudentPayment::whereIn('student_id', $students->pluck('id'))
            ->where(function ($q) use ($year, $month) {
                $q->where(function ($sub) use ($year, $month) {
                    $sub->where('type', 'month')
                        ->whereYear('period_month', $year)
                        ->whereMonth('period_month', $month);
                })->orWhere(function ($sub) use ($year, $month) {
                    $sub->where('type', 'session')
                        ->whereYear('paid_at', $year)
                        ->whereMonth('paid_at', $month);
                });
            })
            ->select('id', 'student_id', 'group_id', 'amount', 'type', 'period_month', 'paid_at')
            ->get();

        $paymentsGrouped = $payments->groupBy(function ($item) {
            return $item->student_id . '_' . ($item->group_id ?? 0);
        });

        $lateRecords = collect();
        $totalCollectedThisMonth = (float) $payments->sum('amount');
        $totalRequired = 0.0;
        $totalDue = 0.0;

        foreach ($students as $student) {
            $enrolledGroups = $student->groups;

            if ($this->selectedGroup) {
                $enrolledGroups = $enrolledGroups->where('id', $this->selectedGroup);
            }

            if ($enrolledGroups->isEmpty()) {
                // طالب بدون مجموعة مسجلة
                $key = $student->id . '_0';
                $studentPayments = $paymentsGrouped->get($key, collect());
                $paidAmount = (float) $studentPayments->sum('amount');

                if ($paidAmount <= 0) {
                    $lateRecords->push([
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                        'student_code' => 'STD-' . str_pad((string) $student->id, 4, '0', STR_PAD_LEFT),
                        'stage_name' => $student->educationalStage?->name ?? 'غير محدد',
                        'group_id' => null,
                        'group_name' => 'غير مسجل بمجموعة حالياً',
                        'subject_name' => 'عام',
                        'required_amount' => 0.0,
                        'paid_amount' => 0.0,
                        'due_amount' => 0.0,
                        'status' => 'unpaid',
                        'parent_phone' => $student->parent_phone,
                        'student_phone' => $student->phone,
                    ]);
                }
                continue;
            }

            foreach ($enrolledGroups as $group) {
                $basePrice = (float) $group->price_per_month;

                // تطبيق الخصم إن وجد
                if ($student->discount && $student->discount->is_active) {
                    $disc = $student->discount;
                    if ($disc->type === 'percentage') {
                        $basePrice -= ($basePrice * ($disc->value / 100));
                    } else {
                        $basePrice = max(0, $basePrice - $disc->value);
                    }
                }

                $key = $student->id . '_' . $group->id;
                $groupPayments = $paymentsGrouped->get($key, collect());
                $paidAmount = (float) $groupPayments->sum('amount');
                $dueAmount = max(0, $basePrice - $paidAmount);

                $totalRequired += $basePrice;

                if ($dueAmount > 0) {
                    $totalDue += $dueAmount;
                    $status = ($paidAmount > 0) ? 'partial' : 'unpaid';

                    if ($this->statusFilter === 'all' || $this->statusFilter === $status) {
                        $lateRecords->push([
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'student_code' => 'STD-' . str_pad((string) $student->id, 4, '0', STR_PAD_LEFT),
                            'stage_name' => $student->educationalStage?->name ?? 'غير محدد',
                            'group_id' => $group->id,
                            'group_name' => $group->name,
                            'subject_name' => $group->subject?->name ?? 'المادة الأساسية',
                            'required_amount' => $basePrice,
                            'paid_amount' => $paidAmount,
                            'due_amount' => $dueAmount,
                            'status' => $status,
                            'parent_phone' => $student->parent_phone,
                            'student_phone' => $student->phone,
                        ]);
                    }
                }
            }
        }

        $totalDefaultersCount = $lateRecords->pluck('student_id')->unique()->count();
        $collectionRate = ($totalRequired > 0) 
            ? round(($totalCollectedThisMonth / max(1, $totalRequired)) * 100, 1) 
            : 0;

        return [
            'records' => $lateRecords->sortByDesc('due_amount')->values(),
            'stats' => [
                'total_records' => $lateRecords->count(),
                'total_students' => $totalDefaultersCount,
                'total_due' => $totalDue,
                'total_collected' => $totalCollectedThisMonth,
                'collection_rate' => min(100, $collectionRate),
                'month_label' => $this->getArabicMonthLabel($targetDate),
            ],
        ];
    }

    /**
     * إرسال إشعار تذكير بالسداد لبوابة ولي الأمر
     */
    public function notifyParentPortal(int $studentId, string $groupName, float $dueAmount): void
    {
        $student = Student::find($studentId);
        if (! $student) {
            return;
        }

        $monthLabel = $this->getArabicMonthLabel(Carbon::parse(($this->selectedMonth ?: now()->format('Y-m')) . '-01'));

        NotificationService::notifyParent(
            studentId: $student->id,
            type: 'payment',
            title: 'تذكير بمستحقات الاشتراك الشهري 💰',
            message: "المكرم ولي أمر الطالب {$student->name}، نود تذكيركم بسداد اشتراك شهر ({$monthLabel}) لمجموعة ({$groupName}) بقيمة {$dueAmount} ج.م.",
            actionUrl: '/parent/payments'
        );

        Notification::make()
            ->title("تم إرسال إشعار تذكير لبوابة ولي أمر الطالب ({$student->name}) بنجاح")
            ->success()
            ->send();
    }

    /**
     * إرسال تذكير واتساب لجميع المتأخرين المحددين
     */
    public function sendBulkWhatsApp(): void
    {
        $data = $this->defaultersData;
        $records = $data['records'] ?? collect();

        if ($records->isEmpty()) {
            Notification::make()
                ->title('لا يوجد طلاب متأخرون في القائمة المحددة')
                ->warning()
                ->send();
            return;
        }

        $waService = app(WhatsAppNotificationService::class);
        $monthLabel = $data['stats']['month_label'] ?? 'الشهر الحالي';
        $sentCount = 0;

        foreach ($records as $record) {
            $phone = $record['parent_phone'] ?: $record['student_phone'];
            if (! $phone) {
                continue;
            }

            $studentName = $record['student_name'];
            $groupName = $record['group_name'];
            $due = $record['due_amount'];

            $msg = "السلام عليكم ورحمة الله 💰\n\n";
            $msg .= "المكرم ولي أمر الطالب/ة: *{$studentName}*\n";
            $msg .= "نود تذكيركم بلطف بموعد سداد اشتراك شهر ({$monthLabel}) لمجموعة *{$groupName}*.\n";
            $msg .= "المبلغ المستحق: *{$due} ج.م*\n\n";
            $msg .= "يرجى التكرم بالسداد في أقرب وقت حرصاً على انتظام الطالب.\n";
            $msg .= "شاكرين حسن تعاونكم معنا — سنتر الأستاذ محمد الغندي.";

            if (WhatsAppNotificationService::isBulkEnabled()) {
                $waService->sendMessage($phone, $msg);
                $sentCount++;
            }
        }

        Notification::make()
            ->title("تم تجهيز وبدء إرسال تذكيرات الواتساب لـ ({$records->count()}) طالب")
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $defaulters = $this->defaultersData;

        return [
            'records' => $defaulters['records'],
            'stats' => $defaulters['stats'],
            'stages' => $this->stages,
            'groups' => $this->groups,
            'months' => $this->availableMonths,
        ];
    }
}
