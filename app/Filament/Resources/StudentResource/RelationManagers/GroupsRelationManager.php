<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    protected static ?string $title = 'المجموعات الدراسية المتاح/المسجل بها الطالب';

    protected static ?string $modelLabel = 'مجموعة';

    protected static ?string $pluralModelLabel = 'المجموعات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->label('اسم المجموعة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('المادة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.joined_at')
                    ->label('تاريخ الانضمام')
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
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('تسكين الطالب في مجموعة')
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
                Tables\Actions\DetachAction::make()->label('إلغاء التسكين'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('إلغاء التسكين للمحدد'),
                ]),
            ]);
    }
}
