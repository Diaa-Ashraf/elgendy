<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExamResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'examResults';

    protected static ?string $title = 'سجل درجات الامتحانات والنتائج';

    protected static ?string $modelLabel = 'نتيجة امتحان';

    protected static ?string $pluralModelLabel = 'نتائج الامتحانات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('exam_id')
                    ->label('الامتحان')
                    ->relationship('exam', 'title')
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('marks_obtained')
                    ->label('الدرجة المحصلة')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('الامتحان')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('exam.subject.name')
                    ->label('المادة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('marks_obtained')
                    ->label('الدرجة المحصلة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('exam.total_marks')
                    ->label('الدرجة الكلية')
                    ->sortable(),

                Tables\Columns\TextColumn::make('result_percentage')
                    ->label('النسبة المئوية')
                    ->state(function ($record): string {
                        if (! $record->exam || ! $record->exam->total_marks) {
                            return '-';
                        }
                        $pct = round(($record->marks_obtained / $record->exam->total_marks) * 100, 1);
                        return "{$pct}%";
                    }),

                Tables\Columns\TextColumn::make('exam.date')
                    ->label('تاريخ الامتحان')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('رصد نتيجة امتحان للطالب'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ]);
    }
}
