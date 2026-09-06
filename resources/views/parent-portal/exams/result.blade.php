<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تقرير نتيجة الاختبار — {{ $exam->title }}</title>
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
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <a href="{{ route('parent.dashboard') }}" class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-white/15">
                <span>←</span> العودة للوحة ولي الأمر
            </a>
            <div class="text-xs font-semibold text-slate-300">
                الطالب: <span class="text-white font-bold">{{ $student->name }}</span>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-emerald-800 text-xs font-bold text-center">
                {{ session('success') }}
            </div>
        @endif

        {{-- 1. بطاقة النتيجة --}}
        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-9 shadow-sm text-center space-y-5">
            <div>
                <h1 class="text-xl sm:text-2xl font-heading font-black text-brand-slate">{{ $exam->title }}</h1>
                <p class="text-xs font-semibold text-brand-muted mt-1">تاريخ التسليم: {{ $attempt->submitted_at?->format('Y-m-d h:i A') }}</p>
            </div>

            <div class="max-w-sm mx-auto p-6 rounded-3xl border {{ $attempt->passed ? 'bg-emerald-50/70 border-emerald-200' : 'bg-rose-50/70 border-rose-200' }}">
                <div class="text-xs font-bold text-slate-500 mb-1">الدرجة المحصلة</div>
                <div class="text-4xl font-heading font-black {{ $attempt->passed ? 'text-emerald-600' : 'text-rose-600' }} tracking-tight">
                    {{ $attempt->total_score }} <span class="text-base font-bold text-slate-400">/ {{ $attempt->max_possible_score }}</span>
                </div>
                <div class="mt-2.5 text-xs sm:text-sm font-bold {{ $attempt->passed ? 'text-emerald-700' : 'text-rose-700' }}">
                    النسبة المئوية: {{ $attempt->percentage }}% ({{ $attempt->passed ? 'ناجح ومتميز' : 'دون حد النجاح المطلوب' }})
                </div>
            </div>
        </div>

        {{-- 2. مراجعة الأسئلة والإجابات والشرح النموذجي --}}
        @if($exam->show_correct_answers_after_submission && !empty($attempt->student_answers))
            <div class="space-y-4">
                <h2 class="text-base font-heading font-black text-brand-slate flex items-center gap-2 border-b border-slate-200 pb-3">
                    التقرير التفصيلي والشرح النموذجي للأسئلة
                </h2>

                @php
                    $answers = (array) $attempt->student_answers;
                @endphp

                @foreach($answers as $qId => $ans)
                    @php
                        $question = $questions->get($qId);
                        $isCorrect = !empty($ans['is_correct']);
                    @endphp

                    <div class="bg-white border {{ $isCorrect ? 'border-emerald-200' : 'border-rose-200' }} rounded-3xl p-5 sm:p-7 shadow-sm space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold {{ $isCorrect ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                    {{ $isCorrect ? 'إجابة صحيحة (+ ' . $ans['marks_earned'] . ')' : 'إجابة خاطئة (0 / ' . $ans['max_marks'] . ')' }}
                                </span>
                                <h3 class="font-heading font-bold text-sm sm:text-base text-brand-slate mt-2 leading-relaxed">{{ $question?->question_text }}</h3>
                            </div>
                        </div>

                        {{-- خيارات السؤال موضح عليها إجابة الطالب والصواب --}}
                        @if($question && is_array($question->options))
                            <div class="space-y-2 pt-1">
                                @foreach($question->options as $opt)
                                    @php
                                        $isSelected = in_array($opt['key'], (array) ($ans['selected'] ?? []));
                                        $isThisCorrect = in_array($opt['key'], (array) ($ans['correct'] ?? []));
                                    @endphp

                                    <div class="flex items-center justify-between p-3.5 rounded-2xl border text-xs sm:text-sm font-semibold {{ $isThisCorrect ? 'bg-emerald-50/80 border-emerald-200 text-emerald-900' : ($isSelected ? 'bg-rose-50/80 border-rose-200 text-rose-900' : 'bg-slate-50/70 border-slate-200 text-brand-slate') }}">
                                        <div class="flex items-center gap-2.5">
                                            <span class="px-2 py-1 min-w-[24px] text-center rounded-lg bg-white flex items-center justify-center font-mono text-xs font-bold border border-slate-200 shadow-sm">
                                                {{ $opt['key'] === 'true' ? 'صواب' : ($opt['key'] === 'false' ? 'خطأ' : $opt['key']) }}
                                            </span>
                                            <span>{{ $opt['text'] }}</span>
                                        </div>

                                        <div>
                                            @if($isThisCorrect)
                                                <span class="text-emerald-700 text-xs font-bold">الإجابة الصحيحة ✓</span>
                                            @elseif($isSelected)
                                                <span class="text-rose-700 text-xs font-bold">إجابتك ✕</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- الشرح والتفسير العلمي --}}
                        @if(!empty($ans['explanation']))
                            <div class="bg-amber-50/80 border border-amber-200 p-4 rounded-2xl text-xs space-y-1">
                                <span class="font-bold text-amber-900 block">💡 التفسير والشرح النموذجي:</span>
                                <p class="text-amber-800 leading-relaxed font-medium">{{ $ans['explanation'] }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

    </main>

</body>
</html>
