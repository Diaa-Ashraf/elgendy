<?php

namespace App\Filament\Pages;

use App\Models\EducationalStage;
use App\Models\GroupSchedule;
use App\Models\Subject;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class WeeklyTimetable extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'الجدول الأسبوعي التفاعلي';

    protected static ?string $title = 'الجدول الأسبوعي للمجموعات والدراسة';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.weekly-timetable';

    public ?int $selected_stage_id = null;

    public static function canAccess(): bool
    {
        return (bool) Auth::user();
    }

    public function getTimetableData(): array
    {
        $days = [
            'sat' => 'السبت',
            'sun' => 'الأحد',
            'mon' => 'الإثنين',
            'tue' => 'الثلاثاء',
            'wed' => 'الأربعاء',
            'thu' => 'الخميس',
            'fri' => 'الجمعة',
        ];

        $query = GroupSchedule::with(['group.subject', 'group.educationalStage']);

        if ($this->selected_stage_id) {
            $query->whereHas('group', function ($q) {
                $q->where('stage_id', $this->selected_stage_id);
            });
        }

        $schedules = $query->get()->groupBy('day_of_week');

        return [
            'days' => $days,
            'schedules' => $schedules,
            'stages' => EducationalStage::pluck('name', 'id'),
        ];
    }
}
