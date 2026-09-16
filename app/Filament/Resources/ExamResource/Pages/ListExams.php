<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use App\Models\EducationalStage;
use App\Models\Question;
use App\Models\Subject;
use App\Services\ExamService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateSmartExam')
                ->label('توليد امتحان ذكي من بنك الأسئلة')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->modalHeading('معالج توليد امتحان ذكي من بنك الأسئلة')
                ->modalDescription('حدد معايير الاختبار ومستويات الصعوبة وسيقوم النظام باختيار وتوزيع الأسئلة تلقائياً من بنك الأسئلة.')
                ->modalSubmitActionLabel('توليد الامتحان الآن')
                ->form([
                    Forms\Components\Section::make('البيانات الأساسية للامتحان')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('عنوان الامتحان')
                                ->placeholder('مثال: اختبار فيزياء شامل على الباب الأول')
                                ->required()
                                ->columnSpanFull(),

                            Forms\Components\Select::make('stage_id')
                                ->label('المرحلة الدراسية')
                                ->options(EducationalStage::pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive(),

                            Forms\Components\Select::make('group_id')
                                ->label('المجموعة المستهدفة (اختياري)')
                                ->options(function (Forms\Get $get) {
                                    $stageId = $get('stage_id');
                                    return \App\Models\Group::when($stageId, fn ($q) => $q->where('stage_id', $stageId))
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->placeholder('جميع المجموعات (المرحلة كاملة)')
                                ->helperText('اختر مجموعة معينة أو اتركه فارغاً ليكون الامتحان لجميع طلاب المرحلة.')
                                ->reactive(),

                            Forms\Components\Select::make('subject_id')
                                ->label('المادة الدراسية')
                                ->options(Subject::pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive(),

                            Forms\Components\Select::make('exam_type')
                                ->label('نوع الامتحان')
                                ->options([
                                    'quiz' => 'كويز سريع (Quiz)',
                                    'monthly' => 'اختبار شهري',
                                    'midterm' => 'منتصف الفصل (Midterm)',
                                    'final' => 'اختبار شامل / نهائي (Final)',
                                ])
                                ->default('quiz')
                                ->required()
                                ->native(false),

                            Forms\Components\DatePicker::make('date')
                                ->label('تاريخ عقد الامتحان')
                                ->default(now()->toDateString())
                                ->required()
                                ->native(false),

                            Forms\Components\Select::make('topics')
                                ->label('تحديد الدروس والموضوعات (اختياري)')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->placeholder('اختر درساً أو عدة دروس معاً... (أو اتركه فارغاً للسحب من المنهج كاملاً)')
                                ->options(function (Forms\Get $get) {
                                    $stageId = $get('stage_id');
                                    $subjectId = $get('subject_id');
                                    return Question::when($stageId, fn ($q) => $q->where('stage_id', $stageId))
                                        ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
                                        ->whereNotNull('topic')
                                        ->where('topic', '!=', '')
                                        ->distinct()
                                        ->pluck('topic', 'topic')
                                        ->toArray();
                                })
                                ->helperText('يمكنك اختيار أكثر من درس معاً، أو تركه فارغاً ليتم السحب عشوائياً من كامل المنهج')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make('معايير بنك الأسئلة وتوزيع الصعوبة')
                        ->schema([
                            Forms\Components\TextInput::make('easy_count')
                                ->label('عدد الأسئلة السهلة')
                                ->numeric()
                                ->default(3)
                                ->minValue(0)
                                ->required(),

                            Forms\Components\TextInput::make('medium_count')
                                ->label('عدد الأسئلة المتوسطة')
                                ->numeric()
                                ->default(5)
                                ->minValue(0)
                                ->required(),

                            Forms\Components\TextInput::make('hard_count')
                                ->label('أسئلة المتفوقين')
                                ->numeric()
                                ->default(2)
                                ->minValue(0)
                                ->required(),

                            Forms\Components\TextInput::make('marks_per_question')
                                ->label('درجة كل سؤال')
                                ->numeric()
                                ->default(1.0)
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(3),

                    Forms\Components\Section::make('إعدادات الاختبار الإلكتروني والنماذج (Online Quiz)')
                        ->schema([
                            Forms\Components\Toggle::make('is_online')
                                ->label('تفعيل كاختبار إلكتروني أونلاين')
                                ->helperText('عند التفعيل، سيتمكن الطلاب من أداء الاختبار أونلاين عبر البوابة وتصحيحه تلقائياً.')
                                ->reactive()
                                ->default(false)
                                ->columnSpanFull(),

                            Forms\Components\Select::make('models_count')
                                ->label('عدد نماذج الامتحان (أ / ب / ج / د)')
                                ->options([
                                    1 => 'نموذج واحد (أ)',
                                    2 => 'نموذجان (أ / ب) — تبديل ترتيب الأسئلة لمنع الغش',
                                    3 => '3 نماذج (أ / ب / ج)',
                                    4 => '4 نماذج (أ / ب / ج / د)',
                                ])
                                ->default(2)
                                ->helperText('يحدد عدد نماذج الامتحان المتاحة مع تبديل ترتيب الأسئلة تلقائياً لكل نموذج.')
                                ->native(false),

                            Forms\Components\TextInput::make('duration_minutes')
                                ->label('مدة الامتحان للطالب (بالدقائق)')
                                ->numeric()
                                ->default(45)
                                ->placeholder('مثال: 15 أو 45')
                                ->required(),

                            Forms\Components\TextInput::make('pass_percentage')
                                ->label('نسبة النجاح (%)')
                                ->numeric()
                                ->default(50)
                                ->minValue(1)
                                ->maxValue(100)
                                ->visible(fn (Forms\Get $get) => (bool) $get('is_online')),

                            Forms\Components\Toggle::make('show_correct_answers_after_submission')
                                ->label('إظهار الإجابات الصحيحة والشرح للطالب بعد التسليم')
                                ->default(true)
                                ->visible(fn (Forms\Get $get) => (bool) $get('is_online')),

                            Forms\Components\DateTimePicker::make('starts_at')
                                ->label('تاريخ ووقت فتح الامتحان')
                                ->displayFormat('Y-m-d h:i A')
                                ->seconds(false)
                                ->placeholder('اختر تاريخ ووقت البدء (ص / م)')
                                ->visible(fn (Forms\Get $get) => (bool) $get('is_online'))
                                ->native(false),

                            Forms\Components\DateTimePicker::make('ends_at')
                                ->label('تاريخ ووقت إغلاق الامتحان')
                                ->displayFormat('Y-m-d h:i A')
                                ->seconds(false)
                                ->placeholder('اختر تاريخ ووقت الانتهاء (ص / م)')
                                ->visible(fn (Forms\Get $get) => (bool) $get('is_online'))
                                ->native(false),
                        ])
                        ->columns(2),
                ])
                ->action(function (array $data, ExamService $examService) {
                    try {
                        $exam = $examService->generateExamFromQuestionBank($data);
                        $count = $exam->questions()->count();

                        Notification::make()
                            ->title("تم توليد الامتحان ({$exam->title}) بنجاح")
                            ->body("تم إدراج {$count} سؤالاً وتجهيز نماذج الطباعة التلقائية (أ / ب).")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('خطأ في توليد الامتحان')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make()
                ->label('إضافة امتحان يدوي جديد'),
        ];
    }
}
