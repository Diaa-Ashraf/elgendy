<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\SubscriptionPaymentResource\Pages;
use App\Models\SubscriptionPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPaymentResource extends Resource
{
    protected static ?string $model = SubscriptionPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'إيصالات واشتراكات المدرسين';

    protected static ?string $modelLabel = 'إيصال دفع اشتراك';

    protected static ?string $pluralModelLabel = 'إيصالات دفع الاشتراكات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفاصيل السداد والإيصال')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('المدرس / السنتر')
                            ->relationship('tenant', 'name')
                            ->disabled(),

                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ المحول')
                            ->disabled(),

                        Forms\Components\TextInput::make('payment_method')
                            ->label('طريقة الدفع')
                            ->disabled(),

                        Forms\Components\TextInput::make('sender_phone')
                            ->label('رقم المحول منه')
                            ->disabled(),

                        Forms\Components\FileUpload::make('receipt_image')
                            ->label('صورة إيصال التحويل')
                            ->image()
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('حالة الإيصال')
                            ->options([
                                'pending' => 'قيد المراجعة',
                                'approved' => 'معتمد ومقبول',
                                'rejected' => 'مرفوض',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('سبب الرفض إن وجد')
                            ->visible(fn ($get) => $get('status') === 'rejected')
                            ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->weight('black')
                    ->color('success'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('الطريقة')
                    ->badge(),

                Tables\Columns\TextColumn::make('sender_phone')
                    ->label('رقم المحول'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'approved' => 'معتمد',
                        'rejected' => 'مرفوض',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('اعتماد وتفعيل الاشتراك')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        app(\App\Services\SubscriptionService::class)->activateFromPayment($record, auth()->id());

                        Notification::make()
                            ->title('تم اعتماد الإيصال وتفعيل اشتراك المدرس بنجاح! 🚀')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPayments::route('/'),
        ];
    }
}
