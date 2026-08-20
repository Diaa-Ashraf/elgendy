<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'أسئلة الامتحان الإلكتروني';

    protected static ?string $modelLabel = 'سؤال';

    protected static ?string $pluralModelLabel = 'أسئلة الامتحان';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('stage_id')
                    ->default(fn () => $this->getOwnerRecord()->stage_id),

                Forms\Components\Hidden::make('subject_id')
                    ->default(fn () => $this->getOwnerRecord()->subject_id),

                Forms\Components\Section::make('نص السؤال وتصنيفه')
                    ->schema([
                        Forms\Components\Textarea::make('question_text')
                            ->label('نص السؤال')
                            ->required()
                            ->rows(3)
                            ->placeholder('اكتب نص السؤال هنا...'),

                        Forms\Components\TextInput::make('topic')
                            ->label('الموضوع / الدرس / الباب (لتشخيص نقاط الضعف)')
                            ->placeholder('مثال: قوانين كيرشوف، الديناميكا، النحو...'),

                        Forms\Components\Select::make('difficulty')
                            ->label('مستوى الصعوبة')
                            ->options([
                                'easy' => 'سهل 🟢',
                                'medium' => 'متوسط 🟡',
                                'hard' => 'صعب 🔴',
                            ])
                            ->default('medium')
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('default_marks')
                            ->label('درجة هذا السؤال')
                            ->numeric()
                            ->default(1.0)
                            ->minValue(0.5)
                            ->required(),

                        Forms\Components\FileUpload::make('question_image')
                            ->label('صورة السؤال إن وجدت (اختياري)')
                            ->image()
                            ->directory('questions')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('الخيارات والإجابة النموذجية والتفسير')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('نوع السؤال')
                            ->options([
                                'single_choice' => 'اختيار من متعدد (إجابة واحدة صحيحة)',
                                'multiple_choice' => 'اختيار من متعدد (أكثر من إجابة صحيحة)',
                                'true_false' => 'صح أم خطأ',
                            ])
                            ->default('single_choice')
                            ->required()
                            ->native(false),

                        Forms\Components\Repeater::make('options')
                            ->label('خيارات الإجابة')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('رمز الخيار')
                                    ->required()
                                    ->placeholder('A, B, C, D'),
                                Forms\Components\TextInput::make('text')
                                    ->label('نص الخيار')
                                    ->required()
                                    ->placeholder('اكتب نص الخيار...'),
                            ])
                            ->columns(2)
                            ->default([
                                ['key' => 'A', 'text' => ''],
                                ['key' => 'B', 'text' => ''],
                                ['key' => 'C', 'text' => ''],
                                ['key' => 'D', 'text' => ''],
                            ])
                            ->required()
                            ->minItems(2),

                        Forms\Components\TagsInput::make('correct_answers')
                            ->label('رموز الإجابات الصحيحة (اكتب الرمز مثل A واضغط Enter)')
                            ->placeholder('A')
                            ->required(),

                        Forms\Components\Textarea::make('explanation')
                            ->label('الشرح والتفسير العلمي للإجابة')
                            ->rows(2)
                            ->placeholder('خطوات الحل لتظهر للطالب لتصحيح نقاط ضعفه...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        $exam = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('question_text')
            ->columns([
                Tables\Columns\TextColumn::make('pivot.order')
                    ->label('الترتيب')
                    ->sortable(),

                Tables\Columns\TextColumn::make('question_text')
                    ->label('نص السؤال')
                    ->limit(65)
                    ->wrap(),

                Tables\Columns\TextColumn::make('topic')
                    ->label('الموضوع / الدرس')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('difficulty')
                    ->label('الصعوبة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'easy' => 'سهل',
                        'medium' => 'متوسط',
                        'hard' => 'صعب',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('pivot.marks')
                    ->label('درجة السؤال')
                    ->sortable(),
            ])
            ->defaultSort('exam_questions.order', 'asc')
            ->headerActions([
                // 1. إنشاء فوري مباشر لسؤال جديد داخل الامتحان ويحفظ أيضاً في بنك الأسئلة
                Tables\Actions\CreateAction::make()
                    ->label('إنشاء سؤال جديد فوري ➕')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['stage_id'] = $this->getOwnerRecord()->stage_id;
                        $data['subject_id'] = $this->getOwnerRecord()->subject_id;
                        return $data;
                    })
                    ->after(function (Question $record) {
                        $exam = $this->getOwnerRecord();
                        $currentCount = $exam->questions()->count();

                        // التأكد من حفظ وإدراج الـ Pivot
                        if (! $exam->questions()->where('questions.id', $record->id)->exists()) {
                            $exam->questions()->attach($record->id, [
                                'marks' => $record->default_marks ?? 1.0,
                                'order' => $currentCount + 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        } else {
                            $exam->questions()->updateExistingPivot($record->id, [
                                'marks' => $record->default_marks ?? 1.0,
                                'order' => $currentCount,
                            ]);
                        }
                    }),

                // 2. تحديد واختيار مجموعة أسئلة من بنك الأسئلة دفعة واحدة (Bulk Selector)
                Tables\Actions\Action::make('bulkAttachFromBank')
                    ->label('تحديد أسئلة جماعية من بنك الأسئلة 📚⚡')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->modalHeading('اختيار أسئلة من بنك الأسئلة وإدراجها دفعة واحدة')
                    ->modalWidth('4xl')
                    ->form(function () use ($exam): array {
                        // جلب الأسئلة غير المضافة للامتحان بعد
                        $existingIds = $exam->questions()->pluck('questions.id')->toArray();

                        $query = Question::query()->whereNotIn('id', $existingIds);

                        // محاولة مطابقة المرحلة والمادة أولاً إن وجدت أسئلة لهما
                        $hasScoped = (clone $query)->where('stage_id', $exam->stage_id)
                            ->where('subject_id', $exam->subject_id)
                            ->exists();

                        if ($hasScoped) {
                            $query->where('stage_id', $exam->stage_id)
                                  ->where('subject_id', $exam->subject_id);
                        }

                        $availableQuestions = $query->orderBy('id', 'desc')->get();

                        $options = [];
                        foreach ($availableQuestions as $q) {
                            $diff = match ($q->difficulty) {
                                'easy' => '🟢 سهل',
                                'hard' => '🔴 صعب',
                                default => '🟡 متوسط',
                            };
                            $stageName = $q->educationalStage?->name ? " [{$q->educationalStage->name}]" : '';
                            $topic = $q->topic ? " [{$q->topic}]" : '';
                            $options[$q->id] = "#{$q->id}{$stageName} - {$diff}{$topic}: " . mb_substr($q->question_text, 0, 90) . '...';
                        }

                        return [
                            Forms\Components\CheckboxList::make('selected_questions')
                                ->label('حدد الأسئلة التي ترغب في إضافتها لهذا الامتحان:')
                                ->options($options)
                                ->columns(1)
                                ->searchable()
                                ->bulkToggleable()
                                ->required()
                                ->helperText('يمكنك استخدام مربع البحث لتصفية الأسئلة بالدرس أو النص ثم الضغط على تحديد الكل.'),

                            Forms\Components\TextInput::make('uniform_marks')
                                ->label('درجة موحدة لكل سؤال من الأسئلة المحددة')
                                ->numeric()
                                ->default(1.0)
                                ->minValue(0.5)
                                ->required(),
                        ];
                    })
                    ->action(function (array $data) use ($exam): void {
                        $selectedIds = (array) ($data['selected_questions'] ?? []);
                        if (empty($selectedIds)) {
                            return;
                        }

                        $marks = (float) ($data['uniform_marks'] ?? 1.0);
                        $currentOrder = $exam->questions()->count();

                        $attachData = [];
                        foreach ($selectedIds as $qId) {
                            $currentOrder++;
                            $attachData[$qId] = [
                                'marks' => $marks,
                                'order' => $currentOrder,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        $exam->questions()->attach($attachData);

                        Notification::make()
                            ->title("تمت إضافة " . count($selectedIds) . " سؤال للامتحان بنجاح ⚡")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل السؤال'),

                Tables\Actions\Action::make('editMarks')
                    ->label('تعديل الدرجة والترتيب')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->fillForm(fn ($record) => [
                        'marks' => $record->pivot->marks,
                        'order' => $record->pivot->order,
                    ])
                    ->form([
                        Forms\Components\TextInput::make('marks')
                            ->label('درجة السؤال في الامتحان')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('order')
                            ->label('ترتيب السؤال')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $this->getOwnerRecord()->questions()->updateExistingPivot($record->id, [
                            'marks' => (float) $data['marks'],
                            'order' => (int) $data['order'],
                        ]);

                        Notification::make()
                            ->title('تم تحديث درجة وترتيب السؤال')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DetachAction::make()
                    ->label('إزالة من الامتحان')
                    ->modalHeading('إزالة السؤال من الامتحان')
                    ->modalDescription('سيتم إزالة السؤال من هذا الامتحان فقط دون حذفه من بنك الأسئلة العام.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('إزالة المحدد من الامتحان'),
                ]),
            ]);
    }
}
