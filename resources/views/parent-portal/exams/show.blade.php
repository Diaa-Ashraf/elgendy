<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تفاصيل الاختبار — {{ $exam->title }}</title>
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen pb-16">

    <header class="bg-slate-900/90 border-b border-slate-800 backdrop-blur-md sticky top-0 z-50 px-4 py-4">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('parent.dashboard') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1">
                    <span>←</span> رجوع للرئيسية
                </a>
            </div>
            <div class="text-xs font-bold text-slate-400">
                الطالب: <span class="text-white font-extrabold">{{ $student->name }}</span>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto p-4 sm:p-6 space-y-6 mt-4">

        @if(session('error'))
            <div class="bg-rose-950 border border-rose-800 p-4 rounded-2xl text-rose-200 text-xs font-bold">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden text-center space-y-6">
            <div class="w-16 h-16 mx-auto bg-gradient-to-tr from-indigo-600 to-violet-600 text-white rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-indigo-950">
                📝
            </div>

            <div>
                <span class="inline-block px-3 py-1 bg-indigo-950 border border-indigo-800 text-indigo-400 rounded-full text-xs font-bold mb-2">
                    {{ $exam->subject?->name }} • {{ $exam->educationalStage?->name }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-white">{{ $exam->title }}</h1>
            </div>

            {{-- تعليمات وتفاصيل الامتحان --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-right">
                <div class="bg-slate-950/80 border border-slate-800/80 p-3.5 rounded-2xl">
                    <span class="text-[11px] font-bold text-slate-400 block mb-1">⏱️ مدة الامتحان:</span>
                    <span class="text-sm font-black text-white">{{ $exam->duration_minutes ? $exam->duration_minutes . ' دقيقة' : 'مفتوح دون وقت' }}</span>
                </div>

                <div class="bg-slate-950/80 border border-slate-800/80 p-3.5 rounded-2xl">
                    <span class="text-[11px] font-bold text-slate-400 block mb-1">❓ عدد الأسئلة:</span>
                    <span class="text-sm font-black text-white">{{ $exam->questions->count() }} أسئلة</span>
                </div>

                <div class="bg-slate-950/80 border border-slate-800/80 p-3.5 rounded-2xl col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-bold text-slate-400 block mb-1">🎯 نسبة النجاح:</span>
                    <span class="text-sm font-black text-emerald-400">{{ $exam->pass_percentage }}% فأكثر</span>
                </div>
            </div>

            <div class="bg-slate-950 border border-slate-800/60 p-4 rounded-2xl text-right text-xs space-y-2 text-slate-300">
                <h4 class="font-bold text-white text-sm">📌 تعليمات هامة قبل البدء:</h4>
                <ul class="list-disc list-inside space-y-1 text-[12px] text-slate-400 leading-relaxed">
                    <li>بمجرد الضغط على "بدء الاختبار الآن"، سيبدأ المؤقت التنازلي للوقت ولن يتوقف.</li>
                    <li>عند انتهاء الوقت المحدد، سيتم تسليم إجاباتك أوتوماتيكياً واعتمادها فوراً.</li>
                    <li>ستظهر لك نتيجتك ونقاط القوة والضعف والشرح النموذجي فور الانتهاء مباشرة.</li>
                </ul>
            </div>

            {{-- حالة المحاولة وزر البدء --}}
            <div class="pt-2">
                @if($attempt && $attempt->status === 'completed')
                    <div class="space-y-3">
                        <div class="p-4 bg-emerald-950/80 border border-emerald-800 rounded-2xl text-emerald-300 text-sm font-bold">
                            لقد قمت بأداء هذا الاختبار بالفعل محققاً نتيجة ({{ $attempt->total_score }} / {{ $attempt->max_possible_score }}) بنسبة {{ $attempt->percentage }}% ✅
                        </div>
                        @php
                            $resultRoute = isset($tenant)
                                ? route('tenant.student.exams.result', ['tenant' => $tenant, 'id' => $exam->id])
                                : (Route::has('parent.exams.result') ? route('parent.exams.result', ['id' => $exam->id]) : '#');
                        @endphp
                        <a href="{{ $resultRoute }}" class="inline-block px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-2xl text-sm font-black shadow-xl shadow-indigo-950 transition">
                            عرض بطاقة النتيجة والشرح النموذجي ↗
                        </a>
                    </div>
                @elseif(! $isAvailable)
                    <div class="p-4 bg-amber-950/80 border border-amber-800 rounded-2xl text-amber-300 text-xs font-bold leading-relaxed">
                        ⚠️ {{ $availabilityMessage }}
                    </div>
                @else
                    @php
                        $startRoute = isset($tenant)
                            ? route('tenant.student.exams.start', ['tenant' => $tenant, 'id' => $exam->id])
                            : (Route::has('parent.exams.start') ? route('parent.exams.start', ['id' => $exam->id]) : '#');
                    @endphp
                    <a href="{{ $startRoute }}" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-10 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-2xl text-base font-black shadow-xl shadow-emerald-950 transition transform hover:-translate-y-0.5">
                        <span>ابدأ الاختبار الآن</span> 🚀
                    </a>
                @endif
            </div>
        </div>

    </main>

</body>
</html>
