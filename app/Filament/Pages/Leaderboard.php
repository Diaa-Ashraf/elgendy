<?php

namespace App\Filament\Pages;

use App\Models\EducationalStage;
use App\Models\Group;
use App\Services\LeaderboardService;
use Filament\Pages\Page;

class Leaderboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static string $view = 'filament.pages.leaderboard';

    protected static ?string $navigationLabel = 'لوحة الشرف والأوائل';

    protected static ?string $title = ' لوحة الشرف وأوائل المنظومة';

    protected static ?string $navigationGroup = 'التقارير والإحصائيات';

    protected static ?int $navigationSort = 2;

    public ?int $selectedStage = null;
    public ?int $selectedGroup = null;

    protected $memoizedTopStudents = null;

    public function updatedSelectedStage(): void
    {
        $this->selectedGroup = null;
        $this->memoizedTopStudents = null;
    }

    public function updatedSelectedGroup(): void
    {
        $this->memoizedTopStudents = null;
    }

    public function getTopStudentsProperty()
    {
        if ($this->memoizedTopStudents !== null) {
            return $this->memoizedTopStudents;
        }

        return $this->memoizedTopStudents = app(LeaderboardService::class)->getTopStudents(
            stageId: $this->selectedStage,
            groupId: $this->selectedGroup,
            limit: 20
        );
    }

    public function getStagesProperty()
    {
        return \Illuminate\Support\Facades\Cache::remember('leaderboard_stages_list', 60, function () {
            return EducationalStage::select('id', 'name')->get();
        });
    }

    public function getGroupsProperty()
    {
        $stageId = $this->selectedStage;
        $cacheKey = 'leaderboard_groups_list_' . ($stageId ?? 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($stageId) {
            $query = Group::select('id', 'name', 'stage_id')->where('status', 'active');
            if ($stageId) {
                $query->where('stage_id', $stageId);
            }
            return $query->get();
        });
    }

    protected function getViewData(): array
    {
        return [
            'topStudents' => $this->topStudents,
            'stages' => $this->stages,
            'groups' => $this->groups,
        ];
    }
}
