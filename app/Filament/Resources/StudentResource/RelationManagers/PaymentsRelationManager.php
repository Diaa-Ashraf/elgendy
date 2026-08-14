<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'سجل المدفوعات والإيصالات';

    protected static ?string $modelLabel = 'إيصال دفع';

    protected static ?string $pluralModelLabel = 'المدفوعات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('group_id')
                    ->label('المجموعة')
                    ->options(fn ($livewire) => $livewire->ownerRecord->groups->pluck('name', 'id'))
                    ->required()
                    ->native(false),

                Forms\Components\Select::make('type')
                    ->label('نوع السداد')
                    ->options([
                        'month' => 'اشتراك شهري',
                        'session' => 'بالحصة',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->prefix('ج.م')
                    ->required(),

                Forms\Components\DatePicker::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->default(now())
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'month' => 'success',
                        'session' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'month' => 'شهري',
                        'session' => 'بالحصة',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('period_month')
                    ->label('عن شهر')
                    ->date('Y-m')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('paid_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('تسجيل مدفوعات جديدة')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['received_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ]);
    }
}
