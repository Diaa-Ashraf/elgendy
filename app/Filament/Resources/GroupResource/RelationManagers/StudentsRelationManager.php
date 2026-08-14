<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'الطلاب المسجلين بالمجموعة';

    protected static ?string $modelLabel = 'طالب';

    protected static ?string $pluralModelLabel = 'الطلاب';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('joined_at')
                    ->label('تاريخ التسجيل بالجموعة')
                    ->default(now())
                    ->required()
                    ->native(false),

                Forms\Components\Select::make('status')
                    ->label('حالة تسجيل الطالب')
                    ->options([
                        'active' => 'مستمر (نشط)',
                        'paused' => 'موقوف مؤقتاً',
                        'left' => 'منسحب',
                    ])
                    ->default('active')
                    ->required()
                    ->native(false),

                Forms\Components\DatePicker::make('left_at')
                    ->label('تاريخ الانسحاب (إن وجد)')
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent_phone')
                    ->label('هاتف ولي الأمر')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('pivot.joined_at')
                    ->label('تاريخ التسجيل')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'left' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'مستمر',
                        'paused' => 'موقوف مؤقتاً',
                        'left' => 'منسحب',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('pivot.left_at')
                    ->label('تاريخ الانسحاب')
                    ->date()
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الاشتراك')
                    ->options([
                        'active' => 'مستمر',
                        'paused' => 'موقوف مؤقتاً',
                        'left' => 'منسحب',
                    ])
                    ->attribute('group_student.status'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('تسجيل طالب جديد بالمجموعة')
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\DatePicker::make('joined_at')
                            ->label('تاريخ الانضمام')
                            ->default(now())
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->label('حالة الاشتراك')
                            ->options([
                                'active' => 'مستمر (نشط)',
                                'paused' => 'موقوف مؤقتاً',
                                'left' => 'منسحب',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل حالة الاشتراك'),
                Tables\Actions\DetachAction::make()->label('إلغاء التسجيل'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('إلغاء تسجيل المحدد'),
                ]),
            ]);
    }
}
