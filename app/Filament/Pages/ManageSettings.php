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
            'center_name' => $settingService->get('center_name', 'سنتر الأستاذ محمد الغندي التعليمي'),
            'center_phone' => $settingService->get('center_phone', '01000000000'),
            'center_address' => $settingService->get('center_address', 'القاهرة - مصر'),
            'center_logo' => $settingService->get('center_logo', ''),
            'site_favicon' => $settingService->get('site_favicon', ''),
            'receipt_footer_notes' => $settingService->get('receipt_footer_notes', 'شكراً لتعاملكم معنا - يرجى الاحتفاظ بالوصل سارياً'),
            'currency_symbol' => $settingService->get('currency_symbol', 'ج.م'),
            'default_session_capacity' => $settingService->get('default_session_capacity', '25'),
            'academic_year' => $settingService->get('academic_year', '2026/2027'),
            'whatsapp_api_url' => $settingService->get('whatsapp_api_url', ''),
            'whatsapp_api_key' => $settingService->get('whatsapp_api_key', ''),
            'whatsapp_instance_id' => $settingService->get('whatsapp_instance_id', ''),
        ]);
    }

    public static function canAccess(): bool
    {
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
