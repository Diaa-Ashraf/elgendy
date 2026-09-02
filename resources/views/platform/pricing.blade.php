<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>خطط وباقات الاشتراك — المنظومة السحابية الذكية لإدارة المدرسين والسناتر</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Alexandria', sans-serif; }
        .saas-pattern {
            background-color: #030712;
            background-image: 
                radial-gradient(at 100% 0%, rgba(56, 189, 248, 0.12) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(99, 102, 241, 0.12) 0px, transparent 50%);
        }
        .grid-lines {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
    </style>
</head>
<body class="saas-pattern text-slate-100 min-h-screen selection:bg-sky-500 selection:text-white relative">
    <div class="grid-lines absolute inset-0 pointer-events-none"></div>

    {{-- نافبار علوي نظيف وبسيط --}}
    <header class="border-b border-slate-800/80 backdrop-blur-md sticky top-0 z-50 px-4 sm:px-8 py-4 bg-slate-950/60">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ route('platform.home') }}" class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-xl text-white shadow-lg shadow-sky-500/20">
                    ⚡
                </div>
                <div class="text-right">
                    <span class="font-heading font-extrabold text-base text-white block leading-tight">المنظومة السحابية</span>
                    <span class="text-[11px] font-semibold text-sky-400">نظام إدارة المعلمين والسناتر</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('platform.register') }}" class="px-4 py-2 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white rounded-xl text-xs sm:text-sm font-bold shadow-lg shadow-sky-500/25 transition">
                    ابدأ تجربتك المجانية 🎁
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 relative z-10 space-y-16">

        {{-- الهيدر الرئيسي وتوضيح التجربة المجانية --}}
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-500/10 border border-sky-500/30 text-sky-300 text-xs font-bold shadow-inner">
                <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                <span>بدون رسوم إعداد أو مصاريف خفية إطلاقاً</span>
            </div>

            <h1 class="font-heading font-black text-3xl sm:text-5xl text-white leading-tight">
                اختر الباقة المناسبة لحجم شغلك، <br class="hidden sm:inline">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-teal-300 to-indigo-400">وجرّب مجاناً أول أسبوع بالكامل</span>
            </h1>

            <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                كل ما تحتاجه لإدارة طلابك، امتحاناتك الأونلاين، حساباتك، وبوابات أولياء الأمور مع موقع خاص بك — في مكان احترافي موحد.
            </p>
        </div>

        {{-- بطاقات الخطط والأسعار --}}
        <div id="plans" class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch pt-4">
            @foreach($plans as $plan)
                <div class="rounded-3xl p-7 sm:p-8 flex flex-col justify-between transition-all duration-300 relative {{ $plan->is_popular ? 'bg-gradient-to-b from-slate-800/90 to-slate-900/90 border-2 border-sky-500 shadow-2xl shadow-sky-500/15 scale-100 md:-translate-y-2' : 'bg-slate-900/60 border border-slate-800 hover:border-slate-700' }}">
                    
                    {{-- شارة الأكثر طلباً --}}
                    @if($plan->is_popular)
                        <div class="absolute -top-4 right-1/2 translate-x-1/2 bg-gradient-to-r from-sky-500 to-indigo-600 text-white text-[11px] font-black px-4 py-1.5 rounded-full uppercase tracking-wider shadow-lg shadow-sky-500/30">
                            ⭐ الأكثر اختياراً للسناتر
                        </div>
                    @endif

                    <div class="space-y-6">
                        {{-- عنوان الخطة ونبذة عنها --}}
                        <div class="border-b border-slate-800/80 pb-6">
                            <h3 class="font-heading font-extrabold text-2xl text-white mb-1.5">{{ $plan->name }}</h3>
                            <p class="text-xs text-slate-400">
                                @if($plan->slug === 'starter')
                                    مثالية للمدرس في بداية تأسيس مجموعاته الخاصة
                                @elseif($plan->slug === 'growth')
                                    للنشاط المتوسع الذي يحتاج متابعة يومية قوية للطلاب
                                @else
                                    للسناتر الضخمة وكبار المدرسين مع فريق مساعدين كبير
                                @endif
                            </p>

                            <div class="mt-6 flex items-baseline gap-1">
                                <span class="font-heading font-black text-4xl sm:text-5xl text-white">{{ number_format($plan->price_monthly, 0) }}</span>
                                <span class="text-xs font-bold text-slate-400">ج.م / شهرياً</span>
                            </div>
                            <span class="inline-block mt-2 text-[11px] font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-md border border-emerald-500/20">
                                🎁 تجربة أول 7 أيام مجاناً
                            </span>
                        </div>

                        {{-- أرقام الحدود الأساسية --}}
                        <div class="grid grid-cols-3 gap-2 py-2 text-center bg-slate-950/60 rounded-2xl border border-slate-800/80 p-3">
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold">الطلاب</div>
                                <div class="text-xs sm:text-sm font-black text-sky-400">{{ $plan->max_students >= 5000 ? '5,000+' : $plan->max_students }}</div>
                            </div>
                            <div class="border-x border-slate-800">
                                <div class="text-[10px] text-slate-400 font-bold">المجموعات</div>
                                <div class="text-xs sm:text-sm font-black text-indigo-400">{{ $plan->max_groups }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold">المساعدين</div>
                                <div class="text-xs sm:text-sm font-black text-teal-400">{{ $plan->max_teachers }}</div>
                            </div>
                        </div>

                        {{-- قائمة المميزات --}}
                        <div class="space-y-3 pt-2">
                            <span class="text-[11px] font-extrabold text-slate-300 uppercase tracking-wider block">ما تتضمنه الباقة:</span>
                            @if(!empty($plan->features))
                                <ul class="space-y-2.5 text-xs text-slate-300">
                                    @foreach($plan->features as $feature)
                                        <li class="flex items-start gap-2.5">
                                            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] shrink-0 mt-0.5">✓</span>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    {{-- زر الاشتراك المباشر --}}
                    <div class="pt-8">
                        <a href="{{ route('platform.register', ['plan' => $plan->id]) }}" class="w-full py-3.5 rounded-2xl font-bold text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2 {{ $plan->is_popular ? 'bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white shadow-lg shadow-sky-500/30' : 'bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700' }}">
                            <span>ابدأ تجربتك المجانية الآن</span>
                            <span>➔</span>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- قسم الضمان والأسئلة الشائعة السريعة --}}
        <div class="bg-gradient-to-r from-slate-900/90 via-slate-900/60 to-slate-900/90 border border-slate-800 rounded-3xl p-8 max-w-4xl mx-auto text-center space-y-4 shadow-xl">
            <div class="w-12 h-12 bg-sky-500/10 text-sky-400 border border-sky-500/30 rounded-2xl flex items-center justify-center text-2xl mx-auto">
                🔒
            </div>
            <h3 class="font-heading font-extrabold text-xl text-white">بياناتك ومجموعاتك وطلابك في أمان تام</h3>
            <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-2xl mx-auto">
                طرق سداد مرنة عبر <strong>InstaPay</strong> أو <strong>محافظ فودافون كاش</strong>. تفعيل فوري مع دعم فني مخصص لنقل بيانات طلابك ومساعدتك في تجهيز بنوك الأسئلة مجاناً خلال فترة التجربة.
            </p>
        </div>

    </main>

    {{-- التذييل --}}
    <footer class="border-t border-slate-900 text-center text-xs text-slate-500 py-8 relative z-10">
        جميع الحقوق محفوظة © {{ date('Y') }} — المنظومة السحابية للمؤسسات التعليمية
    </footer>

</body>
</html>
