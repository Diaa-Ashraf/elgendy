<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'المدرسين والسناتر';

    protected static ?string $modelLabel = 'مؤسسة / سنتر';

    protected static ?string $pluralModelLabel = 'المدرسين والسناتر المشتركة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات المؤسسة والمدرس')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم السنتر / المدرس')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label('الرابط المخصص (Slug)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText('يستخدم في رابط البوابة: yourdomain.com/t/{slug}'),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email(),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('الحساب نشط ويعمل')
                            ->default(true),

                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('تاريخ انتهاء الفترة التجريبية'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('خطة الاشتراك والحساب الإداري للمدرس')
                    ->description('تحديد باقة الاشتراك وحساب الدخول الخاص بالمدرس مباشرة')
                    ->schema([
                        Forms\Components\Select::make('plan_id')
                            ->label('باقة الاشتراك')
                            ->options(\App\Models\Plan::where('is_active', true)->pluck('name', 'id'))
                            ->default(fn () => \App\Models\Plan::first()?->id)
                            ->required()
                            ->dehydrated(false)
                            ->helperText('اختر الباقة التي تريد إسناد المدرس إليها فوراً'),

                        Forms\Components\Select::make('subscription_status')
                            ->label('حالة الاشتراك المبدئية')
                            ->options([
                                'trial' => 'فترة تجريبية مجانية (7 أيام)',
                                'active' => 'نشط ومفعل مباشرة (شهر كامل)',
                            ])
                            ->default('active')
                            ->required()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('admin_name')
                            ->label('اسم صاحب الحساب / المدرس')
                            ->placeholder('مثال: أ. محمد أحمد')
                            ->visibleOn('create')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('admin_password')
                            ->label('كلمة مرور حساب المدرس')
                            ->password()
                            ->default('123456789')
                            ->visibleOn('create')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false)
                            ->helperText('كلمة المرور الافتراضية 123456789'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('السنتر / المدرس')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('الرابط')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('subscription.plan.name')
                    ->label('الباقة الحالية')
                    ->badge()
                    ->color('success')
                    ->default('بدون باقة'),

                Tables\Columns\TextColumn::make('subscription.status')
                    ->label('حالة الاشتراك')
                    ->badge()
                    ->colors([
                        'warning' => 'trial',
                        'success' => 'active',
                        'danger' => ['expired', 'cancelled'],
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الانضمام')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('حالة التفعيل'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
