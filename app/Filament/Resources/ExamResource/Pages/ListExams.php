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
                    Forms\Components\TextInput::make('title')
                        ->label('عنوان الامتحان')
                        ->placeholder('مثال: اختبار فيزياء شامل على الباب الأول')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('stage_id')
                                ->label('المرحلة الدراسية')
                                ->options(EducationalStage::pluck('name', 'id'))
                                ->required()
                                ->reactive(),

                            Forms\Components\Select::make('subject_id')
                                ->label('المادة الدراسية')
                                ->options(Subject::pluck('name', 'id'))
                                ->required()
                                ->reactive(),
                        ]),

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

                    Forms\Components\Section::make('توزيع مستويات الصعوبة والأسئلة')
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
                        ])
                        ->columns(3),

                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('exam_type')
                                ->label('نوع الامتحان')
                                ->options([
                                    'quiz' => 'كويز سريع',
                                    'monthly' => 'امتحان شهري',
                                    'midterm' => 'منتصف الترم',
                                    'final' => 'شامل / نهائي',
                                ])
                                ->default('quiz')
                                ->required(),

                            Forms\Components\DatePicker::make('date')
                                ->label('تاريخ الامتحان')
                                ->default(now()->toDateString())
                                ->required(),

                            Forms\Components\TextInput::make('duration_minutes')
                                ->label('المدة (بالدقائق)')
                                ->numeric()
                                ->default(45)
                                ->required(),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('marks_per_question')
                                ->label('درجة كل سؤال')
                                ->numeric()
                                ->default(1.0)
                                ->required(),

                            Forms\Components\Toggle::make('is_online')
                                ->label('إتاحته كاختبار إلكتروني أونلاين للطلاب')
                                ->default(false),
                        ]),
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
