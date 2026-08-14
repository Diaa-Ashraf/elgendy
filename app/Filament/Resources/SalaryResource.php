<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryResource\Pages;
use App\Models\Group;
use App\Models\Salary;
use App\Models\User;
use App\Services\SalaryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalaryResource extends Resource
{
    protected static ?string $model = Salary::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $modelLabel = 'راتب / مستحق';

    protected static ?string $pluralModelLabel = 'سجل الرواتب والمستحقات';

    protected static ?string $navigationLabel = 'الرواتب والمستحقات';

    protected static ?string $navigationGroup = 'الإدارة المالية';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفاصيل صرف الراتب')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('الموظف / المعلم')
                            ->options(User::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('type')
                            ->label('نظام الراتب')
                            ->options([
                                'fixed' => 'راتب ثابت (Fixed)',
                                'percentage' => 'نسبة من الدخل (Percentage)',
                            ])
                            ->default('fixed')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('amount_paid', 0)),

                        Forms\Components\TextInput::make('base_amount')
                            ->label('المبلغ الأساسي')
                            ->numeric()
                            ->prefix('ج.م')
                            ->visible(fn(callable $get) => $get('type') === 'fixed')
                            ->required(fn(callable $get) => $get('type') === 'fixed')
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set, $state) => $set('amount_paid', $state)),

                        Forms\Components\TextInput::make('percentage')
                            ->label('النسبة المئوية (%)')
                            ->numeric()
                            ->prefix('%')
                            ->minValue(1)
                            ->maxValue(100)
                            ->visible(fn(callable $get) => $get('type') === 'percentage')
                            ->required(fn(callable $get) => $get('type') === 'percentage')
                            ->reactive(),

                        Forms\Components\Select::make('group_id')
                            ->label('المجموعة (لحساب النسبة)')
                            ->options(Group::pluck('name', 'id'))
                            ->visible(fn(callable $get) => $get('type') === 'percentage')
                            ->required(fn(callable $get) => $get('type') === 'percentage')
                            ->searchable()
                            ->preload()
                            ->reactive(),

                        Forms\Components\DatePicker::make('month')
                            ->label('عن شهر')
                            ->default(now()->startOfMonth())
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m')
                            ->reactive(),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('calculateAmount')
                                ->label('حساب المبلغ تلقائياً')
                                ->icon('heroicon-o-calculator')
                                ->color('primary')
                                ->action(function (callable $get, callable $set, SalaryService $salaryService) {
                                    $userId = $get('user_id');
                                    $type = $get('type');
                                    $baseAmount = $get('base_amount') ? (float) $get('base_amount') : null;
                                    $percentage = $get('percentage') ? (float) $get('percentage') : null;
                                    $groupId = $get('group_id') ? (int) $get('group_id') : null;
                                    $month = $get('month') ?? now()->startOfMonth()->format('Y-m-01');

                                    if (! $userId) {
                                        return;
                                    }

                                    $calculated = $salaryService->calculateSalary(
                                        (int) $userId,
                                        $type,
                                        $baseAmount,
                                        $percentage,
                                        $groupId,
                                        $month
                                    );

                                    $set('amount_paid', $calculated);
                                }),
                        ])->columnSpanFull()->visible(fn(callable $get) => $get('type') === 'percentage'),

                        Forms\Components\TextInput::make('amount_paid')
                            ->label('المبلغ الصافي المصروف')
                            ->numeric()
                            ->prefix('ج.م')
                            ->required(),

                        Forms\Components\DatePicker::make('paid_at')
                            ->label('تاريخ الصرف')
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
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('الموظف / المعلم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('نظام الراتب')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'fixed' => 'info',
                        'percentage' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'fixed' => 'ثابت',
                        'percentage' => 'نسبة %',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('المبلغ المصروف')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('month')
                    ->label('عن شهر')
                    ->date('Y-m')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الصرف')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('paid_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('الموظف / المعلم')
                    ->options(User::pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('type')
                    ->label('نظام الراتب')
                    ->options([
                        'fixed' => 'ثابت',
                        'percentage' => 'نسبة %',
                    ]),

                Tables\Filters\Filter::make('month')
                    ->form([
                        Forms\Components\DatePicker::make('month')->label('عن شهر')->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['month'], fn($q, $m) => $q->whereYear('month', date('Y', strtotime($m)))->whereMonth('month', date('m', strtotime($m))));
                    }),
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
            'index' => Pages\ListSalaries::route('/'),
            'create' => Pages\CreateSalary::route('/create'),
            'edit' => Pages\EditSalary::route('/{record}/edit'),
        ];
    }
}
