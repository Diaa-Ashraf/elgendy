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

                Forms\Components\Section::make('إعدادات الاختبار الإلكتروني (Online Quiz) ⚡')
                    ->schema([
                        Forms\Components\Toggle::make('is_online')
                            ->label('تفعيل كاختبار إلكتروني أونلاين')
                            ->helperText('عند التفعيل، سيتمكن الطلاب من حل الامتحان أونلاين وتصحيحه ذاتياً.')
                            ->reactive()
                            ->default(false),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('مدة الامتحان (بالدقائق)')
                            ->numeric()
                            ->placeholder('مثال: 30')
                            ->helperText('اتركه فارغاً إذا كان الاختبار مفتوح بدون وقت محدد.')
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
                            ->native(false),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('تاريخ ووقت إغلاق الامتحان')
                            ->displayFormat('Y-m-d h:i A')
                            ->seconds(false)
                            ->placeholder('اختر تاريخ ووقت الانتهاء (ص / م)')
                            ->visible(fn ($get) => (bool) $get('is_online'))
                            ->native(false),

                        Forms\Components\Toggle::make('show_correct_answers_after_submission')
                            ->label('إظهار الإجابات النموذجية والشرح للطالب بعد التسليم فوراً')
                            ->default(true)
                            ->visible(fn ($get) => (bool) $get('is_online')),
                    ])
                    ->columns(2),
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
                    ->formatStateUsing(fn (bool $state): string => $state ? 'أونلاين ⚡' : 'ورقي 📄'),

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
                    ->label('النتائج')
                    ->state(function (Exam $record): string {
                        $count = $record->examResults()->count();
                        return $count > 0 ? "تم رصد {$count} طالب" : 'لم تُرصد النتائج';
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
                Tables\Actions\Action::make('analytics')
                    ->label('تحليل نقاط الضعف 🎯')
                    ->icon('heroicon-o-chart-pie')
                    ->color('warning')
                    ->visible(fn (Exam $record): bool => (bool) $record->is_online)
                    ->url(fn (Exam $record): string => static::getUrl('analytics', ['record' => $record])),

                Tables\Actions\Action::make('recordResults')
                    ->label('رصد يدوي')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->url(fn (Exam $record): string => static::getUrl('record-results', ['record' => $record])),

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
