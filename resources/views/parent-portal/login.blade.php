<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بوابة ولي الأمر — تسجيل الدخول</title>
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Alexandria', sans-serif; }
        .academic-grid-pattern {
            background-image: radial-gradient(rgba(15, 39, 68, 0.07) 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col justify-between p-4 sm:p-6 selection:bg-sky-600 selection:text-white academic-grid-pattern">

    {{-- زر العودة للرئيسية --}}
    <div class="max-w-md mx-auto w-full pt-2 sm:pt-4 flex justify-between items-center relative z-10">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 transition bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-sm">
            <span>←</span>
            <span>العودة للموقع الرئيسي</span>
        </a>
        <span class="text-xs text-slate-500 font-semibold">بوابة المتابعة الأكاديمية</span>
    </div>

    {{-- كارت تسجيل الدخول --}}
    <div class="w-full max-w-md mx-auto bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm relative z-10 my-auto">
        <div class="text-center mb-6 sm:mb-8">
            <h1 class="font-heading font-extrabold text-xl sm:text-2xl text-slate-900 mb-1.5">بوابة متابعة ولي الأمر</h1>
            <p class="text-xs text-slate-500 font-medium leading-relaxed">
                متابعة الحضور، تقارير الامتحانات الدورية، وسداد الرسوم الأكاديمية
            </p>
        </div>

        @if($errors->any())
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-bold leading-relaxed">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('parent.login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم هاتف ولي الأمر المسجل</label>
                <input type="tel" name="parent_phone" required placeholder="01xxxxxxxxx" class="w-full px-3.5 py-3 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-900 focus:outline-none focus:border-sky-600 focus:bg-white transition" dir="ltr">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">كود الطالب الأكاديمي (#ID)</label>
                <input type="number" name="student_id" required placeholder="مثال: 104" class="w-full px-3.5 py-3 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-900 focus:outline-none focus:border-sky-600 focus:bg-white transition" dir="ltr">
            </div>

            <button type="submit" class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs sm:text-sm transition shadow-sm flex items-center justify-center gap-2 mt-2">
                <span>دخول البوابة</span>
                <span>➔</span>
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center text-[11px] text-slate-500 leading-normal">
            تجد كود الطالب مطبوعاً على كارنيه الطالب أو من خلال مسؤولي السنتر.
        </div>
    </div>

    {{-- التذييل --}}
    <div class="text-center text-xs font-medium text-slate-500 py-3 relative z-10">
        جميع الحقوق محفوظة © {{ date('Y') }} — منظومة التعليم والمتابعة
    </div>

</body>
</html>
