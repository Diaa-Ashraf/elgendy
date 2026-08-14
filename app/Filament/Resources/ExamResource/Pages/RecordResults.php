<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecordResults extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ExamResource::class;

    protected static string $view = 'filament.resources.exam-resource.pages.record-results';

    protected static ?string $title = 'رصد درجات الامتحان';

    public Exam $exam;

    public function mount(int $record): void
    {
        $this->exam = Exam::with(['educationalStage', 'subject'])->findOrFail($record);
    }

    public function getSubheading(): ?string
    {
        return "{$this->exam->title} — {$this->exam->educationalStage?->name} — {$this->exam->subject?->name} — الدرجة الكلية: {$this->exam->total_marks}";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->select('students.*')
                    ->where('students.stage_id', $this->exam->stage_id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('educationalStage.name')
                    ->label('المرحلة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_mark')
                    ->label('الدرجة المسجلة')
                    ->state(function (Student $record): string {
                        $result = ExamResult::where('exam_id', $this->exam->id)
                            ->where('student_id', $record->id)
                            ->first();

                        if (! $result) {
                            return 'لم تُرصد';
                        }

                        return $result->marks_obtained . ' / ' . $this->exam->total_marks;
                    })
                    ->badge()
                    ->color(function (Student $record): string {
                        $result = ExamResult::where('exam_id', $this->exam->id)
                            ->where('student_id', $record->id)
                            ->first();

                        if (! $result) {
                            return 'gray';
                        }

                        $half = $this->exam->total_marks / 2;

                        return $result->marks_obtained >= $half ? 'success' : 'danger';
                    }),

                Tables\Columns\TextColumn::make('current_notes')
                    ->label('ملاحظات')
                    ->state(function (Student $record): string {
                        $result = ExamResult::where('exam_id', $this->exam->id)
                            ->where('student_id', $record->id)
                            ->first();

                        return $result?->notes ?? '—';
                    })
                    ->color('gray'),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50])
            ->actions([
                Tables\Actions\Action::make('recordMark')
                    ->label('رصد الدرجة')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->fillForm(function (Student $record): array {
                        $result = ExamResult::where('exam_id', $this->exam->id)
                            ->where('student_id', $record->id)
                            ->first();

                        return [
                            'marks_obtained' => $result?->marks_obtained,
                            'notes' => $result?->notes,
                        ];
                    })
                    ->form([
                        Forms\Components\TextInput::make('marks_obtained')
                            ->label('الدرجة المحصلة')
                            ->numeric()
                            ->step(0.5)
                            ->required()
                            ->minValue(0)
                            ->maxValue($this->exam->total_marks ?? 100)
                            ->placeholder('مثال: 85.5')
                            ->suffix('/ ' . ($this->exam->total_marks ?? 100)),

                        Forms\Components\TextInput::make('notes')
                            ->label('ملاحظات (اختياري)')
                            ->placeholder('ملاحظة تظهر في كشف الدرجات...'),
                    ])
                    ->action(function (Student $record, array $data): void {
                        ExamResult::updateOrCreate(
                            [
                                'exam_id' => $this->exam->id,
                                'student_id' => $record->id,
                            ],
                            [
                                'marks_obtained' => (float) $data['marks_obtained'],
                                'notes' => $data['notes'] ?? null,
                            ]
                        );

                        Notification::make()
                            ->title("تم رصد درجة الطالب: {$record->name}")
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn (Student $record) => "رصد درجة: {$record->name}")
                    ->modalWidth('md'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulkRecordMarks')
                    ->label('رصد درجة موحدة للمحددين')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('marks_obtained')
                            ->label('الدرجة الموحدة')
                            ->numeric()
                            ->step(0.5)
                            ->required()
                            ->minValue(0)
                            ->maxValue($this->exam->total_marks ?? 100)
                            ->suffix('/ ' . ($this->exam->total_marks ?? 100)),

                        Forms\Components\TextInput::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('ملاحظة مشتركة...'),
                    ])
                    ->action(function ($records, array $data): void {
                        foreach ($records as $student) {
                            ExamResult::updateOrCreate(
                                [
                                    'exam_id' => $this->exam->id,
                                    'student_id' => $student->id,
                                ],
                                [
                                    'marks_obtained' => (float) $data['marks_obtained'],
                                    'notes' => $data['notes'] ?? null,
                                ]
                            );
                        }

                        Notification::make()
                            ->title("تم رصد الدرجة لـ {$records->count()} طالب")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
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
