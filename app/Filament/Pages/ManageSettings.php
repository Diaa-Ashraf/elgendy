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

    protected static ?string $navigationLabel = 'إعدادات النظام والموقع';

    protected static ?string $title = 'إعدادات المنصة التعليمية والموقع التعريفي';

    protected static ?string $navigationGroup = 'إدارة النظام والسلطات';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $s = app(SettingService::class);

        $this->form->fill([
            // ─── 1. الهوية والبيانات الأساسية ───
            'center_name' => $s->get('center_name', 'سنتر الأستاذ محمد الجندي التعليمي'),
            'center_phone' => $s->get('center_phone', '01000000000'),
            'center_whatsapp' => $s->get('center_whatsapp', '01000000000'),
            'center_address' => $s->get('center_address', 'القاهرة - مصر'),
            'academic_year' => $s->get('academic_year', '2026/2027'),
            'currency_symbol' => $s->get('currency_symbol', 'ج.م'),
            'center_logo' => $s->get('center_logo', ''),
            'site_favicon' => $s->get('site_favicon', ''),

            // ─── 2. ملف ونبذة المعلم ───
            'teacher_name' => $s->get('teacher_name', 'الأستاذ محمد الجندي'),
            'teacher_title' => $s->get('teacher_title', 'كبير معلمي المادة والمستشار التربوي المعتمد'),
            'teacher_subject' => $s->get('teacher_subject', 'المادة التعليمية التخصصية'),
            'teacher_experience_years' => $s->get('teacher_experience_years', '+14'),
            'teacher_students_count' => $s->get('teacher_students_count', '+10,000'),
            'teacher_image' => $s->get('teacher_image', ''),
            'teacher_quote' => $s->get('teacher_quote', 'الدرجة النهائية ليست وليدة الصدفة، بل هي نتيجة نظام محكم يربط الشرح الوافي بالمتابعة الحازمة والاختبارات الأسبوعية.'),
            'teacher_bio_heading' => $s->get('teacher_bio_heading', 'رحلة أكاديمية هدفها الأول تحويل صعوبة المادة إلى شغف وتفوق دائم'),
            'teacher_bio' => $s->get('teacher_bio', 'صاحب مسيرة تعليمية ممتدة لأكثر من 14 عاماً خرّجت آلاف الطلاب المتفوقين وأوائل الجمهورية والمحافظات، معتمدين على أسلوب الفهم التحليلي والخرائط الذهنية وتدريبات الامتحانات الشاملة.'),

            // منهجية التدريس الأربعة
            'methodology_1_title' => $s->get('methodology_1_title', 'الربط المنطقي والمفاهيمي'),
            'methodology_1_desc' => $s->get('methodology_1_desc', 'شرح القوانين والقواعد من جذورها حتى يرسخ المفهوم في ذهن الطالب دون نسيان.'),
            'methodology_2_title' => $s->get('methodology_2_title', 'حل آلاف المسائل المتدرجة'),
            'methodology_2_desc' => $s->get('methodology_2_desc', 'تدريب مستمر على مستويات التفكير العليا والأنماط الامتحانية الجديدة بدقة تامة.'),
            'methodology_3_title' => $s->get('methodology_3_title', 'متابعة فردية صارمة'),
            'methodology_3_desc' => $s->get('methodology_3_desc', 'فريق مساعدين مؤهل يصحح الواجبات ويرصد الحضور والامتحانات لحظياً.'),
            'methodology_4_title' => $s->get('methodology_4_title', 'تقييمات أسبوعية ومحاكاة'),
            'methodology_4_desc' => $s->get('methodology_4_desc', 'امتحانات محاكاة مطابقة لنظام الوزارة لكسر رهبة الامتحانات النهائية مبكراً.'),

            // ─── 3. نصوص ومحتوى الموقع التعريفي ───
            'hero_badge_text' => $s->get('hero_badge_text', 'المنظومة التعليمية الرائدة للتميز والتفوق الأكاديمي'),
            'hero_title_prefix' => $s->get('hero_title_prefix', 'صناعة التفوق تبدأ مع'),
            'hero_description' => $s->get('hero_description', 'منهجية تدريس مبسطة ترتكز على استيعاب المفاهيم، حل آلاف الأسئلة الامتحانية، ونظام متابعة ذكي ودقيق لكل طالب.'),
            'trust_stat_1' => $s->get('trust_stat_1', 'متابعة وتقييم أسبوعي'),
            'trust_stat_2' => $s->get('trust_stat_2', 'بوابة رقمية لولي الأمر'),
            'trust_stat_3' => $s->get('trust_stat_3', 'بنوك أسئلة واختبارات دورية'),

            // ركائز الهيرو الأربعة
            'hero_pillar_1_title' => $s->get('hero_pillar_1_title', 'تفوق مستمر'),
            'hero_pillar_1_desc' => $s->get('hero_pillar_1_desc', 'خطط دراسية محكمة ونتائج ملموسة'),
            'hero_pillar_2_title' => $s->get('hero_pillar_2_title', 'تقارير فورية'),
            'hero_pillar_2_desc' => $s->get('hero_pillar_2_desc', 'إشعار ولي الأمر بعد كل حصة'),
            'hero_pillar_3_title' => $s->get('hero_pillar_3_title', 'حضور ذكي QR'),
            'hero_pillar_3_desc' => $s->get('hero_pillar_3_desc', 'حضور وانصراف ذكي بدون تأخير'),
            'hero_pillar_4_title' => $s->get('hero_pillar_4_title', 'بنوك أسئلة'),
            'hero_pillar_4_desc' => $s->get('hero_pillar_4_desc', 'تغطية لكافة أفكار امتحانات الوزارة'),

            // كروت المميزات الثلاثة
            'feature_1_title' => $s->get('feature_1_title', 'مذكرات شرح وتدريبات احترافية'),
            'feature_1_desc' => $s->get('feature_1_desc', 'مذكرات مطبوعة بجودة عالية تشمل خرائط مفاهيمية، أمثلة محلولة بالتفصيل، وتدريبات متدرجة من الأساسيات حتى مستوى الامتحانات التنافسية.'),
            'feature_1_tag' => $s->get('feature_1_tag', 'تحديث دوري ومستمر للمناهج'),

            'feature_2_title' => $s->get('feature_2_title', 'بوابة إلكترونية لولي الأمر'),
            'feature_2_desc' => $s->get('feature_2_desc', 'تسجيل دخول سهل للاطلاع على نسبة حضور الطالب، درجات الامتحانات الشهرية والأسبوعية، ورصد الرصيد المالي وحالة تسليم المذكرات.'),
            'feature_2_tag' => $s->get('feature_2_tag', 'شفافية كاملة ومتابعة مباشرة'),

            'feature_3_title' => $s->get('feature_3_title', 'اختبارات دورية وتحفيز دائم'),
            'feature_3_desc' => $s->get('feature_3_desc', 'امتحان أسبوعي بعد كل درس، مع لوحة شرف للمتميزين وخصومات تشجيعية للطلاب الحاصلين على الدرجات النهائية تحفيزاً للاستمرار.'),
            'feature_3_tag' => $s->get('feature_3_tag', 'نظام حوافز ومكافآت التميز'),

            // ─── 4. السوشيال ميديا والفوتر ───
            'facebook_url' => $s->get('facebook_url', ''),
            'youtube_url' => $s->get('youtube_url', ''),
            'telegram_url' => $s->get('telegram_url', ''),
            'footer_copyright_text' => $s->get('footer_copyright_text', 'جميع الحقوق محفوظة — المنظومة التعليمية الرسمية'),

            // ─── 5. إعدادات الدفع الإلكتروني ───
            'online_payment_enabled' => (bool) $s->get('online_payment_enabled', true),
            'vodafone_cash_number' => $s->get('vodafone_cash_number', '01000000000'),
            'instapay_username' => $s->get('instapay_username', 'teacher@instapay'),
            'instapay_qr_code' => $s->get('instapay_qr_code', ''),
            'online_payment_instructions' => $s->get('online_payment_instructions', 'يرجى التحويل على رقم فودافون كاش أو حساب انستاباي ثم إرفاق صورة واضحة لإشعار التحويل لتأكيد السداد فوراً.'),

            // ─── 6. الإيصالات والواتساب ───
            'default_session_capacity' => $s->get('default_session_capacity', '25'),
            'receipt_footer_notes' => $s->get('receipt_footer_notes', 'شكراً لتعاملكم معنا - يرجى الاحتفاظ بالوصل سارياً'),
            'whatsapp_api_url' => $s->get('whatsapp_api_url', ''),
            'whatsapp_api_key' => $s->get('whatsapp_api_key', ''),
            'whatsapp_instance_id' => $s->get('whatsapp_instance_id', ''),
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
                Forms\Components\Tabs::make('SettingsNavigation')
                    ->tabs([
                        // ─── TAB 1: الهوية والبيانات الأساسية ───
                        Forms\Components\Tabs\Tab::make('الهوية والسنتر')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Forms\Components\Section::make('البيانات الأساسية للمؤسسة / السنتر')
                                    ->schema([
                                        Forms\Components\TextInput::make('center_name')
                                            ->label('اسم السنتر / المنظومة التعليمية')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('academic_year')
                                            ->label('العام الدراسي الحالي')
                                            ->default('2026/2027')
                                            ->required(),

                                        Forms\Components\TextInput::make('center_phone')
                                            ->label('رقم هاتف الاتصال الرئيسي')
                                            ->required()
                                            ->tel(),

                                        Forms\Components\TextInput::make('center_whatsapp')
                                            ->label('رقم الواتساب للاستفسارات المباشرة')
                                            ->helperText('يستخدم في زر المحادثة المباشرة في الموقع')
                                            ->tel(),

                                        Forms\Components\TextInput::make('currency_symbol')
                                            ->label('رمز العملة')
                                            ->default('ج.م')
                                            ->required(),

                                        Forms\Components\Textarea::make('center_address')
                                            ->label('العنوان التفصيلي ومقر السنتر')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('الشعار والأيقونات (Logos & Icons)')
                                    ->schema([
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
                                            ->helperText('تظهر في أعلى تبويب المتصفح بجوار العنوان'),
                                    ])
                                    ->columns(2),
                            ]),

                        // ─── TAB 2: ملف ونبذة المعلم ───
                        Forms\Components\Tabs\Tab::make('ملف ونبذة المعلم')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Forms\Components\Section::make('البيانات الشخصية والأكاديمية للمعلم')
                                    ->schema([
                                        Forms\Components\TextInput::make('teacher_name')
                                            ->label('اسم المعلم / الأستاذ')
                                            ->required(),

                                        Forms\Components\TextInput::make('teacher_title')
                                            ->label('المسمى الوظيفي والتربوي')
                                            ->required(),

                                        Forms\Components\TextInput::make('teacher_subject')
                                            ->label('المادة الأكاديمية التخصصية')
                                            ->placeholder('مثال: مادة الفيزياء والرياضيات')
                                            ->required(),

                                        Forms\Components\TextInput::make('teacher_experience_years')
                                            ->label('سنوات الخبرة (مثال: +14)')
                                            ->required(),

                                        Forms\Components\TextInput::make('teacher_students_count')
                                            ->label('عدد الطلاب الخريجين (مثال: +10,000)')
                                            ->required(),

                                        Forms\Components\FileUpload::make('teacher_image')
                                            ->label('صورة المعلم الشخصية (Portrait Photo)')
                                            ->image()
                                            ->directory('settings')
                                            ->maxSize(4096)
                                            ->imageEditor()
                                            ->helperText('صورة طولية أو مربعة عالية الدقة تظهر في الهيرو وقسم النبذة'),

                                        Forms\Components\TextInput::make('teacher_quote')
                                            ->label('اقتباس أو رسالة المعلم للطلاب')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('teacher_bio_heading')
                                            ->label('عنوان قسم النبذة الأكاديمية')
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('teacher_bio')
                                            ->label('نبذة تفصيلية عن المعلم ومسيرته')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('ركائز المنهجية التعليمية للمعلم (4 ركائز)')
                                    ->description('تظهر في قسم السيرة الأكاديمية لتوضيح أسلوب التدريس')
                                    ->schema([
                                        Forms\Components\TextInput::make('methodology_1_title')->label('عنوان الركيزة 1'),
                                        Forms\Components\TextInput::make('methodology_1_desc')->label('شرح الركيزة 1'),

                                        Forms\Components\TextInput::make('methodology_2_title')->label('عنوان الركيزة 2'),
                                        Forms\Components\TextInput::make('methodology_2_desc')->label('شرح الركيزة 2'),

                                        Forms\Components\TextInput::make('methodology_3_title')->label('عنوان الركيزة 3'),
                                        Forms\Components\TextInput::make('methodology_3_desc')->label('شرح الركيزة 3'),

                                        Forms\Components\TextInput::make('methodology_4_title')->label('عنوان الركيزة 4'),
                                        Forms\Components\TextInput::make('methodology_4_desc')->label('شرح الركيزة 4'),
                                    ])
                                    ->columns(2),
                            ]),

                        // ─── TAB 3: محتوى ونصوص الموقع التعريفي ───
                        Forms\Components\Tabs\Tab::make('محتوى الموقع التعريفي')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\Section::make('الواجهة الرئيسية (Hero Section)')
                                    ->schema([
                                        Forms\Components\TextInput::make('hero_badge_text')
                                            ->label('النص الترحيبي المصغر (Top Badge)')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('hero_title_prefix')
                                            ->label('بادئة العنوان الرئيسي (قبل اسم المدرس)'),

                                        Forms\Components\Textarea::make('hero_description')
                                            ->label('الوصف التوضيحي في الواجهة الرئيسية')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('مؤشرات الثقة (Trust Signals تحت الأزرار)')
                                    ->schema([
                                        Forms\Components\TextInput::make('trust_stat_1')->label('المؤشر 1'),
                                        Forms\Components\TextInput::make('trust_stat_2')->label('المؤشر 2'),
                                        Forms\Components\TextInput::make('trust_stat_3')->label('المؤشر 3'),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('بطاقات الإحصائيات الأربعة (Hero 4 Highlights)')
                                    ->schema([
                                        Forms\Components\TextInput::make('hero_pillar_1_title')->label('عنوان البطاقة 1'),
                                        Forms\Components\TextInput::make('hero_pillar_1_desc')->label('وصف البطاقة 1'),

                                        Forms\Components\TextInput::make('hero_pillar_2_title')->label('عنوان البطاقة 2'),
                                        Forms\Components\TextInput::make('hero_pillar_2_desc')->label('وصف البطاقة 2'),

                                        Forms\Components\TextInput::make('hero_pillar_3_title')->label('عنوان البطاقة 3'),
                                        Forms\Components\TextInput::make('hero_pillar_3_desc')->label('وصف البطاقة 3'),

                                        Forms\Components\TextInput::make('hero_pillar_4_title')->label('عنوان البطاقة 4'),
                                        Forms\Components\TextInput::make('hero_pillar_4_desc')->label('وصف البطاقة 4'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('مميزات المنظومة (لماذا يثق بنا الطلاب؟)')
                                    ->schema([
                                        Forms\Components\TextInput::make('feature_1_title')->label('عنوان الميزة 1'),
                                        Forms\Components\TextInput::make('feature_1_tag')->label('وسم الميزة 1'),
                                        Forms\Components\Textarea::make('feature_1_desc')->label('شرح الميزة 1')->rows(2)->columnSpanFull(),

                                        Forms\Components\TextInput::make('feature_2_title')->label('عنوان الميزة 2'),
                                        Forms\Components\TextInput::make('feature_2_tag')->label('وسم الميزة 2'),
                                        Forms\Components\Textarea::make('feature_2_desc')->label('شرح الميزة 2')->rows(2)->columnSpanFull(),

                                        Forms\Components\TextInput::make('feature_3_title')->label('عنوان الميزة 3'),
                                        Forms\Components\TextInput::make('feature_3_tag')->label('وسم الميزة 3'),
                                        Forms\Components\Textarea::make('feature_3_desc')->label('شرح الميزة 3')->rows(2)->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        // ─── TAB 4: السوشيال ميديا وحقوق النشر ───
                        Forms\Components\Tabs\Tab::make('السوشيال ميديا والفوتر')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Forms\Components\Section::make('روابط المنصات وقنوات التواصل')
                                    ->schema([
                                        Forms\Components\TextInput::make('facebook_url')
                                            ->label('رابط صفحة الفيسبوك (Facebook URL)')
                                            ->url()
                                            ->placeholder('https://facebook.com/...'),

                                        Forms\Components\TextInput::make('youtube_url')
                                            ->label('رابط قناة اليوتيوب (YouTube Channel)')
                                            ->url()
                                            ->placeholder('https://youtube.com/...'),

                                        Forms\Components\TextInput::make('telegram_url')
                                            ->label('رابط قناة التليجرام (Telegram Channel)')
                                            ->url()
                                            ->placeholder('https://t.me/...'),

                                        Forms\Components\TextInput::make('footer_copyright_text')
                                            ->label('نص حقوق الملكية في أسفل الموقع')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(3),
                            ]),

                        // ─── TAB 5: بوابات الدفع والمحافظ ───
                        Forms\Components\Tabs\Tab::make('طرق الدفع والمحافظ')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
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
                            ]),

                        // ─── TAB 6: الإيصالات والواتساب ───
                        Forms\Components\Tabs\Tab::make('الإيصالات والواتساب')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\Section::make('إعدادات الإيصالات والمطبوعات')
                                    ->schema([
                                        Forms\Components\TextInput::make('default_session_capacity')
                                            ->label('السعة الافتراضية للطلاب بالمجموعة')
                                            ->numeric()
                                            ->default(25)
                                            ->required(),

                                        Forms\Components\Textarea::make('receipt_footer_notes')
                                            ->label('ملاحظات تذييل إيصال السداد (الفاتورة)')
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
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $formData = $this->form->getState();
        $settingService = app(SettingService::class);

        $settingService->setMany($formData);

        Notification::make()
            ->title('تم حفظ وتحديث إعدادات النظام والموقع بنجاح')
            ->success()
            ->send();
    }
}

