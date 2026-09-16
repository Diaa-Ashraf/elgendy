<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use App\Services\ExamService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $modelLabel = 'امتحان';

    protected static ?string $pluralModelLabel = 'الامتحانات والنتائج';

    protected static ?string $navigationLabel = 'الامتحانات والنتائج';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الامتحان الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان الامتحان')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: اختبار شهر أكتوبر في الفيزياء'),

                        Forms\Components\Select::make('stage_id')
                            ->label('المرحلة الدراسية')
                            ->relationship('educationalStage', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('subject_id')
                            ->label('المادة الدراسية')
                            ->relationship('subject', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('exam_type')
                            ->label('نوع الامتحان')
                            ->options([
                                'monthly' => 'اختبار شهري',
                                'quiz' => 'كويز سريع (Quiz)',
                                'midterm' => 'منتصف الفصل (Midterm)',
                                'final' => 'اختبار نهائي (Final)',
                            ])
                            ->default('monthly')
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('total_marks')
                            ->label('الدرجة الكلية (النهائية)')
                            ->numeric()
                            ->default(100)
                            ->required()
                            ->minValue(1),

                        Forms\Components\DatePicker::make('date')
                            ->label('تاريخ عقد الامتحان')
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('إعدادات الاختبار الإلكتروني (Online Quiz)')
                    ->schema([
                        Forms\Components\Toggle::make('is_online')
                            ->label('تفعيل كاختبار إلكتروني أونلاين')
                            ->helperText('عند التفعيل، سيتمكن الطلاب من حل الامتحان أونلاين وتصحيحه ذاتياً.')
                            ->reactive()
                            ->default(false),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('مدة الامتحان للطالب (بالدقائق)')
                            ->numeric()
                            ->placeholder('مثال: 10 أو 30')
                            ->helperText('الوقت المتاح للطالب بمجرد البدء. إذا تركته فارغاً فسيتم احتسابه تلقائياً من فترة فتح وإغلاق الامتحان.')
                            ->visible(fn ($get) => (bool) $get('is_online')),

                        Forms\Components\TextInput::make('pass_percentage')
                            ->label('نسبة النجاح (%)')
                            ->numeric()
                            ->default(50)
                            ->minValue(1)
                            ->maxValue(100)
                            ->visible(fn ($get) => (bool) $get('is_online')),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('تاريخ ووقت فتح الامتحان')
                            ->displayFormat('Y-m-d h:i A')
                            ->seconds(false)
                            ->placeholder('اختر تاريخ ووقت البدء (ص / م)')
                            ->visible(fn ($get) => (bool) $get('is_online'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                if ($state && $get('ends_at') && blank($get('duration_minutes'))) {
                                    try {
                                        $start = \Carbon\Carbon::parse($state);
                                        $end = \Carbon\Carbon::parse($get('ends_at'));
                                        if ($end->greaterThan($start)) {
                                            $set('duration_minutes', (int) $start->diffInMinutes($end));
                                        }
                                    } catch (\Throwable $e) {}
                                }
                            })
                            ->native(false),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('تاريخ ووقت إغلاق الامتحان')
                            ->displayFormat('Y-m-d h:i A')
                            ->seconds(false)
                            ->placeholder('اختر تاريخ ووقت الانتهاء (ص / م)')
                            ->visible(fn ($get) => (bool) $get('is_online'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                if ($state && $get('starts_at') && blank($get('duration_minutes'))) {
                                    try {
                                        $start = \Carbon\Carbon::parse($get('starts_at'));
                                        $end = \Carbon\Carbon::parse($state);
                                        if ($end->greaterThan($start)) {
                                            $set('duration_minutes', (int) $start->diffInMinutes($end));
                                        }
                                    } catch (\Throwable $e) {}
                                }
                            })
                            ->native(false),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('أسئلة الامتحان 📝')
                    ->description('تنبيه: يتم إدارة وإضافة أسئلة الامتحان مباشرة من جدول "أسئلة الامتحان الإلكتروني" بالأسفل عبر زر (تحديد أسئلة جماعية من بنك الأسئلة) أو (إنشاء سؤال جديد فوري).')
                    ->schema([])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الامتحان')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('educationalStage.name')
                    ->label('المرحلة الدراسية')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('المادة')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('is_online')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'أونلاين' : 'ورقي 📄'),

                Tables\Columns\TextColumn::make('exam_type')
                    ->label('التصنيف')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'monthly' => 'info',
                        'quiz' => 'warning',
                        'midterm' => 'primary',
                        'final' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'monthly' => 'شهري',
                        'quiz' => 'كويز',
                        'midterm' => 'منتصف الترم',
                        'final' => 'نهائي',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('total_marks')
                    ->label('الدرجة الكلية')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('تاريخ الامتحان')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('results_summary')
                    ->label('حالة الامتحان')
                    ->state(function (Exam $record, ExamService $examService): string {
                        $stats = $examService->getExamStats($record->id);
                        $attended = $stats['attended_count'];
                        $absent = $stats['absent_count'];

                        if ($attended === 0 && $absent === 0) {
                            return 'لا يوجد طلاب بالمرحلة';
                        }

                        return "{$attended} ممتحن / {$absent} غائب";
                    })
                    ->badge()
                    ->color(function (Exam $record, ExamService $examService): string {
                        $stats = $examService->getExamStats($record->id);
                        return $stats['attended_count'] > 0 ? 'success' : 'warning';
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('stage_id')
                    ->label('المرحلة الدراسية')
                    ->relationship('educationalStage', 'name'),

                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة الدراسية')
                    ->relationship('subject', 'name'),

                Tables\Filters\TernaryFilter::make('is_online')
                    ->label('اختبار أونلاين'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewExamResults')
                    ->label('النتائج والأوائل')
                    ->icon('heroicon-o-trophy')
                    ->color('success')
                    ->modalHeading(fn (Exam $record) => "كشف نتائج ومتفوقين امتحان: {$record->title}")
                    ->modalContent(function (Exam $record, ExamService $examService) {
                        $attendees = $examService->getExamAttendees($record->id);
                        $stats = $examService->getExamStats($record->id);

                        return view('filament.modals.exam-results-list', [
                            'attendees' => $attendees,
                            'exam' => $record,
                            'stats' => $stats,
                        ]);
                    })
                    ->extraModalFooterActions(function (Tables\Actions\Action $action): array {
                        $actions = [
                            $action->makeModalSubmitAction('notifyResultsPortal', ['target' => 'results_portal'])
                                ->label('إشعار بوابة ولي الأمر بالدرجات')
                                ->color('primary'),
                        ];

                        if (\App\Services\WhatsAppNotificationService::isBulkEnabled()) {
                            $actions[] = $action->makeModalSubmitAction('sendBulkWhatsAppResults', ['target' => 'results_whatsapp'])
                                ->label('إرسال كشف الدرجات واتساب للجميع')
                                ->color('success');
                        }

                        return $actions;
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->action(function (Exam $record, array $arguments, ExamService $examService): void {
                        $target = $arguments['target'] ?? null;
                        if ($target === 'results_portal') {
                            $count = $examService->notifyBulkParentPortalForExam($record->id, 'results');
                            Notification::make()
                                ->title("تم إرسال {$count} بطاقة نتيجة لبوابة ولي الأمر بنجاح")
                                ->success()
                                ->send();
                        } elseif ($target === 'results_whatsapp') {
                            $result = $examService->sendBulkWhatsAppForExam($record->id, 'results');
                            Notification::make()
                                ->title("تم إرسال {$result['success']} رسالة نتيجة عبر الواتساب بنجاح")
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('viewExamAbsentees')
                    ->label('الغائبين والتنبيه')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->modalHeading(fn (Exam $record) => "قائمة الطلاب الغائبين عن امتحان: {$record->title}")
                    ->modalContent(function (Exam $record, ExamService $examService) {
                        $absentees = $examService->getExamAbsentees($record->id);
                        $stats = $examService->getExamStats($record->id);

                        return view('filament.modals.exam-absentees-list', [
                            'absentees' => $absentees,
                            'exam' => $record,
                            'stats' => $stats,
                        ]);
                    })
                    ->extraModalFooterActions(function (Tables\Actions\Action $action): array {
                        $actions = [
                            $action->makeModalSubmitAction('notifyAbsenteesPortal', ['target' => 'absentees_portal'])
                                ->label('إشعار تنبيه غياب لبوابة ولي الأمر للكل')
                                ->color('danger'),
                        ];

                        if (\App\Services\WhatsAppNotificationService::isBulkEnabled()) {
                            $actions[] = $action->makeModalSubmitAction('sendBulkWhatsAppAbsentees', ['target' => 'absentees_whatsapp'])
                                ->label('إرسال رسائل تذكير واتساب لجميع الغائبين')
                                ->color('success');
                        }

                        return $actions;
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->action(function (Exam $record, array $arguments, ExamService $examService): void {
                        $target = $arguments['target'] ?? null;
                        if ($target === 'absentees_portal') {
                            $count = $examService->notifyBulkParentPortalForExam($record->id, 'absent');
                            Notification::make()
                                ->title("تم إرسال {$count} تنبيه غياب لبوابة ولي الأمر بنجاح")
                                ->success()
                                ->send();
                        } elseif ($target === 'absentees_whatsapp') {
                            $result = $examService->sendBulkWhatsAppForExam($record->id, 'absent');
                            Notification::make()
                                ->title("تم إرسال {$result['success']} رسالة تذكير للغائبين عبر الواتساب")
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('recordResults')
                    ->label('رصد يدوي')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->url(fn (Exam $record): string => static::getUrl('record-results', ['record' => $record])),

                Tables\Actions\Action::make('printPdf')
                    ->label('طباعة نماذج الامتحان (أ / ب)')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->modalHeading(fn (Exam $record) => "طباعة نماذج امتحان: {$record->title}")
                    ->modalDescription('اختر عدد النماذج المطلوبة لطباعتها وخيار تضمين نموذج الإجابة النموذجي للمعلم.')
                    ->modalSubmitActionLabel('فتح ومعاينة الطباعة')
                    ->form([
                        Forms\Components\Select::make('models_count')
                            ->label('عدد النماذج المطبوعة')
                            ->options([
                                1 => 'نموذج واحد (نموذج أ)',
                                2 => 'نموذجان (أ / ب) بترتيب مختلف لمنع الغش',
                                3 => '3 نماذج (أ / ب / ج)',
                            ])
                            ->default(2)
                            ->required(),

                        Forms\Components\Toggle::make('include_answer_key')
                            ->label('تضمين مفتاح الإجابة وتوزيع الدرجات للمعلم')
                            ->default(true),
                    ])
                    ->action(function (Exam $record, array $data, $livewire) {
                        $url = route('exam.pdf.print', [
                            'record' => $record->id,
                            'models_count' => $data['models_count'] ?? 1,
                            'include_answer_key' => !empty($data['include_answer_key']) ? 1 : 0,
                        ]);

                        $livewire->js("window.open('{$url}', '_blank')");
                    }),

                Tables\Actions\Action::make('analytics')
                    ->label('تحليل نقاط الضعف')
                    ->icon('heroicon-o-chart-pie')
                    ->color('warning')
                    ->visible(fn (Exam $record): bool => (bool) $record->is_online)
                    ->url(fn (Exam $record): string => static::getUrl('analytics', ['record' => $record])),

                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ExamResource\RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
            'record-results' => Pages\RecordResults::route('/{record}/record-results'),
            'analytics' => Pages\ExamAnalytics::route('/{record}/analytics'),
        ];
    }
}
