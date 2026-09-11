<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بوابة الطالب — تسجيل الدخول</title>
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            teal: '#0D3B4C',
                            'teal-dark': '#082531',
                            coral: '#FF5E36',
                            'coral-hover': '#F2481F',
                            mint: '#10B981',
                            amber: '#F59E0B',
                            bg: '#F8FAFC',
                            slate: '#0F172A',
                            muted: '#64748B'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Alexandria', sans-serif; }
        .bento-pattern {
            background-color: #F8FAFC;
            background-image: radial-gradient(rgba(13, 59, 76, 0.08) 1.2px, transparent 1.2px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bento-pattern text-brand-slate min-h-screen flex flex-col justify-between p-4 sm:p-6 selection:bg-brand-coral selection:text-white relative overflow-x-hidden">

    {{-- خلفيات ضوئية Ambient Glow --}}
    <div class="fixed top-0 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 left-1/4 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

    {{-- شريط العودة والتبديل --}}
    <div class="max-w-md mx-auto w-full pt-2 sm:pt-4 flex justify-between items-center relative z-10">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs font-bold text-brand-muted hover:text-brand-teal transition bg-white/90 backdrop-blur-sm px-4 py-2 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md">
            <span>←</span>
            <span>الموقع الرئيسي</span>
        </a>
        <a href="{{ route('parent.login') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl text-xs font-bold bg-white/90 hover:bg-white text-brand-teal border border-slate-200 shadow-sm transition">
            <span>👨‍👩‍👧‍👦</span>
            <span>بوابة ولي الأمر</span>
        </a>
    </div>

    {{-- كارت تسجيل الدخول للطالب --}}
    <div class="w-full max-w-md mx-auto bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-[2rem] p-7 sm:p-9 shadow-xl shadow-blue-900/5 relative z-10 my-auto">
        <div class="text-center mb-7">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600 text-white rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-blue-500/25 border border-blue-400/30">
                🎓
            </div>
            <h1 class="font-heading font-black text-2xl sm:text-3xl text-brand-slate mb-2">بوابة الطالب الذكية</h1>
            <p class="text-xs sm:text-sm text-brand-muted font-medium leading-relaxed">
                أداء الامتحانات الإلكترونية، متابعة الواجبات، ومطالعة كشف الحضور والدرجات
            </p>
        </div>

        @if($errors->any())
            <div class="mb-5 p-4 bg-rose-50/90 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold leading-relaxed flex items-center gap-2.5">
                <span class="text-base">⚠️</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('student.login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-brand-slate mb-2">رقم الهاتف (رقمك أو رقم ولي أمرك)</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="01xxxxxxxxx" class="w-full px-4 py-3.5 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-brand-slate placeholder:text-slate-400 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition" dir="ltr">
            </div>

            <div>
                <label class="block text-xs font-bold text-brand-slate mb-2">كود الطالب الأكاديمي (#ID)</label>
                <input type="text" inputmode="numeric" name="student_id" value="{{ old('student_id') }}" required placeholder="مثال: 104" class="w-full px-4 py-3.5 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-brand-slate placeholder:text-slate-400 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition" dir="ltr">
            </div>

            <button type="submit" class="w-full py-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl font-heading font-bold text-xs sm:text-sm transition-all duration-300 shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/35 flex items-center justify-center gap-2 mt-3 transform active:scale-[0.99]">
                <span>دخول حسابي كطالب</span>
                <span class="text-base">➔</span>
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 space-y-2 text-center text-xs text-brand-muted leading-relaxed">
            <div class="flex items-center justify-center gap-1.5 text-blue-700 font-bold">
                <span></span>
                <span>يمكنك الدخول برقمك الشخصي أو برقم ولي أمرك مع كود الكارنيه.</span>
            </div>
            <p class="text-[11px] text-slate-400">إذا لم يكن لديك هاتف خاص، استخدم رقم والدك/والدتك المسجل في السنتر.</p>
        </div>
    </div>

    {{-- التذييل --}}
    <div class="text-center text-xs font-semibold text-brand-muted py-4 relative z-10">
        جميع الحقوق محفوظة © {{ date('Y') }} — المنظومة الأكاديمية الذكية
    </div>

</body>
</html>
