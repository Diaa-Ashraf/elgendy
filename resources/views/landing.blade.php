<!DOCTYPE html>
<html dir="rtl" lang="ar" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings['center_name'] }} — المنصة التعليمية الرسمية</title>
    
    @if(!empty($settings['site_favicon']))
        <link rel="icon" href="{{ asset('storage/' . $settings['site_favicon']) }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('storage/' . $settings['site_favicon']) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $settings['site_favicon']) }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    
    {{-- AOS Animation Library --}}
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

    <style>
        body { font-family: 'Cairo', sans-serif; }
        .hero-gradient {
            background: radial-gradient(circle at 50% -20%, #2e1065 0%, #0f172a 75%, #020617 100%);
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card-hover:hover {
            transform: translateY(-6px) scale(1.01);
            border-color: rgba(129, 140, 248, 0.35);
            box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.25);
        }
        .glow-effect {
            position: relative;
        }
        .glow-effect::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            background: linear-gradient(45deg, #6366f1, #a855f7, #ec4899);
            border-radius: inherit;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .glow-effect:hover::before {
            opacity: 0.6;
            filter: blur(8px);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white overflow-x-hidden">

    {{-- ─── NAVBAR ─── --}}
    <header class="fixed top-0 inset-x-0 z-50 bg-slate-950/80 backdrop-blur-xl border-b border-slate-800/80 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="#" class="flex items-center gap-3.5 group">
                @if(!empty($settings['center_logo']))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['center_logo']) }}" alt="{{ $settings['center_name'] }}" class="w-11 h-11 object-contain rounded-2xl shadow-xl shadow-indigo-500/20 border border-slate-800 group-hover:scale-105 transition">
                @else
                    <div class="w-11 h-11 bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 rounded-2xl flex items-center justify-center font-black text-xl shadow-xl shadow-indigo-500/30 group-hover:rotate-6 transition">
                        🎓
                    </div>
                @endif
                <div>
                    <span class="font-black text-lg sm:text-xl tracking-tight text-white block group-hover:text-indigo-400 transition">{{ $settings['center_name'] }}</span>
                    <span class="text-xs text-indigo-400 font-bold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping inline-block"></span>
                        العام الدراسي {{ $settings['academic_year'] }}
                    </span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-bold text-slate-300">
                <a href="#about" class="hover:text-indigo-400 transition hover:scale-105">عن السنتر</a>
                <a href="#features" class="hover:text-indigo-400 transition hover:scale-105">مميزاتنا</a>
                <a href="#stages" class="hover:text-indigo-400 transition hover:scale-105">المراحل الدراسية</a>
                <a href="#groups" class="hover:text-indigo-400 transition hover:scale-105">المجموعات المتاحة</a>
                <a href="#enroll" class="hover:text-indigo-400 transition hover:scale-105">طلب التقديم</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('parent.login') }}" class="px-4 py-2.5 bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 rounded-xl text-xs sm:text-sm font-bold text-slate-200 transition-all flex items-center gap-2 hover:border-indigo-500/50 shadow-md">
                    🔑 <span>بوابة ولي الأمر</span>
                </a>
                <a href="/admin" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-500 hover:to-pink-500 rounded-xl text-xs sm:text-sm font-black text-white transition-all transform hover:-translate-y-0.5 shadow-xl shadow-indigo-600/30">
                    دخول اللوحة ⚡
                </a>
            </div>
        </div>
    </header>

    {{-- ─── HERO SECTION ─── --}}
    <section class="relative pt-36 pb-24 md:pt-48 md:pb-36 hero-gradient overflow-hidden">
        <div class="absolute -top-40 right-1/4 w-[500px] h-[500px] bg-indigo-600/25 rounded-full blur-[120px] pointer-events-none animate-pulse"></div>
        <div class="absolute top-1/3 left-10 w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-10 right-10 w-[350px] h-[350px] bg-pink-600/15 rounded-full blur-[90px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div data-aos="fade-down" class="inline-flex items-center gap-2.5 px-5 py-2 bg-indigo-950/80 border border-indigo-500/40 rounded-full text-indigo-300 font-extrabold text-xs sm:text-sm mb-8 shadow-xl backdrop-blur-md">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>🌟 المنصة الأكاديمية الرائدة للتميز والتفوق الدراسية</span>
            </div>

            <h1 data-aos="fade-up" data-aos-delay="100" class="text-3xl sm:text-5xl lg:text-7xl font-black text-white leading-[1.25] tracking-tight mb-8 max-w-5xl mx-auto">
                منظومة تعليمية متطورة تضمن <br>
                <span class="bg-gradient-to-r from-indigo-400 via-purple-300 to-pink-400 bg-clip-text text-transparent underline decoration-indigo-500/40 decoration-wavy underline-offset-8">التفوق والوصول لأعلى المراتب</span>
            </h1>

            <p data-aos="fade-up" data-aos-delay="200" class="text-slate-300 font-semibold text-base sm:text-xl max-w-3xl mx-auto mb-12 leading-relaxed">
                مرحباً بكم في <strong class="text-white font-extrabold">{{ $settings['center_name'] }}</strong> — بيئة شمولية تفاعلية تعتمد على أحدث الأساليب الأكاديمية، متابعة الحضور بالـ QR Code، واختبارات قياس المستوى اللحظية.
            </p>

            <div data-aos="fade-up" data-aos-delay="300" class="flex flex-col sm:flex-row items-center justify-center gap-4 max-w-lg mx-auto">
                <a href="#enroll" class="w-full sm:w-auto px-9 py-4.5 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-500 hover:to-pink-500 text-white font-black rounded-2xl text-base shadow-2xl shadow-indigo-600/40 transition-all transform hover:-translate-y-1 hover:scale-105 flex items-center justify-center gap-2 glow-effect">
                    <span>📝 قدم الآن أونلاين</span>
                    <span class="text-lg">←</span>
                </a>
                <a href="#groups" class="w-full sm:w-auto px-8 py-4.5 bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 text-slate-200 font-extrabold rounded-2xl text-base transition-all hover:border-indigo-500/50 backdrop-blur-md shadow-lg">
                    📅 استعرض المجموعات
                </a>
            </div>

            {{-- 📊 STATS CARDS --}}
            <div data-aos="fade-up" data-aos-delay="400" class="grid grid-cols-2 md:grid-cols-4 gap-5 mt-20 max-w-5xl mx-auto">
                <div class="glass-card glass-card-hover p-6 rounded-3xl text-center">
                    <div class="w-12 h-12 bg-indigo-500/10 text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl font-black border border-indigo-500/20">👨‍🎓</div>
                    <span class="block text-3xl sm:text-4xl font-black text-white mb-1">+1000</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-400">طالب وطالبة متفوقين</span>
                </div>
                <div class="glass-card glass-card-hover p-6 rounded-3xl text-center">
                    <div class="w-12 h-12 bg-purple-500/10 text-purple-400 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl font-black border border-purple-500/20">📱</div>
                    <span class="block text-3xl sm:text-4xl font-black text-purple-300 mb-1">100%</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-400">متابعة إلكترونية لولي الأمر</span>
                </div>
                <div class="glass-card glass-card-hover p-6 rounded-3xl text-center">
                    <div class="w-12 h-12 bg-emerald-500/10 text-emerald-400 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl font-black border border-emerald-500/20">⚡</div>
                    <span class="block text-3xl sm:text-4xl font-black text-emerald-400 mb-1">QR Code</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-400">حضور سريع وتأكيد آلي</span>
                </div>
                <div class="glass-card glass-card-hover p-6 rounded-3xl text-center">
                    <div class="w-12 h-12 bg-pink-500/10 text-pink-400 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl font-black border border-pink-500/20">🏆</div>
                    <span class="block text-3xl sm:text-4xl font-black text-pink-400 mb-1">دوري</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-400">امتحانات وتقييمات مستمرة</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── FEATURES SECTION ─── --}}
    <section id="features" class="py-24 bg-slate-950 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="px-4 py-1.5 bg-indigo-950 border border-indigo-800 text-indigo-400 rounded-full text-xs font-black uppercase tracking-wider mb-3 inline-block">لماذا نتميز؟</span>
                <h2 class="text-3xl sm:text-5xl font-black text-white leading-snug">رؤية تعليمية حديثة تلائم طموحك</h2>
                <p class="text-slate-400 font-semibold text-base mt-4">نوفر أدوات أكاديمية وتكنولوجية متكاملة تساعد الطالب على تحصيل أعلى الدرجات بكل سهولة.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="glass-card glass-card-hover p-8 rounded-3xl space-y-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-14 h-14 bg-indigo-600/20 text-indigo-400 rounded-2xl flex items-center justify-center text-3xl font-black border border-indigo-500/30">
                        📚
                    </div>
                    <h3 class="text-xl font-black text-white">مناهج وملازم مطورة</h3>
                    <p class="text-slate-400 text-sm font-semibold leading-relaxed">
                        خرائط ذهنية ملونة، أسئلة متدرجة الصعوبة، وملازم مخصصة تدعم الفهم العميق وتمنع تشتت الطالب.
                    </p>
                </div>

                <div class="glass-card glass-card-hover p-8 rounded-3xl space-y-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-14 h-14 bg-purple-600/20 text-purple-400 rounded-2xl flex items-center justify-center text-3xl font-black border border-purple-500/30">
                        📊
                    </div>
                    <h3 class="text-xl font-black text-white">بوابة متابعة أولياء الأمور</h3>
                    <p class="text-slate-400 text-sm font-semibold leading-relaxed">
                        شفافية كاملة بتقرير مالي وأكاديمي مباشر يشمل نسبة الحضور، درجات الاختبارات، وكشف الحساب.
                    </p>
                </div>

                <div class="glass-card glass-card-hover p-8 rounded-3xl space-y-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-14 h-14 bg-emerald-600/20 text-emerald-400 rounded-2xl flex items-center justify-center text-3xl font-black border border-emerald-500/30">
                        🎯
                    </div>
                    <h3 class="text-xl font-black text-white">تحفيز وتكريم المتفوقين</h3>
                    <p class="text-slate-400 text-sm font-semibold leading-relaxed">
                        نظام خصومات دراسية للمتفوقين، مكافآت دورية لأوائل الاختيارات، ومتابعة شخصية مستمرة.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── TEACHER BIO & ABOUT SECTION ─── --}}
    <section id="about" class="py-24 bg-slate-900/40 border-y border-slate-800/60 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-6" data-aos="fade-right">
                    <div class="inline-block px-4 py-1.5 bg-indigo-950 border border-indigo-800 text-indigo-400 rounded-lg text-xs font-black">عن المعلم والمؤسس</div>
                    <h2 class="text-3xl sm:text-5xl font-black text-white leading-snug">
                        خبرة سنوات في تبسيط المناهج وإعداد الأوائل
                    </h2>
                    <p class="text-slate-300 leading-relaxed font-semibold text-base sm:text-lg">
                        هدفنا ليس مجرد تحفيظ المناهج، بل بناء عقلية تفكر وتفهم. نعتمد على أحدث أساليب الشرح والتطبيقات المباشرة مع نظام متابعة إلكتروني شامل لكل طالب وولي أمر.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
                        <div class="p-5 bg-slate-950/80 rounded-2xl border border-slate-800 shadow-md">
                            <div class="text-3xl mb-2">💡</div>
                            <h3 class="font-extrabold text-white text-base mb-1">فهم وتأسيس متين</h3>
                            <p class="text-xs text-slate-400 font-semibold">تفكيك عقد المواد والشرح بأسلوب تفاعلي يناسب كافة المستويات.</p>
                        </div>
                        <div class="p-5 bg-slate-950/80 rounded-2xl border border-slate-800 shadow-md">
                            <div class="text-3xl mb-2">📈</div>
                            <h3 class="font-extrabold text-white text-base mb-1">تقييمات دورية مستمرة</h3>
                            <p class="text-xs text-slate-400 font-semibold">امتحانات بعد كل حصة مع تقارير أداء فورية تصل لولي الأمر.</p>
                        </div>
                    </div>
                </div>

                <div class="relative" data-aos="fade-left">
                    <div class="bg-gradient-to-tr from-indigo-950 via-slate-900 to-purple-950 rounded-3xl border border-slate-800/80 p-8 flex flex-col justify-between shadow-2xl relative overflow-hidden space-y-8 animate-float">
                        <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-purple-500/20 rounded-full blur-3xl"></div>
                        <div class="flex items-center gap-5">
                            @if(!empty($settings['center_logo']))
                                <img src="{{ asset('storage/' . $settings['center_logo']) }}" class="w-20 h-20 object-contain rounded-2xl border border-indigo-500/30 shadow-2xl">
                            @else
                                <div class="w-20 h-20 bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-600 rounded-2xl flex items-center justify-center font-black text-4xl text-white shadow-2xl shadow-indigo-600/40">
                                    👨‍🏫
                                </div>
                            @endif
                            <div>
                                <h3 class="text-2xl font-black text-white">{{ $settings['center_name'] }}</h3>
                                <p class="text-xs font-black text-indigo-400 mt-1">معلم خبير ومستشار أكاديمي</p>
                            </div>
                        </div>

                        <blockquote class="text-slate-300 italic font-semibold text-sm leading-relaxed border-r-2 border-indigo-500 pr-4">
                            "الطريق إلى التفوق والقمة يكمن في الالتزام والاستمرارية والمتابعة الدقيقة.. ونحن هنا لنأخذ بيدك خطوة بخطوة نحو النجاح."
                        </blockquote>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-800/80 text-xs font-bold text-slate-400">
                            <span>📞 التواصل الرسمي: {{ $settings['center_phone'] }}</span>
                            <span class="text-emerald-400">متاح التسجيل الآن 🟢</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── EDUCATIONAL STAGES SECTION ─── --}}
    <section id="stages" class="py-24 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="px-4 py-1.5 bg-purple-950 border border-purple-800 text-purple-400 rounded-full text-xs font-black uppercase tracking-wider mb-3 inline-block">المراحل التعليمية</span>
                <h2 class="text-3xl sm:text-5xl font-black text-white">اختر مرحلتك الدراسية</h2>
                <p class="text-slate-400 font-semibold text-base mt-4">نقدم برامج مخصصة ومجموعات منفصلة لكل صف دراسي لضمان تركيز أعلى.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($stages as $index => $stage)
                    <div class="glass-card glass-card-hover p-8 rounded-3xl flex flex-col justify-between" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                        <div>
                            <div class="w-12 h-12 bg-indigo-600/20 text-indigo-400 rounded-xl flex items-center justify-center font-black text-xl mb-6 border border-indigo-500/30">
                                🏫
                            </div>
                            <h3 class="text-2xl font-black text-white mb-2">{{ $stage->name }}</h3>
                            <p class="text-slate-400 text-xs font-bold mb-6">عدد الطلاب المقيدين بها: {{ $stage->students_count }} طالب وطالبة</p>
                        </div>

                        <a href="#enroll" onclick="selectStage('{{ $stage->id }}')" class="w-full py-3 bg-slate-900 hover:bg-indigo-600 border border-slate-700/80 hover:border-indigo-500 text-white font-extrabold rounded-xl text-xs transition-all text-center block">
                            حجز موعد أو تقديم طلب ➔
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── GROUPS & TIMETABLE SECTION ─── --}}
    <section id="groups" class="py-24 bg-slate-900/40 border-y border-slate-800/60 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="px-4 py-1.5 bg-emerald-950 border border-emerald-800 text-emerald-400 rounded-full text-xs font-black uppercase tracking-wider mb-3 inline-block">المجموعات المتاحة</span>
                <h2 class="text-3xl sm:text-5xl font-black text-white">مواعيد المجموعات الدراسية</h2>
                <p class="text-slate-400 font-semibold text-base mt-4">اختر المجموعة والجدول المناسب لك وانضم فوراً.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($groups as $index => $group)
                    <div class="glass-card glass-card-hover p-8 rounded-3xl flex flex-col justify-between relative overflow-hidden" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 bg-indigo-950 text-indigo-300 rounded-full text-xs font-black border border-indigo-800">
                                    {{ $group->educationalStage?->name }}
                                </span>
                                <span class="text-xs font-black text-emerald-400 bg-emerald-950 px-2.5 py-1 rounded-full border border-emerald-800">
                                    {{ number_format($group->price_per_month) }} ج.م / شهرياً
                                </span>
                            </div>

                            <h3 class="text-2xl font-black text-white pt-2">{{ $group->name }}</h3>
                            <p class="text-xs font-extrabold text-slate-400">المادة: <span class="text-white">{{ $group->subject?->name }}</span></p>

                            <div class="space-y-2 pt-2 border-t border-slate-800/80">
                                <span class="text-xs font-black text-slate-400 block">📅 المواعيد الأسبوعية:</span>
                                @forelse($group->schedules as $sched)
                                    <div class="flex items-center justify-between text-xs font-bold text-slate-300 bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">
                                        <span>
                                            @switch($sched->day_of_week)
                                                @case('sat') السبت @break
                                                @case('sun') الأحد @break
                                                @case('mon') الإثنين @break
                                                @case('tue') الثلاثاء @break
                                                @case('wed') الأربعاء @break
                                                @case('thu') الخميس @break
                                                @case('fri') الجمعة @break
                                                @default {{ $sched->day_of_week }}
                                            @endswitch
                                        </span>
                                        <span class="font-mono text-indigo-400">{{ \Carbon\Carbon::parse($sched->start_time)->format('h:i A') }}</span>
                                    </div>
                                @empty
                                    <span class="text-xs text-slate-500 italic block">يتم تحديث المواعيد قريباً</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="pt-6">
                            <a href="#enroll" onclick="selectGroup('{{ $group->stage_id }}', '{{ $group->id }}')" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-black rounded-xl text-xs transition-all text-center block shadow-lg shadow-indigo-600/30">
                                اختيار هذه المجموعة للانضمام 🚀
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── ENROLLMENT FORM SECTION ─── --}}
    <section id="enroll" class="py-12 sm:py-24 relative">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass-card p-5 sm:p-14 rounded-3xl shadow-2xl relative" data-aos="zoom-in">
                <div class="text-center max-w-xl mx-auto mb-8 sm:mb-12">
                    <span class="px-3 py-1 sm:px-4 sm:py-1.5 bg-emerald-950 border border-emerald-800 text-emerald-400 rounded-full text-[10px] sm:text-xs font-black uppercase tracking-wider mb-3 inline-block">التقديم أونلاين</span>
                    <h2 class="text-2xl sm:text-4xl font-black text-white mb-2 sm:mb-3">استمارة التقديم والالتحاق للطلاب الجدد</h2>
                    <p class="text-slate-400 text-xs sm:text-sm font-semibold leading-relaxed px-2">قم بملء البيانات وسنقوم بالتواصل معك لترتيب بدء الحضور وموعد الاختبار.</p>
                </div>

                <form id="enrollmentForm" class="space-y-4 sm:space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">اسم الطالب ثلاثي / رباعي *</label>
                            <input type="text" name="name" required placeholder="أحمد محمد علي" class="w-full px-4 py-3 sm:py-3.5 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">النوع *</label>
                            <select name="gender" required class="w-full px-4 py-3 sm:py-3.5 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition">
                                <option value="male">ذكر</option>
                                <option value="female">أنثى</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">المرحلة الدراسية *</label>
                            <select id="stageSelect" name="stage_id" required class="w-full px-4 py-3 sm:py-3.5 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition">
                                <option value="">-- اختر المرحلة --</option>
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">المجموعة المرغوبة (اختياري)</label>
                            <select id="groupSelect" name="group_id" class="w-full px-4 py-3 sm:py-3.5 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition">
                                <option value="">-- اختر المجموعة بعد تحديد المرحلة --</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">رقم هاتف ولي الأمر *</label>
                            <input type="tel" name="parent_phone" required placeholder="01xxxxxxxxx" class="w-full px-4 py-3 sm:py-3.5 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">رقم هاتف الطالب (إن وجد)</label>
                            <input type="tel" name="phone" placeholder="01xxxxxxxxx" class="w-full px-4 py-3 sm:py-3.5 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">العنوان السكني</label>
                        <input type="text" name="address" placeholder="المدينة / المنطقة..." class="w-full px-4 py-3 sm:py-3.5 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">ملاحظات إضافية</label>
                        <textarea name="notes" rows="2" placeholder="أي ملاحظات أو استفسارات إضافية..." class="w-full px-4 py-3 sm:py-3.5 bg-slate-950 border border-slate-800 rounded-xl text-xs sm:text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition"></textarea>
                    </div>

                    <div id="formResponse" class="hidden p-4 rounded-xl text-xs font-bold"></div>

                    <button type="submit" class="w-full py-3.5 sm:py-4.5 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black rounded-2xl text-sm sm:text-base shadow-xl shadow-emerald-600/30 transition transform hover:-translate-y-0.5">
                        تأكيد إرسال طلب التقديم 🚀
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- ─── FOOTER ─── --*/}}
    <footer class="bg-slate-950 border-t border-slate-800/80 py-14 text-slate-400 text-xs font-bold">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @if(!empty($settings['center_logo']))
                    <img src="{{ asset('storage/' . $settings['center_logo']) }}" class="w-10 h-10 object-contain rounded-xl border border-slate-800">
                @endif
                <div>
                    <p class="text-white font-black text-sm mb-0.5">{{ $settings['center_name'] }}</p>
                    <p class="text-slate-400 text-xs">العنوان: {{ $settings['center_address'] }} — هاتف: {{ $settings['center_phone'] }}</p>
                </div>
            </div>
            <p>© {{ date('Y') }} جميع الحقوق محفوظة — المنصة التعليمية الذكية.</p>
        </div>
    </footer>

    {{-- AOS Script --}}
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-out-cubic'
        });

        const stageSelect = document.getElementById('stageSelect');
        const groupSelect = document.getElementById('groupSelect');

        function selectStage(stageId) {
            stageSelect.value = stageId;
            stageSelect.dispatchEvent(new Event('change'));
        }

        stageSelect.addEventListener('change', function() {
            const stageId = this.value;
            groupSelect.innerHTML = '<option value="">-- جار التحميل... --</option>';
            
            if(!stageId) {
                groupSelect.innerHTML = '<option value="">-- اختر المجموعة بعد تحديد المرحلة --</option>';
                return;
            }

            fetch(`/api/stages/${stageId}/groups`)
                .then(res => res.json())
                .then(data => {
                    groupSelect.innerHTML = '<option value="">-- اختر المجموعة (اختياري) --</option>';
                    data.forEach(g => {
                        groupSelect.innerHTML += `<option value="${g.id}">${g.name} (${g.price_per_month} ج.م/شهرياً)</option>`;
                    });
                });
        });

        function selectGroup(stageId, groupId) {
            stageSelect.value = stageId;
            stageSelect.dispatchEvent(new Event('change'));
            setTimeout(() => {
                groupSelect.value = groupId;
            }, 300);
        }

        document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const responseDiv = document.getElementById('formResponse');
            responseDiv.classList.add('hidden');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerText;
            submitBtn.disabled = true;
            submitBtn.innerText = 'جار إرسال الطلب... ⏳';

            const formData = new FormData(this);

            const csrfToken = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch('{{ route("enroll.submit") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    let errMsg = data.message || 'حدث خطأ أثناء إرسال الطلب';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join(' - ');
                    }
                    throw new Error(errMsg);
                }
                return data;
            })
            .then(data => {
                responseDiv.className = 'p-4 rounded-xl text-xs font-bold bg-emerald-950 border border-emerald-800 text-emerald-300';
                responseDiv.innerText = data.message;
                responseDiv.classList.remove('hidden');
                this.reset();
            })
            .catch(err => {
                responseDiv.className = 'p-4 rounded-xl text-xs font-bold bg-rose-950 border border-rose-800 text-rose-300';
                responseDiv.innerText = err.message || 'حدث خطأ أثناء إرسال الطلب، يرجى التأكد من البيانات والمحاولة مجدداً.';
                responseDiv.classList.remove('hidden');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerText = originalBtnText;
            });
        });
    </script>
</body>
</html>
