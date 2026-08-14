<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $modelLabel = 'مصروف';

    protected static ?string $pluralModelLabel = 'سجل المصروفات';

    protected static ?string $navigationLabel = 'المصروفات العامة';

    protected static ?string $navigationGroup = 'الإدارة المالية';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفاصيل سند الصرف / المصروف')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('تصنيف المصروف')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ المصروف')
                            ->numeric()
                            ->prefix('ج.م')
                            ->required()
                            ->minValue(0.01),

                        Forms\Components\DatePicker::make('date')
                            ->label('تاريخ الصرف')
                            ->default(now())
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('description')
                            ->label('البيان / الوصف التفصيلي')
                            ->placeholder('بيان أسباب الصرف أو التفاصيل...')
                            ->rows(3)
                            ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('category.name')
                    ->label('تصنيف المصروف')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ المصروف')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('تاريخ الصرف')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('البيان / الوصف')
                    ->limit(40)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('paidBy.name')
                    ->label('القائم بالصرف')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name'),

                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')->label('من تاريخ'),
                        Forms\Components\DatePicker::make('to_date')->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from_date'], fn($q, $d) => $q->whereDate('date', '>=', $d))
                            ->when($data['to_date'], fn($q, $d) => $q->whereDate('date', '<=', $d));
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
