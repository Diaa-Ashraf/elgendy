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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; } 
        h1, h2, h3, h4, .font-heading { font-family: 'Alexandria', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen pb-16 selection:bg-sky-600 selection:text-white">

    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50 px-4 py-3.5 shadow-sm">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('parent.dashboard') }}" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <span>←</span> رجوع للرئيسية
                </a>
            </div>
            <div class="text-xs font-semibold text-slate-400">
                الطالب: <span class="text-white font-bold">{{ $student->name }}</span>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto p-4 sm:p-6 space-y-6 mt-4">

        @if(session('error'))
            <div class="bg-rose-950/90 border border-rose-800 p-4 rounded-2xl text-rose-200 text-xs font-bold">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-6 sm:p-8 shadow-sm text-center space-y-6">
            <div>
                <span class="inline-block px-3 py-1 bg-slate-900/80 border border-slate-700 text-sky-300 rounded-full text-xs font-bold mb-2">
                    {{ $exam->subject?->name }} • {{ $exam->educationalStage?->name }}
                </span>
                <h1 class="text-xl sm:text-2xl font-heading font-bold text-white">{{ $exam->title }}</h1>
            </div>

            {{-- تفاصيل الامتحان --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-right">
                <div class="bg-slate-900/90 border border-slate-700 p-3.5 rounded-xl">
                    <span class="text-[11px] font-semibold text-slate-400 block mb-1">مدة الامتحان:</span>
                    <span class="text-sm font-bold text-white">{{ $exam->duration_minutes ? $exam->duration_minutes . ' دقيقة' : 'مفتوح دون وقت' }}</span>
                </div>

                <div class="bg-slate-900/90 border border-slate-700 p-3.5 rounded-xl">
                    <span class="text-[11px] font-semibold text-slate-400 block mb-1">عدد الأسئلة:</span>
                    <span class="text-sm font-bold text-white">{{ $exam->questions->count() }} أسئلة</span>
                </div>

                <div class="bg-slate-900/90 border border-slate-700 p-3.5 rounded-xl col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-semibold text-slate-400 block mb-1">نسبة النجاح:</span>
                    <span class="text-sm font-bold text-emerald-400">{{ $exam->pass_percentage }}% فأكثر</span>
                </div>
            </div>

            <div class="bg-slate-900/90 border border-slate-700 p-4 rounded-xl text-right text-xs space-y-2 text-slate-300">
                <h4 class="font-heading font-bold text-white text-sm">تعليمات هامة قبل البدء:</h4>
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
                        <div class="p-4 bg-emerald-950/80 border border-emerald-800 rounded-xl text-emerald-300 text-xs sm:text-sm font-bold">
                            تم أداء هذا الاختبار بنتيجة ({{ $attempt->total_score }} / {{ $attempt->max_possible_score }}) بنسبة {{ $attempt->percentage }}%
                        </div>
                        <a href="{{ route('parent.exams.result', ['id' => $exam->id]) }}" class="inline-block px-7 py-3 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-200 rounded-xl text-xs sm:text-sm font-bold shadow-sm transition">
                            عرض بطاقة النتيجة والشرح النموذجي ➔
                        </a>
                    </div>
                @elseif(! $isAvailable)
                    <div class="p-4 bg-amber-950/80 border border-amber-800 rounded-xl text-amber-300 text-xs font-bold leading-relaxed">
                        {{ $availabilityMessage }}
                    </div>
                @else
                    <a href="{{ route('parent.exams.start', ['id' => $exam->id]) }}" class="inline-block w-full sm:w-auto px-10 py-3.5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-xl text-sm font-bold shadow-sm transition">
                        بدء الاختبار الآن
                    </a>
                @endif
            </div>
        </div>

    </main>

</body>
</html>
