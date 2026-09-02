<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'متابعة الاشتراكات';

    protected static ?string $modelLabel = 'اشتراك';

    protected static ?string $pluralModelLabel = 'اشتراكات المدرسين';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الاشتراك')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('المدرس / السنتر')
                            ->relationship('tenant', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('plan_id')
                            ->label('الخطة')
                            ->relationship('plan', 'name')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'trial' => 'فترة تجريبية',
                                'active' => 'نشط',
                                'past_due' => 'متأخر عن السداد',
                                'expired' => 'منتهي',
                                'cancelled' => 'ملغي',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('تاريخ البداية'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('تاريخ الانتهاء'),

                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('تاريخ نهاية التجربة'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('المدرس / السنتر')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('الخطة')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->colors([
                        'warning' => 'trial',
                        'success' => 'active',
                        'danger' => ['expired', 'cancelled'],
                        'gray' => 'past_due',
                    ]),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('ينتهي في')
                    ->dateTime('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('نهاية التجربة')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('فلترة بالحالة')
                    ->options([
                        'trial' => 'فترة تجريبية',
                        'active' => 'نشط',
                        'past_due' => 'متأخر',
                        'expired' => 'منتهي',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
