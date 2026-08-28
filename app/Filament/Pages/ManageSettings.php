<?php

namespace App\Filament\Pages;

use App\Services\SettingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ManageSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'إعدادات النظام';

    protected static ?string $title = 'إعدادات النظام والسنتر التعليمي';

    protected static ?string $navigationGroup = 'إدارة النظام والسلطات';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settingService = app(SettingService::class);

        $this->form->fill([
            'center_name' => $settingService->get('center_name', 'سنتر الأستاذ محمد الجندي التعليمي'),
            'center_phone' => $settingService->get('center_phone', '01000000000'),
            'center_address' => $settingService->get('center_address', 'القاهرة - مصر'),
            'center_logo' => $settingService->get('center_logo', ''),
            'site_favicon' => $settingService->get('site_favicon', ''),
            'teacher_name' => $settingService->get('teacher_name', 'الأستاذ محمد الجندي'),
            'teacher_title' => $settingService->get('teacher_title', 'كبير معلمي المادة والمستشار التربوي المعتمد'),
            'teacher_bio' => $settingService->get('teacher_bio', 'صاحب مسيرة تعليمية ممتدة لأكثر من 14 عاماً خرّجت آلاف الطلاب المتفوقين وأوائل الجمهورية والمحافظات، معتمدين على أسلوب الفهم التحليلي والخرائط الذهنية وتدريبات الامتحانات الشاملة.'),
            'teacher_quote' => $settingService->get('teacher_quote', 'الدرجة النهائية ليست وليدة الصدفة، بل هي نتيجة نظام محكم يربط الشرح الوافي بالمتابعة الحازمة والاختبارات الأسبوعية.'),
            'teacher_experience_years' => $settingService->get('teacher_experience_years', '+14'),
            'teacher_image' => $settingService->get('teacher_image', ''),
            'receipt_footer_notes' => $settingService->get('receipt_footer_notes', 'شكراً لتعاملكم معنا - يرجى الاحتفاظ بالوصل سارياً'),
            'currency_symbol' => $settingService->get('currency_symbol', 'ج.م'),
            'default_session_capacity' => $settingService->get('default_session_capacity', '25'),
            'academic_year' => $settingService->get('academic_year', '2026/2027'),
            'whatsapp_api_url' => $settingService->get('whatsapp_api_url', ''),
            'whatsapp_api_key' => $settingService->get('whatsapp_api_key', ''),
            'whatsapp_instance_id' => $settingService->get('whatsapp_instance_id', ''),
            'online_payment_enabled' => (bool) $settingService->get('online_payment_enabled', true),
            'vodafone_cash_number' => $settingService->get('vodafone_cash_number', '01000000000'),
            'instapay_username' => $settingService->get('instapay_username', 'teacher@instapay'),
            'instapay_qr_code' => $settingService->get('instapay_qr_code', ''),
            'online_payment_instructions' => $settingService->get('online_payment_instructions', 'يرجى التحويل على رقم فودافون كاش أو حساب انستاباي ثم إرفاق صورة واضحة لإشعار التحويل لتأكيد السداد فوراً.'),
        ]);
    }

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') || $user->email === 'admin@admin.com';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('البيانات الأساسية والهوية البصرية للسنتر')
                    ->schema([
                        Forms\Components\TextInput::make('center_name')
                            ->label('اسم السنتر / المؤسسة')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('center_phone')
                            ->label('رقم هاتف التواصل')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('academic_year')
                            ->label('العام الدراسي الحالي')
                            ->default('2026/2027')
                            ->required(),

                        Forms\Components\TextInput::make('currency_symbol')
                            ->label('رمز العملة')
                            ->default('ج.م')
                            ->required(),

                        Forms\Components\FileUpload::make('center_logo')
                            ->label('شعار السنتر / اللوجو الرئيسي (Logo)')
                            ->image()
                            ->directory('settings')
                            ->maxSize(2048)
                            ->imageEditor(),

                        Forms\Components\FileUpload::make('site_favicon')
                            ->label('أيقونة المتصفح العلوي (Favicon .ico/.png)')
                            ->image()
                            ->directory('settings')
                            ->maxSize(1024)
                            ->helperText('تظهر في أعلى تبويب المتصفح بجوار العنوان (Favicon in head tab)'),

                        Forms\Components\Textarea::make('center_address')
                            ->label('العنوان التفصيلي')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('ملف ونبذة المعلم والمشرف الأكاديمي (Teacher Profile)')
                    ->description('تظهر هذه البيانات والصورة في الصفحة الرئيسية للموقع التعريفي')
                    ->schema([
                        Forms\Components\TextInput::make('teacher_name')
                            ->label('اسم المعلم / الأستاذ')
                            ->default('الأستاذ محمد الجندي')
                            ->required(),

                        Forms\Components\TextInput::make('teacher_title')
                            ->label('المسمى الوظيفي / التخصص الأكاديمي')
                            ->default('كبير معلمي المادة والمستشار التربوي المعتمد')
                            ->required(),

                        Forms\Components\TextInput::make('teacher_experience_years')
                            ->label('سنوات الخبرة (مثال: +14)')
                            ->default('+14')
                            ->required(),

                        Forms\Components\FileUpload::make('teacher_image')
                            ->label('صورة المعلم الشخصية (Portrait Photo)')
                            ->image()
                            ->directory('settings')
                            ->maxSize(4096)
                            ->imageEditor()
                            ->helperText('يفضل رفع صورة طولية أو مربعة عالية الدقة بخلفية احترافية'),

                        Forms\Components\TextInput::make('teacher_quote')
                            ->label('اقتباس أو رسالة المعلم للطلاب')
                            ->default('الدرجة النهائية ليست وليدة الصدفة، بل هي نتيجة نظام محكم يربط الشرح الوافي بالمتابعة الحازمة والاختبارات الأسبوعية.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('teacher_bio')
                            ->label('نبذة تفصيلية عن المعلم ومسيرته')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('إعدادات الدفع الإلكتروني والمحافظ (InstaPay & Cash Wallets)')
                    ->description('تظهر هذه البيانات لأولياء الأمور في البوابة لسداد الرسوم والاشتراكات أونلاين')
                    ->schema([
                        Forms\Components\Toggle::make('online_payment_enabled')
                            ->label('تفعيل استقبال المدفوعات الإلكترونية من أولياء الأمور')
                            ->default(true)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('vodafone_cash_number')
                            ->label('رقم فودافون كاش / المحافظ الإلكترونية')
                            ->placeholder('010xxxxxxxx')
                            ->tel(),

                        Forms\Components\TextInput::make('instapay_username')
                            ->label('عنوان / حساب انستاباي (InstaPay IPA / Username)')
                            ->placeholder('username@instapay'),

                        Forms\Components\FileUpload::make('instapay_qr_code')
                            ->label('صورة رمز الـ QR الخاص بـ InstaPay')
                            ->image()
                            ->directory('settings')
                            ->maxSize(2048)
                            ->helperText('يمكن لولي الأمر مسح الرمز مباشرة من تطبيق انستاباي للتحويل السريع.'),

                        Forms\Components\Textarea::make('online_payment_instructions')
                            ->label('تعليمات وملاحظات التحويل لولي الأمر')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('إعدادات الإيصالات والمطبوعات')
                    ->schema([
                        Forms\Components\TextInput::make('default_session_capacity')
                            ->label('السعة الافتراضية للطلاب بالمجموعة')
                            ->numeric()
                            ->default(25)
                            ->required(),

                        Forms\Components\Textarea::make('receipt_footer_notes')
                            ->label('ملاحظات تذيل إيصال السداد (الفاتورة)')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('إعدادات إشعارات الواتساب (WhatsApp Gateway)')
                    ->schema([
                        Forms\Components\TextInput::make('whatsapp_api_url')
                            ->label('رابط خدمة الـ API (Gateway URL)')
                            ->placeholder('https://api.ultramsg.com/instance...'),

                        Forms\Components\TextInput::make('whatsapp_instance_id')
                            ->label('معرف الجلسة (Instance ID)')
                            ->placeholder('instance12345'),

                        Forms\Components\TextInput::make('whatsapp_api_key')
                            ->label('مفتاح الوصول (API Key / Token)')
                            ->password()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $formData = $this->form->getState();
        $settingService = app(SettingService::class);

        $settingService->setMany($formData);

        Notification::make()
            ->title('تم حفظ إعدادات النظام بنجاح')
            ->success()
            ->send();
    }
}
