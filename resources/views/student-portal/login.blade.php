<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بوابة الطالب — {{ $currentTenant->name ?? 'المنصة التعليمية' }}</title>
    @if(!empty($currentTenant->favicon))
        <link rel="icon" href="{{ asset('storage/' . $currentTenant->favicon) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Alexandria', sans-serif; }
        .academic-grid-pattern {
            background-image: radial-gradient(rgba(14, 165, 233, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-between p-4 sm:p-6 selection:bg-sky-500 selection:text-white academic-grid-pattern">

    {{-- رأس الصفحة --}}
    <div class="max-w-md mx-auto w-full pt-2 sm:pt-4 flex justify-between items-center relative z-10">
        <a href="{{ route('tenant.home', ['tenant' => $currentTenant->slug]) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-300 hover:text-white transition bg-slate-800/80 backdrop-blur px-3.5 py-2 rounded-xl border border-slate-700/60 shadow-sm">
            <span>←</span>
            <span>الموقع التعريفي</span>
        </a>
        <span class="text-xs text-sky-400 font-semibold flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
            {{ $currentTenant->name }}
        </span>
    </div>

    {{-- كارت تسجيل الدخول --}}
    <div class="w-full max-w-md mx-auto bg-slate-800/90 backdrop-blur-xl border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-2xl relative z-10 my-auto">
        <div class="text-center mb-6 sm:mb-8">
            <div class="w-16 h-16 bg-gradient-to-tr from-sky-600 to-indigo-600 text-white rounded-2xl flex items-center justify-center mx-auto text-3xl mb-4 shadow-lg shadow-sky-500/20 border border-sky-400/30">
                🎓
            </div>
            <h1 class="font-heading font-extrabold text-xl sm:text-2xl text-white mb-2">بوابة الطالب الذكية</h1>
            <p class="text-xs text-slate-400 font-medium leading-relaxed">
                ادخل للامتحانات الأونلاين، تقييم درجاتك، وجدول الحصص والمذكرات
            </p>
        </div>

        @if($errors->any())
            <div class="mb-5 p-3.5 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-xl text-xs font-bold leading-relaxed">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('tenant.student.login.submit', ['tenant' => $currentTenant->slug]) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">كود الطالب الأكاديمي (#ID)</label>
                <input type="number" name="student_code" value="{{ old('student_code') }}" required placeholder="مثال: 104" class="w-full px-4 py-3 bg-slate-900/90 border border-slate-700 rounded-xl text-xs sm:text-sm font-semibold text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition placeholder:text-slate-500" dir="ltr">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">رقم هاتف ولي الأمر المسجل للتحقق</label>
                <input type="tel" name="parent_phone" value="{{ old('parent_phone') }}" required placeholder="01xxxxxxxxx" class="w-full px-4 py-3 bg-slate-900/90 border border-slate-700 rounded-xl text-xs sm:text-sm font-semibold text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition placeholder:text-slate-500" dir="ltr">
            </div>

            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white rounded-xl font-bold text-xs sm:text-sm transition-all duration-200 shadow-lg shadow-sky-500/25 flex items-center justify-center gap-2 mt-2">
                <span>دخول الحساب</span>
                <span>➔</span>
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-700/60 text-center text-[11px] text-slate-400 leading-normal">
            💡 تجد كودك الأكاديمي مطبوعاً على كارنيه الحضور أو اطلبه من المساعدين.
        </div>
    </div>

    {{-- التذييل --}}
    <div class="text-center text-xs font-medium text-slate-500 py-3 relative z-10">
        {{ $currentTenant->name }} © {{ date('Y') }} — جميع الحقوق محفوظة
    </div>

</body>
</html>
