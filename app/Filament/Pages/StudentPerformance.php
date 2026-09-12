<?php

namespace App\Filament\Pages;

use App\Models\Student;
use App\Services\StudentPerformanceService;
use Filament\Pages\Page;

class StudentPerformance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.student-performance';

    protected static ?string $navigationLabel = 'تحليلات أداء الطلاب';

    protected static ?string $title = '📊 لوحة تحليلات ومتابعة أداء الطالب';

    protected static ?string $navigationGroup = 'التقارير والإحصائيات';

    protected static ?int $navigationSort = 3;

    public ?int $selectedStudentId = null;

    protected $memoizedAnalytics = null;
    protected $memoizedStudentsList = null;

    public function mount()
    {
        $this->selectedStudentId = request('student_id') ?? Student::first()?->id;
    }

    public function updatedSelectedStudentId(): void
    {
        $this->memoizedAnalytics = null;
    }

    public function getAnalyticsProperty()
    {
        if (! $this->selectedStudentId) {
            return null;
        }

        if ($this->memoizedAnalytics !== null) {
            return $this->memoizedAnalytics;
        }

        return $this->memoizedAnalytics = app(StudentPerformanceService::class)->getStudentAnalytics($this->selectedStudentId);
    }

    public function getStudentsListProperty()
    {
        if ($this->memoizedStudentsList !== null) {
            return $this->memoizedStudentsList;
        }

        return $this->memoizedStudentsList = \Illuminate\Support\Facades\Cache::remember('student_perf_dropdown_list', 60, function () {
            return Student::select('id', 'name', 'qr_code')
                ->orderBy('name')
                ->get();
        });
    }

    public function getAtRiskStudentsProperty(): array
    {
        return app(StudentPerformanceService::class)->getAtRiskStudents(12);
    }

    protected function getViewData(): array
    {
        return [
            'analytics' => $this->analytics,
            'studentsList' => $this->studentsList,
            'atRiskStudents' => $this->atRiskStudents,
        ];
    }
}


