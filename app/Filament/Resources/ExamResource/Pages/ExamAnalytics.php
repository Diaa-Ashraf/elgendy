<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use App\Models\Exam;
use App\Services\OnlineExamService;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class ExamAnalytics extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ExamResource::class;

    protected static string $view = 'filament.resources.exam-resource.pages.exam-analytics';

    protected static ?string $title = 'تحليل أداء الاختبار ونقاط الضعف الشائعة';

    public array $analytics = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->record->load(['educationalStage', 'subject', 'questions']);

        $this->analytics = app(OnlineExamService::class)->getWeakPointsAnalytics($this->record);
    }

    public function getSubheading(): ?string
    {
        return "{$this->record->title} — {$this->record->educationalStage?->name} — {$this->record->subject?->name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToExams')
                ->label('الرجوع لقائمة الامتحانات')
                ->icon('heroicon-o-arrow-right')
                ->url(ExamResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
