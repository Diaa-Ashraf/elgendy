<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بوابة ولي الأمر — تسجيل الدخول</title>
    @php
        $favicon = app(\App\Services\SettingService::class)->get('site_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/' . $favicon) }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('storage/' . $favicon) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $favicon) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between p-4 selection:bg-indigo-500 selection:text-white relative overflow-hidden">

    {{-- خلفية جمالية --}}
    <div class="absolute -top-32 right-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-80 h-80 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>

    {{-- زر العودة للرئيسية --}}
    <div class="max-w-md mx-auto w-full pt-4 flex justify-between items-center relative z-10">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-indigo-400 transition">
            ← العودة للموقع الرئيسي
        </a>
    </div>

    {{-- كارت التسجيل --}}
    <div class="w-full max-w-md mx-auto bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl relative z-10 my-auto">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center mx-auto text-3xl font-black text-white mb-4 shadow-lg shadow-indigo-600/30">
                👨‍👩‍👧‍👦
            </div>
            <h1 class="text-2xl font-black text-white mb-1">بوابة متابعة ولي الأمر</h1>
            <p class="text-xs text-slate-400 font-semibold">متابعة دقيقة للحضور والامتحانات والحساب المالي لحظة بلحظة</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-950/80 border border-rose-800 text-rose-300 rounded-2xl text-xs font-bold leading-relaxed">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('parent.login.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-2">رقم هاتف ولي الأمر المسجل بالسنتر</label>
                <input type="tel" name="parent_phone" required placeholder="01xxxxxxxxx" class="w-full px-4 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-2">كود الطالب الخاص (#ID)</label>
                <input type="number" name="student_id" required placeholder="مثال: 12" class="w-full px-4 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition">
            </div>

            <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-2xl font-black text-sm transition shadow-xl shadow-indigo-600/30 transform hover:-translate-y-0.5">
                دخول البوابة الإلكترونية 🚀
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-800/80 text-center text-[11px] font-bold text-slate-500">
            💡 الكود موضح في كارنيه الطالب أو يمكن الاستعلام عنه من إدارة السنتر.
        </div>
    </div>

    {{-- التذييل --}}
    <div class="text-center text-[11px] font-bold text-slate-500 py-4 relative z-10">
        جميع الحقوق محفوظة © {{ date('Y') }}
    </div>

</body>
</html>
