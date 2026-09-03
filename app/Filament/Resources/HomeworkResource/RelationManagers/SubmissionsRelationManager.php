<?php

namespace App\Filament\Resources\HomeworkResource\RelationManagers;

use App\Models\HomeworkSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'تسليمات الطلاب ونتائج الواجب';

    protected static ?string $modelLabel = 'تسليم';

    protected static ?string $pluralModelLabel = 'تسليمات الطلاب';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تقييم ورصد درجة الطالب')
                    ->schema([
                        Forms\Components\TextInput::make('score')
                            ->label('الدرجة المرصودة')
                            ->numeric()
                            ->required()
                            ->maxValue(fn () => $this->getOwnerRecord()->total_marks),

                        Forms\Components\Select::make('status')
                            ->label('حالة التسليم')
                            ->options([
                                'submitted' => 'تم التسليم (قيد المراجعة)',
                                'graded' => 'تم التصحيح والاعتماد ✅',
                                'returned' => 'إعادة للطالب لتصحيح الأخطاء 🔄',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('teacher_feedback')
                            ->label('ملاحظات وتوجيهات المدرس للطالب')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        $homework = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('student.phone')
                    ->label('هاتف الطالب')
                    ->searchable(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('وقت التسليم')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_late')
                    ->label('متأخر؟')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'warning',
                        'graded' => 'success',
                        'returned' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted' => 'قيد المراجعة ⏳',
                        'graded' => 'مُصحح ✅',
                        'returned' => 'مُعاد 🔄',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('score')
                    ->label('الدرجة')
                    ->formatStateUsing(fn ($record) => $record->score !== null ? "{$record->score} / {$homework->total_marks}" : 'لم تُرصد')
                    ->sortable(),

                Tables\Columns\TextColumn::make('auto_score')
                    ->label('الدرجة التلقائية (MCQ)')
                    ->visible(fn () => in_array($homework->type, ['questions', 'mixed'])),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('viewAttachment')
                    ->label('عرض المرفق 📎')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn (HomeworkSubmission $record) => ! empty($record->attachment))
                    ->url(fn (HomeworkSubmission $record) => asset('storage/' . $record->attachment))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->label('رصد الدرجة وملاحظات')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['status'] = $data['status'] === 'submitted' ? 'graded' : $data['status'];
                        return $data;
                    })
                    ->after(function (HomeworkSubmission $record) {
                        $record->update(['graded_at' => now()]);
                        Notification::make()
                            ->title('تم رصد وتحديث درجة الواجب بنجاح')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
