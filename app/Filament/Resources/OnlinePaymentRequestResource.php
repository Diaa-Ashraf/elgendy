<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OnlinePaymentRequestResource\Pages;
use App\Models\OnlinePaymentRequest;
use App\Models\StudentPayment;
use App\Services\WhatsAppNotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnlinePaymentRequestResource extends Resource
{
    protected static ?string $model = OnlinePaymentRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $modelLabel = 'طلب سداد إلكتروني';

    protected static ?string $pluralModelLabel = 'طلبات الدفع الإلكتروني (InstaPay & Cash)';

    protected static ?string $navigationLabel = 'طلبات الدفع الإلكتروني';

    protected static ?string $navigationGroup = 'الإدارة المالية';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') 
            || $user->email === 'admin@admin.com' 
            || $user->can('view_online_payment_requests');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات طلب السداد والمرفقات')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('الطالب')
                            ->relationship('student', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('group_id')
                            ->label('المجموعة')
                            ->relationship('group', 'name')
                            ->disabled(),

                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ المسدد')
                            ->prefix('ج.م')
                            ->disabled(),

                        Forms\Components\Select::make('payment_method')
                            ->label('طريقة الدفع')
                            ->options([
                                'vodafone_cash' => 'فودافون كاش / محفظة 📱',
                                'instapay' => 'انستاباي InstaPay ⚡',
                                'wallet' => 'محفظة إلكترونية أخرى',
                            ])
                            ->disabled(),

                        Forms\Components\TextInput::make('sender_phone')
                            ->label('رقم المحول منه / هاتف المحفظة')
                            ->disabled(),

                        Forms\Components\TextInput::make('transaction_reference')
                            ->label('رقم العملية / المرجع')
                            ->disabled(),

                        Forms\Components\Select::make('type')
                            ->label('نوع السداد')
                            ->options([
                                'month' => 'اشتراك شهري',
                                'session' => 'سداد بالحصة',
                            ])
                            ->disabled(),

                        Forms\Components\DatePicker::make('period_month')
                            ->label('عن شهر')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('حالة الطلب')
                            ->options([
                                'pending' => 'قيد المراجعة ⏳',
                                'approved' => 'معتمد ✅',
                                'rejected' => 'مرفوض ❌',
                            ])
                            ->disabled(),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات ولي الأمر')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('سبب الرفض (إن وجد)')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('receipt_image')
                            ->label('صورة إشعار التحويل / الإيصال')
                            ->image()
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('receipt_image')
                    ->label('الإيصال')
                    ->circular(false)
                    ->height(50)
                    ->width(50)
                    ->extraImgAttributes(['class' => 'rounded-lg shadow border border-slate-700 object-cover cursor-pointer'])
                    ->action(
                        Action::make('viewReceipt')
                            ->label('معاينة الإيصال')
                            ->modalHeading('صورة إيصال التحويل')
                            ->modalContent(fn(OnlinePaymentRequest $record) => view('filament.components.receipt-preview-modal', ['record' => $record]))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('إغلاق')
                    ),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn(OnlinePaymentRequest $record) => 'كود: #' . $record->student_id . ' | هاتف: ' . ($record->student?->parent_phone ?? '-')),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->placeholder('دفعة عامة بالحساب')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->weight('black')
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('الطريقة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'vodafone_cash' => 'danger',
                        'instapay' => 'primary',
                        default => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'vodafone_cash' => 'فودافون كاش 📱',
                        'instapay' => 'انستاباي ⚡',
                        default => 'محفظة أخرى 💳',
                    }),

                Tables\Columns\TextColumn::make('sender_phone')
                    ->label('المحول منه / المرجع')
                    ->formatStateUsing(fn(OnlinePaymentRequest $record) => ($record->sender_phone ?? '-') . ($record->transaction_reference ? ' (' . $record->transaction_reference . ')' : ''))
                    ->copyable()
                    ->copyMessage('تم نسخ رقم المحول'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'approved' => 'معتمد ✅',
                        'rejected' => 'مرفوض ❌',
                        'pending' => 'قيد المراجعة ⏳',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('وقت الرفع')
                    ->dateTime('d/m/Y - h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد المراجعة ⏳',
                        'approved' => 'معتمد ✅',
                        'rejected' => 'مرفوض ❌',
                    ])
                    ->default('pending'),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('طريقة التحويل')
                    ->options([
                        'vodafone_cash' => 'فودافون كاش',
                        'instapay' => 'انستاباي',
                    ]),
            ])
            ->actions([
                // زر اعتماد الدفعة
                Action::make('approve')
                    ->label('اعتماد ✅')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn(OnlinePaymentRequest $record) => $record->status === 'pending' && (Auth::user()?->hasRole('admin') || Auth::user()?->email === 'admin@admin.com' || Auth::user()?->can('approve_online_payment_requests')))
                    ->requiresConfirmation()
                    ->modalHeading('اعتماد وتأكيد الدفعة المالية')
                    ->modalDescription(fn(OnlinePaymentRequest $record) => "هل أنت متأكد من صحة الإيصال واعتماد سداد مبلغ {$record->amount} ج.م للطالب ({$record->student?->name})؟ سيتم إضافة المبلغ لكشف حساب الطالب وإرسال رسالة واتساب فورية لولي الأمر.")
                    ->modalSubmitActionLabel('نعم، اعتمد وأرسل إشعار')
                    ->action(function (OnlinePaymentRequest $record, WhatsAppNotificationService $waService) {
                        DB::transaction(function () use ($record, $waService) {
                            $user = Auth::user();
                            $userId = $user ? $user->id : 1;

                            // 1. إنشاء سجل الدفع المالي
                            $payment = StudentPayment::create([
                                'student_id' => $record->student_id,
                                'group_id' => $record->group_id ?? ($record->student?->groups()->first()?->id),
                                'amount' => $record->amount,
                                'type' => $record->type ?? 'month',
                                'sessions_count' => $record->type === 'session' ? 1 : null,
                                'period_month' => $record->period_month ?? now()->startOfMonth(),
                                'paid_at' => now(),
                                'payment_method' => $record->payment_method,
                                'received_by' => $userId,
                                'notes' => 'سداد إلكتروني معتمد - محول من: ' . ($record->sender_phone ?? 'غير محدد') . ($record->transaction_reference ? ' - مرجع: ' . $record->transaction_reference : '') . ($record->notes ? " [{$record->notes}]" : ''),
                            ]);

                            // 2. تحديث حالة الطلب
                            $record->update([
                                'status' => 'approved',
                                'approved_by' => $userId,
                                'approved_at' => now(),
                            ]);

                            // 3. إرسال إشعار واتساب لولي الأمر
                            $student = $record->student;
                            if ($student && $student->parent_phone) {
                                $methodLabel = $record->payment_method === 'instapay' ? 'انستاباي (InstaPay)' : 'فودافون كاش والمحافظ الإلكترونية';
                                $groupName = $record->group?->name ?? 'الاشتراك الشهري';
                                $waService->notifyOnlinePaymentApproved(
                                    $student->parent_phone,
                                    $student->name,
                                    (float) $record->amount,
                                    $methodLabel,
                                    $groupName
                                );
                            }
                        });

                        // تجهيز رابط الواتساب المباشر المجاني
                        $student = $record->student;
                        $waUrl = null;
                        if ($student && $student->parent_phone) {
                            $cleanPhone = preg_replace('/[^0-9]/', '', $student->parent_phone);
                            if (str_starts_with($cleanPhone, '01')) {
                                $cleanPhone = '2' . $cleanPhone;
                            }
                            $methodLabel = $record->payment_method === 'instapay' ? 'انستاباي (InstaPay)' : 'فودافون كاش والمحافظ الإلكترونية';
                            $groupName = $record->group?->name ?? 'الاشتراك الشهري';
                            
                            $msg = "إشعار سداد إلكتروني ناجح ✅\n\n";
                            $msg .= "المكرم ولي أمر الطالب/ة: {$student->name}\n";
                            $msg .= "نحيطكم علماً بأنه تم استلام وتأكيد سداد مبلغ ({$record->amount} ج.م) بنجاح عبر ({$methodLabel}).\n";
                            $msg .= "المجموعة: {$groupName}\n";
                            $msg .= "تم تسجيل الدفعة وتحديث كشف حساب الطالب في النظام فوراً.\n\n";
                            $msg .= "شاكرين لكم حسن تعاونكم وحرصكم الدائم.";

                            $waUrl = "https://wa.me/{$cleanPhone}?text=" . urlencode($msg);
                        }

                        $notification = Notification::make()
                            ->title('تم اعتماد الدفعة بنجاح 🚀')
                            ->body('تم تسجيل المبلغ بحساب الطالب وتحديث كشف الحساب.')
                            ->success();

                        if ($waUrl) {
                            $notification->actions([
                                \Filament\Notifications\Actions\Action::make('sendWhatsApp')
                                    ->label('فتح محادثة واتساب ولي الأمر 💬')
                                    ->url($waUrl, shouldOpenInNewTab: true)
                                    ->button()
                                    ->color('success'),
                            ]);
                        }

                        $notification->send();
                    }),

                // زر رفض الدفعة
                Action::make('reject')
                    ->label('رفض ❌')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn(OnlinePaymentRequest $record) => $record->status === 'pending' && (Auth::user()?->hasRole('admin') || Auth::user()?->email === 'admin@admin.com' || Auth::user()?->can('reject_online_payment_requests')))
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->placeholder('مثال: لم يصل التحويل على الرقم المطلوب / الصورة غير واضحة / المبلغ غير مطابق')
                            ->required(),
                    ])
                    ->modalHeading('رفض إيصال السداد الإلكتروني')
                    ->modalSubmitActionLabel('تأكيد الرفض')
                    ->action(function (OnlinePaymentRequest $record, array $data, WhatsAppNotificationService $waService) {
                        $user = Auth::user();
                        $userId = $user ? $user->id : 1;

                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'approved_by' => $userId,
                            'approved_at' => now(),
                        ]);

                        // محاولة الإرسال عبر API إن وجد
                        $student = $record->student;
                        if ($student && $student->parent_phone) {
                            $waService->notifyOnlinePaymentRejected(
                                $student->parent_phone,
                                $student->name,
                                (float) $record->amount,
                                $data['rejection_reason']
                            );
                        }

                        // تجهيز رابط الواتساب المباشر المجاني برفض الطلب والسبب
                        $waUrl = null;
                        if ($student && $student->parent_phone) {
                            $cleanPhone = preg_replace('/[^0-9]/', '', $student->parent_phone);
                            if (str_starts_with($cleanPhone, '01')) {
                                $cleanPhone = '2' . $cleanPhone;
                            }
                            $msg = "تنبيه بخصوص إيصال السداد الإلكتروني ⚠️\n\n";
                            $msg .= "المكرم ولي أمر الطالب/ة: {$student->name}\n";
                            $msg .= "نود إحاطتكم بأنه تعذر قبول إيصال السداد بقيمة ({$record->amount} ج.م) للأسباب التالية:\n";
                            $msg .= "❌ السبب: {$data['rejection_reason']}\n\n";
                            $msg .= "يرجى مراجعة إدارة السنتر أو إعادة رفع إشعار التحويل الصحيح عبر بوابة ولي الأمر.\n";
                            $msg .= "شاكرين تفهمكم.";

                            $waUrl = "https://wa.me/{$cleanPhone}?text=" . urlencode($msg);
                        }

                        $notification = Notification::make()
                            ->title('تم رفض الطلب')
                            ->body('تم تسجيل سبب الرفض بنجاح.')
                            ->warning();

                        if ($waUrl) {
                            $notification->actions([
                                \Filament\Notifications\Actions\Action::make('sendWhatsApp')
                                    ->label('إرسال سبب الرفض عبر واتساب ولي الأمر 💬')
                                    ->url($waUrl, shouldOpenInNewTab: true)
                                    ->button()
                                    ->color('danger'),
                            ]);
                        }

                        $notification->send();
                    }),

                Tables\Actions\ViewAction::make()->label('تفاصيل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOnlinePaymentRequests::route('/'),
        ];
    }
}
