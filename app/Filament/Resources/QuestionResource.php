<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $modelLabel = 'سؤال';

    protected static ?string $pluralModelLabel = 'بنك الأسئلة';

    protected static ?string $navigationLabel = 'بنك الأسئلة';

    protected static ?string $navigationGroup = 'الإدارة الأكاديمية';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات وتصنيف السؤال')
                    ->schema([
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

                        Forms\Components\TextInput::make('topic')
                            ->label('الموضوع / الباب / الدرس (لتشخيص نقاط الضعف)')
                            ->placeholder('مثال: قوانين كيرشوف، الديناميكا الحرارية، النحو...')
                            ->maxLength(255),

                        Forms\Components\Select::make('difficulty')
                            ->label('مستوى الصعوبة')
                            ->options([
                                'easy' => 'سهل 🟢',
                                'medium' => 'متوسط 🟡',
                                'hard' => 'صعب / متفوقين 🔴',
                            ])
                            ->default('medium')
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('default_marks')
                            ->label('الدرجة الافتراضية')
                            ->numeric()
                            ->default(1.00)
                            ->minValue(0.5)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('نص السؤال والوسائط')
                    ->schema([
                        Forms\Components\Textarea::make('question_text')
                            ->label('نص السؤال')
                            ->required()
                            ->rows(3)
                            ->placeholder('اكتب نص السؤال هنا بدقة ووضوح...'),

                        Forms\Components\FileUpload::make('question_image')
                            ->label('صورة توضيحية أو مسألة مرسومة (اختياري)')
                            ->image()
                            ->directory('questions')
                            ->maxSize(3072)
                            ->columnSpanFull(),
                    ]),

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
                            ->reactive()
                            ->native(false),

                        Forms\Components\Repeater::make('options')
                            ->label('خيارات الإجابة')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('رمز الخيار')
                                    ->placeholder('A, B, C, D أو 1, 2, 3')
                                    ->required()
                                    ->default(fn ($get) => 'opt_' . rand(10, 99)),
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
                            ->label('رموز الإجابات الصحيحة (اكتب رمز الخيار واضغط Enter)')
                            ->placeholder('مثال: A أو A, B')
                            ->helperText('يجب أن تتطابق الرموز تماماً مع رموز الخيارات المدخلة بالأعلى (مثلاً A أو B)')
                            ->required(),

                        Forms\Components\Textarea::make('explanation')
                            ->label('الشرح والتفسير النموذجي للإجابة (يظهر للطالب بعد التسليم)')
                            ->rows(2)
                            ->placeholder('اكتب خطوات الحل أو التعليل الأكاديمي لمساعدة الطالب على تصحيح مفاهيمه...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('question_text')
                    ->label('نص السؤال')
                    ->limit(60)
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('educationalStage.name')
                    ->label('المرحلة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('المادة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('topic')
                    ->label('الموضوع / الدرس')
                    ->badge()
                    ->color('info')
                    ->searchable(),

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

                Tables\Columns\TextColumn::make('default_marks')
                    ->label('الدرجة')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage_id')
                    ->label('المرحلة')
                    ->relationship('educationalStage', 'name'),

                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'name'),

                Tables\Filters\SelectFilter::make('difficulty')
                    ->label('الصعوبة')
                    ->options([
                        'easy' => 'سهل',
                        'medium' => 'متوسط',
                        'hard' => 'صعب',
                    ]),
            ])
            ->actions([
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
