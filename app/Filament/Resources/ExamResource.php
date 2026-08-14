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
                            ->required()
                            ->native(false),
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

                Tables\Columns\TextColumn::make('exam_type')
                    ->label('نوع الامتحان')
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
                    ->label('المصححين')
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

                Tables\Filters\SelectFilter::make('exam_type')
                    ->label('نوع الامتحان')
                    ->options([
                        'monthly' => 'شهري',
                        'quiz' => 'كويز',
                        'midterm' => 'منتصف الترم',
                        'final' => 'نهائي',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('recordResults')
                    ->label('رصد الدرجات')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
            'record-results' => Pages\RecordResults::route('/{record}/record-results'),
        ];
    }
}
