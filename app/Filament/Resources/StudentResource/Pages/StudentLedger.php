<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use App\Services\StudentLedgerService;
use Filament\Actions;
use Filament\Resources\Pages\Page;

class StudentLedger extends Page
{
    protected static string $resource = StudentResource::class;

    protected static string $view = 'filament.resources.student-resource.pages.student-ledger';

    protected static ?string $title = 'كشف حساب الطالب';

    public Student $student;
    public array $ledger = [];

    public function mount(int $record): void
    {
        $this->student = Student::with(['educationalStage', 'groups.subject'])->findOrFail($record);

        $ledgerService = app(StudentLedgerService::class);
        $this->ledger = $ledgerService->getFullLedger($this->student);
    }

    public function getSubheading(): ?string
    {
        return "{$this->student->name} — {$this->student->educationalStage?->name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToStudent')
                ->label('الرجوع لبيانات الطالب')
                ->icon('heroicon-o-arrow-right')
                ->url(StudentResource::getUrl('edit', ['record' => $this->student]))
                ->color('gray'),

            Actions\Action::make('printLedger')
                ->label('طباعة كشف الحساب PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(route('student.ledger.pdf', $this->student->id))
                ->openUrlInNewTab(),
        ];
    }
}
