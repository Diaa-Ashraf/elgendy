<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'باقات وخطط التسعير';

    protected static ?string $modelLabel = 'خطة تسعير';

    protected static ?string $pluralModelLabel = 'خطط التسعير';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الخطة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الخطة')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label('الرمز التعريفي (Slug)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('price_monthly')
                            ->label('السعر الشهري (ج.م)')
                            ->numeric()
                            ->prefix('EGP')
                            ->required(),

                        Forms\Components\TextInput::make('max_students')
                            ->label('الحد الأقصى للطلاب')
                            ->numeric()
                            ->helperText('0 أو اتركه فارغاً يعني غير محدود')
                            ->nullable(),

                        Forms\Components\TextInput::make('max_teachers')
                            ->label('الحد الأقصى للمساعدين')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Forms\Components\TextInput::make('max_groups')
                            ->label('الحد الأقصى للمجموعات')
                            ->numeric()
                            ->helperText('0 أو اتركه فارغاً يعني غير محدود')
                            ->nullable(),

                        Forms\Components\Toggle::make('is_popular')
                            ->label('تمييز كخطة مفضلة/شائعة')
                            ->default(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('مفعلة ومتاحة للاشتراك')
                            ->default(true),

                        Forms\Components\KeyValue::make('features')
                            ->label('المميزات المعروضة للعميل')
                            ->keyLabel('الميزة')
                            ->valueLabel('الوصف / القيمة')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الخطة')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price_monthly')
                    ->label('السعر الشهري')
                    ->money('EGP')
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('max_students')
                    ->label('الطلاب')
                    ->formatStateUsing(fn ($state) => $state ? $state : 'غير محدود'),

                Tables\Columns\TextColumn::make('max_groups')
                    ->label('المجموعات')
                    ->formatStateUsing(fn ($state) => $state ? $state : 'غير محدود'),

                Tables\Columns\IconColumn::make('is_popular')
                    ->label('شائعة')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
