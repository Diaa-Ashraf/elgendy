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

    protected ?array $memoizedTimetable = null;

    public function getTimetableData(): array
    {
        if ($this->memoizedTimetable !== null) {
            return $this->memoizedTimetable;
        }

        $stageId = $this->selected_stage_id;
        $cacheKey = 'weekly_timetable_' . ($stageId ?? 'all');

        return $this->memoizedTimetable = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($stageId) {
            $days = [
                'sat' => 'السبت',
                'sun' => 'الأحد',
                'mon' => 'الإثنين',
                'tue' => 'الثلاثاء',
                'wed' => 'الأربعاء',
                'thu' => 'الخميس',
                'fri' => 'الجمعة',
            ];

            $query = GroupSchedule::whereHas('group', function ($q) use ($stageId) {
                if ($stageId) {
                    $q->where('stage_id', $stageId);
                }
            })
            ->select('id', 'group_id', 'day_of_week', 'time', 'room')
            ->with([
                'group:id,name,stage_id,subject_id',
                'group.subject:id,name',
                'group.educationalStage:id,name',
            ]);

            $schedules = $query->get()->groupBy('day_of_week');

            return [
                'days' => $days,
                'schedules' => $schedules,
                'stages' => EducationalStage::pluck('name', 'id')->toArray(),
            ];
        });
    }
}

