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
            ->paginated([15, 30, 50, 100])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة أداء الامتحان')
                    ->options([
                        'attended' => 'تم رصد الدرجة (أدوا الامتحان) ✅',
                        'absent' => 'لم يؤدِ الامتحان (غائب) ❌',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === 'attended') {
                            $query->whereHas('examResults', fn ($q) => $q->where('exam_id', $this->exam->id));
                        } elseif (($data['value'] ?? null) === 'absent') {
                            $query->whereDoesntHave('examResults', fn ($q) => $q->where('exam_id', $this->exam->id));
                        }
                    }),
            ])
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

                Tables\Actions\Action::make('sendWhatsApp')
                    ->label(function (Student $record): string {
                        $hasResult = ExamResult::where('exam_id', $this->exam->id)->where('student_id', $record->id)->exists();
                        return $hasResult ? 'إرسال النتيجة 💬' : 'تنبيه غياب 💬';
                    })
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color(function (Student $record): string {
                        $hasResult = ExamResult::where('exam_id', $this->exam->id)->where('student_id', $record->id)->exists();
                        return $hasResult ? 'success' : 'danger';
                    })
                    ->url(function (Student $record): ?string {
                        $parentPhone = $record->parent_phone ?? $record->phone;
                        if (empty($parentPhone)) {
                            return null;
                        }

                        $centerName = app(\App\Services\SettingService::class)->get('center_name', 'المنظومة التعليمية');
                        $subjectName = $this->exam->subject?->name ?? 'المادة';
                        $examDate = $this->exam->date ? \Carbon\Carbon::parse($this->exam->date)->format('Y-m-d') : now()->toDateString();
                        $result = ExamResult::where('exam_id', $this->exam->id)->where('student_id', $record->id)->first();

                        if ($result) {
                            $total = $this->exam->total_marks ?? 100;
                            $pct = round(($result->marks_obtained / $total) * 100, 1);
                            $msg = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$record->name} 📊\n"
                                . "نرسل لكم بطاقة نتيجة امتحان ({$this->exam->title}) في مادة ({$subjectName}):\n\n"
                                . "▪️ الدرجة: {$result->marks_obtained} من {$total}\n"
                                . "▪️ النسبة: {$pct}%\n"
                                . "▪️ ملاحظات: " . ($result->notes ?: 'مستوى طيب ومستمر') . "\n\n"
                                . "— {$centerName}";
                        } else {
                            $msg = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$record->name} ⚠️\n"
                                . "نحيطكم علماً بأن الطالب تغيب عن أداء امتحان ({$this->exam->title}) في مادة ({$subjectName}) بتاريخ: {$examDate}.\n"
                                . "يرجى التواصل معنا لتحديد موعد الإعادة للاطمئنان على مستوى الطالب.\n\n"
                                . "— {$centerName}";
                        }

                        return \App\Services\WhatsAppNotificationService::getWhatsAppUrl($parentPhone, $msg);
                    })
                    ->openUrlInNewTab(),
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
        $actions = [
            Actions\Action::make('notifyAbsenteesPortal')
                ->label('📢 إشعار بوابة ولي الأمر للغائبين')
                ->icon('heroicon-o-bell-alert')
                ->color('danger')
                ->action(function (ExamService $examService): void {
                    $count = $examService->notifyBulkParentPortalForExam($this->exam->id, 'absent');
                    Notification::make()
                        ->title("تم إرسال {$count} تنبيه غياب لبوابة ولي الأمر بنجاح")
                        ->success()
                        ->send();
                }),
        ];

        if (\App\Services\WhatsAppNotificationService::isBulkEnabled()) {
            $actions[] = Actions\Action::make('sendBulkWhatsAppAbsentees')
                ->label('💬 واتساب لجميع الغائبين')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('تأكيد إرسال رسائل تذكير غياب الامتحان')
                ->modalDescription('سيتم إرسال رسائل تنبيه لجميع أولياء أمور الطلاب الذين لم يؤدوا الامتحان بعد.')
                ->action(function (ExamService $examService): void {
                    $res = $examService->sendBulkWhatsAppForExam($this->exam->id, 'absent');
                    Notification::make()
                        ->title("تم إرسال {$res['success']} رسالة تذكير للغائبين بنجاح")
                        ->success()
                        ->send();
                });
        }

        $actions[] = Actions\Action::make('notifyAttendeesPortal')
            ->label('📢 إرسال النتائج للبوابة للجميع')
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->action(function (ExamService $examService): void {
                $count = $examService->notifyBulkParentPortalForExam($this->exam->id, 'results');
                Notification::make()
                    ->title("تم إرسال {$count} بطاقة نتيجة لبوابة ولي الأمر بنجاح")
                    ->success()
                    ->send();
            });

        if (\App\Services\WhatsAppNotificationService::isBulkEnabled()) {
            $actions[] = Actions\Action::make('sendBulkWhatsAppResults')
                ->label('💬 إرسال النتائج واتساب للجميع')
                ->icon('heroicon-o-trophy')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('تأكيد إرسال كشف الدرجات بالواتساب')
                ->modalDescription('سيتم إرسال بطاقة النتيجة والترتيب والتقدير لجميع الطلاب الذين رُصدت لهم درجات.')
                ->action(function (ExamService $examService): void {
                    $res = $examService->sendBulkWhatsAppForExam($this->exam->id, 'results');
                    Notification::make()
                        ->title("تم إرسال {$res['success']} كشف نتيجة عبر الواتساب بنجاح")
                        ->success()
                        ->send();
                });
        }

        $actions[] = Actions\Action::make('backToExams')
            ->label('الرجوع للامتحانات')
            ->icon('heroicon-o-arrow-right')
            ->url(ExamResource::getUrl('index'))
            ->color('gray');

        return $actions;
    }
}
