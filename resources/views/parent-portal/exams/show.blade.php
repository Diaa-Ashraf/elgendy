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
        h1, h2, h3, h4, .font-heading { font-family: 'Alexandria', sans-serif; }
    </style>
</head>
<body class="bg-brand-bg text-brand-slate min-h-screen pb-16 selection:bg-brand-coral selection:text-white">

    <header class="bg-brand-teal border-b border-brand-teal-dark/50 sticky top-0 z-50 px-4 py-3.5 shadow-md shadow-brand-teal/10">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('parent.dashboard') }}" class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-white/15">
                    <span>←</span> رجوع للرئيسية
                </a>
            </div>
            <div class="text-xs font-semibold text-slate-300">
                الطالب: <span class="text-white font-bold">{{ $student->name }}</span>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto p-4 sm:p-6 space-y-6 mt-4">

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl text-rose-800 text-xs font-bold">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-9 shadow-sm text-center space-y-6">
            <div>
                <span class="inline-block px-3.5 py-1 bg-brand-teal/10 border border-brand-teal/20 text-brand-teal rounded-full text-xs font-bold mb-2">
                    {{ $exam->subject?->name }} • {{ $exam->educationalStage?->name }}
                </span>
                <h1 class="text-xl sm:text-2xl font-heading font-black text-brand-slate">{{ $exam->title }}</h1>
            </div>

            {{-- تفاصيل الامتحان --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-right">
                <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl">
                    <span class="text-[11px] font-bold text-slate-500 block mb-1">مدة الامتحان:</span>
                    <span class="text-sm font-black text-brand-slate">{{ $exam->duration_minutes ? $exam->duration_minutes . ' دقيقة' : 'مفتوح دون وقت' }}</span>
                </div>

                <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl">
                    <span class="text-[11px] font-bold text-slate-500 block mb-1">عدد الأسئلة:</span>
                    <span class="text-sm font-black text-brand-slate">{{ $exam->questions->count() }} أسئلة</span>
                </div>

                <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-bold text-slate-500 block mb-1">نسبة النجاح:</span>
                    <span class="text-sm font-black text-emerald-600">{{ $exam->pass_percentage }}% فأكثر</span>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200 p-5 rounded-2xl text-right text-xs space-y-2 text-slate-600">
                <h4 class="font-heading font-black text-brand-slate text-sm">تعليمات هامة قبل البدء:</h4>
                <ul class="list-disc list-inside space-y-1.5 text-[12px] text-slate-500 leading-relaxed font-medium">
                    <li>بمجرد الضغط على "بدء الاختبار الآن"، سيبدأ المؤقت التنازلي للوقت ولن يتوقف.</li>
                    <li>عند انتهاء الوقت المحدد، سيتم تسليم إجاباتك أوتوماتيكياً واعتمادها فوراً.</li>
                    <li>ستظهر لك نتيجتك ونقاط القوة والضعف والشرح النموذجي فور الانتهاء مباشرة.</li>
                </ul>
            </div>

            {{-- حالة المحاولة وزر البدء --}}
            <div class="pt-2">
                @if($attempt && $attempt->status === 'completed')
                    <div class="space-y-3">
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs sm:text-sm font-bold">
                            تم أداء هذا الاختبار بنتيجة ({{ $attempt->total_score }} / {{ $attempt->max_possible_score }}) بنسبة {{ $attempt->percentage }}%
                        </div>
                        <a href="{{ route('parent.exams.result', ['id' => $exam->id]) }}" class="inline-block px-7 py-3.5 bg-brand-teal hover:bg-brand-teal-dark text-white rounded-2xl text-xs sm:text-sm font-bold shadow-md shadow-brand-teal/20 transition">
                            عرض بطاقة النتيجة والشرح النموذجي ➔
                        </a>
                    </div>
                @elseif(! $isAvailable)
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 text-xs font-bold leading-relaxed">
                        عفواً، الاختبار غير متاح حالياً. إما لم يبدأ موعده بعد أو انتهت فترة إتاحته.
                    </div>
                @else
                    <a href="{{ route('parent.exams.start', ['id' => $exam->id]) }}" class="inline-block px-8 py-4 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white rounded-2xl font-heading font-bold text-sm sm:text-base shadow-lg shadow-brand-coral/30 hover:shadow-xl hover:shadow-brand-coral/40 transition transform active:scale-95">
                        بدء الاختبار الآن ➔
                    </a>
                @endif
            </div>
        </div>

    </main>

</body>
</html>
