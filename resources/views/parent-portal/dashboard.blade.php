<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بوابة ولي الأمر — {{ $student->name }}</title>
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
    @endphp
    @if($faviconUrl)
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen pb-16 selection:bg-indigo-500 selection:text-white">

    {{-- الشريط العلوي --}}
    <header class="bg-slate-900/90 border-b border-slate-800/80 backdrop-blur-md sticky top-0 z-50 px-4 py-4">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600/20 text-indigo-400 rounded-xl flex items-center justify-center font-bold text-lg border border-indigo-500/30">
                    👨‍🎓
                </div>
                <div>
                    <h1 class="font-extrabold text-base text-white leading-snug">{{ $student->name }}</h1>
                    <p class="text-[11px] font-bold text-indigo-400">
                        {{ $student->educationalStage?->name ?? 'غير محدد' }} — <span class="font-mono text-slate-400">كود: #{{ $student->id }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition">
                    الموقع الرئيسي 🌐
                </a>
                <a href="{{ route('parent.logout') }}" class="px-3 py-1.5 bg-rose-950/80 border border-rose-800/80 text-rose-300 hover:bg-rose-900 rounded-xl text-xs font-bold transition">
                    خروج 🚪
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        {{-- ─── 1. كروت الإحصائيات الفخمة ─── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl relative overflow-hidden shadow-lg">
                <span class="text-xs font-bold text-slate-400 block mb-1">الرصيد المالي الحالي</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black {{ $ledger['balance'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ number_format(abs($ledger['balance']), 0) }} ج.م
                    </span>
                    <span class="text-xs font-bold text-slate-400">{{ $ledger['balance'] >= 0 ? '(فائض/مقدم)' : '(مستحق الدفع)' }}</span>
                </div>
                <span class="inline-block mt-2 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full {{ $ledger['balance'] >= 0 ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-rose-950 text-rose-300 border border-rose-800' }}">
                    {{ $ledger['balance'] >= 0 ? 'مسدد بالكامل ✅' : 'متبقي مديونية ⚠️' }}
                </span>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                <span class="text-xs font-bold text-slate-400 block mb-1">المجموعات المسجلة</span>
                <span class="text-2xl font-black text-indigo-400 block">
                    {{ $student->groups->count() }} مجموعات
                </span>
                <span class="text-[11px] font-semibold text-slate-500 mt-1 block">دراسة منتظمة</span>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                <span class="text-xs font-bold text-slate-400 block mb-1">نسبة الحضور</span>
                @php
                    $totalSessions = $attendances->count();
                    $presentCount = $attendances->where('status', 'present')->count();
                    $rate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100) : 100;
                @endphp
                <span class="text-2xl font-black text-purple-400 block">
                    {{ $rate }}%
                </span>
                <span class="text-[11px] font-semibold text-slate-500 mt-1 block">مستوى الالتزام بالحضور</span>
            </div>
        </div>

        {{-- ─── 2. كشف الحساب والمدفوعات التفصيلي ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <h2 class="font-black text-base text-white mb-4 flex items-center justify-between border-b border-slate-800 pb-3">
                <span class="flex items-center gap-2">
                    <span>💳</span> كشف الحساب المالي التفصيلي
                </span>
                <span class="text-xs font-semibold text-slate-400">إجمالي المدفوع: {{ number_format($ledger['total_paid'] ?? 0) }} ج.م</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800">
                    <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي المستحقات (الاشتراكات)</span>
                    <span class="text-lg font-black text-amber-400">{{ number_format($ledger['total_dues'] ?? 0) }} ج.م</span>
                </div>
                <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800">
                    <span class="text-xs font-bold text-slate-400 block mb-1">إجمالي المبالغ المسددة</span>
                    <span class="text-lg font-black text-emerald-400">{{ number_format($ledger['total_paid'] ?? 0) }} ج.م</span>
                </div>
            </div>
        </div>

        {{-- ─── 3. سجل الحضور والغياب ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <h2 class="font-black text-base text-white mb-4 flex items-center justify-between border-b border-slate-800 pb-3">
                <span class="flex items-center gap-2">
                    <span>📅</span> سجل الحضور والغياب
                </span>
                <span class="text-xs font-semibold text-slate-400">آخر 15 حصة</span>
            </h2>

            <div class="space-y-2.5">
                @forelse($attendances as $att)
                    <div class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800/80 rounded-2xl text-xs hover:border-slate-700 transition">
                        <div>
                            <span class="font-bold block text-white text-sm mb-0.5">{{ $att->groupSession?->group?->name ?? 'حصة عامة' }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">تاريخ الحصة: {{ \Carbon\Carbon::parse($att->groupSession?->date)->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            @if($att->status === 'present')
                                <span class="px-3 py-1 bg-emerald-950 border border-emerald-800 text-emerald-300 rounded-xl font-black text-xs">حضر ✅</span>
                            @elseif($att->status === 'late')
                                <span class="px-3 py-1 bg-amber-950 border border-amber-800 text-amber-300 rounded-xl font-black text-xs">متأخر ⏰</span>
                            @else
                                <span class="px-3 py-1 bg-rose-950 border border-rose-800 text-rose-300 rounded-xl font-black text-xs">غائب ❌</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-6">لا توجد سجلات حضور مسجلة حالياً</p>
                @endforelse
            </div>
        </div>

        {{-- ─── 4. نتائج الامتحانات والاختبارات ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <h2 class="font-black text-base text-white mb-4 flex items-center justify-between border-b border-slate-800 pb-3">
                <span class="flex items-center gap-2">
                    <span>⭐</span> نتائج الاختبارات والتقييمات
                </span>
            </h2>

            <div class="space-y-2.5">
                @forelse($examResults as $res)
                    <div class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800/80 rounded-2xl text-xs hover:border-slate-700 transition">
                        <div>
                            <span class="font-bold block text-white text-sm mb-0.5">{{ $res->exam?->title ?? 'اختبار' }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">التاريخ: {{ \Carbon\Carbon::parse($res->exam?->date)->format('Y-m-d') }}</span>
                        </div>
                        <div class="text-left bg-indigo-950/80 border border-indigo-800/80 px-3.5 py-1.5 rounded-xl">
                            <span class="font-black text-indigo-300 text-sm">{{ $res->marks_obtained }}</span>
                            <span class="text-[10px] text-slate-400 font-bold">/ {{ $res->exam?->total_marks }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-6">لا توجد نتائج امتحانات مرصودة بعد</p>
                @endforelse
            </div>
        {{-- ─── 5. الملازم والمطبوعات المستلمة ─── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
            <h2 class="font-black text-base text-white mb-4 flex items-center justify-between border-b border-slate-800 pb-3">
                <span class="flex items-center gap-2">
                    <span>📚</span> الملازم والكتب المستلمة
                </span>
            </h2>

            <div class="space-y-2.5">
                @forelse($materials as $mat)
                    <div class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800/80 rounded-2xl text-xs hover:border-slate-700 transition">
                        <div>
                            <span class="font-bold block text-white text-sm mb-0.5">{{ $mat->studyMaterial?->title ?? 'ملزمة' }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">تاريخ الاستلام: {{ \Carbon\Carbon::parse($mat->delivered_at)->format('Y-m-d') }}</span>
                        </div>
                        <div class="text-left">
                            <span class="font-black block text-sm {{ $mat->payment_status === 'paid' ? 'text-emerald-400' : 'text-amber-400' }}">
                                {{ number_format($mat->price) }} ج.م
                            </span>
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full inline-block mt-0.5 {{ $mat->payment_status === 'paid' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                                {{ $mat->payment_status === 'paid' ? 'مسدد ✅' : 'آجل ⚠️' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-6">لم يتم تسليم ملازم بعد</p>
                @endforelse
            </div>
        </div>

    </div>

</body>
</html>
