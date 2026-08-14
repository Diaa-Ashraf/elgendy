<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $modelLabel = 'خصم / عرض';

    protected static ?string $pluralModelLabel = 'نظام الخصومات والعروض';

    protected static ?string $navigationLabel = 'الخصومات والعروض';

    protected static ?string $navigationGroup = 'الإدارة المالية';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفاصيل الخصم والعرض')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان / اسم الخصم')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: خصم الأشقاء 15%'),

                        Forms\Components\Select::make('type')
                            ->label('نوع الخصم')
                            ->options([
                                'percentage' => 'نسبة مئوية (%)',
                                'fixed' => 'مبلغ ثابت (ج.م)',
                            ])
                            ->default('percentage')
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('value')
                            ->label('قيمة الخصم')
                            ->numeric()
                            ->required()
                            ->minValue(0),

                        Forms\Components\Select::make('applies_to')
                            ->label('ينطبق على')
                            ->options([
                                'all' => 'جميع الطلاب',
                                'siblings' => 'خصم الأشقاء والإخوة',
                                'excellence' => 'خصم المتفوقين',
                                'custom' => 'خصم خاص منسق',
                            ])
                            ->default('all')
                            ->required()
                            ->native(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('خصم مفعل ومتاح')
                            ->default(true),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الخصم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('نوع الخصم')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'percentage' ? 'info' : 'success')
                    ->formatStateUsing(fn (string $state): string => $state === 'percentage' ? 'نسبة %' : 'مبلغ ثابت'),

                Tables\Columns\TextColumn::make('value')
                    ->label('القيمة')
                    ->formatStateUsing(fn ($record) => $record->type === 'percentage' ? "{$record->value}%" : "{$record->value} ج.م")
                    ->sortable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('الطلاب المطبق عليهم')
                    ->counts('students')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('مفعل')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDiscounts::route('/'),
        ];
    }
}
