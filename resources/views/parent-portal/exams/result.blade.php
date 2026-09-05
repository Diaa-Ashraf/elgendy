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
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <a href="{{ route('parent.dashboard') }}" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <span>←</span> العودة للوحة ولي الأمر
            </a>
            <div class="text-xs font-semibold text-slate-400">
                الطالب: <span class="text-white font-bold">{{ $student->name }}</span>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        @if(session('success'))
            <div class="bg-emerald-950/80 border border-emerald-700 p-4 rounded-xl text-emerald-200 text-xs font-bold text-center">
                {{ session('success') }}
            </div>
        @endif

        {{-- 1. بطاقة النتيجة --}}
        <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-6 sm:p-8 shadow-sm text-center space-y-5">
            <div>
                <h1 class="text-xl sm:text-2xl font-heading font-bold text-white">{{ $exam->title }}</h1>
                <p class="text-xs font-medium text-slate-400 mt-1">تاريخ التسليم: {{ $attempt->submitted_at?->format('Y-m-d h:i A') }}</p>
            </div>

            <div class="max-w-sm mx-auto p-5 rounded-xl border {{ $attempt->passed ? 'bg-slate-900/90 border-slate-700' : 'bg-slate-900/90 border-slate-700' }}">
                <div class="text-xs font-semibold text-slate-400 mb-1">الدرجة المحصلة</div>
                <div class="text-3xl font-heading font-bold {{ $attempt->passed ? 'text-emerald-400' : 'text-rose-400' }} tracking-tight">
                    {{ $attempt->total_score }} <span class="text-base font-normal text-slate-400">/ {{ $attempt->max_possible_score }}</span>
                </div>
                <div class="mt-2 text-xs sm:text-sm font-bold {{ $attempt->passed ? 'text-emerald-300' : 'text-rose-300' }}">
                    النسبة المئوية: {{ $attempt->percentage }}% ({{ $attempt->passed ? 'ناجح' : 'دون حد النجاح المطلوب' }})
                </div>
            </div>
        </div>

        {{-- 2. مراجعة الأسئلة والإجابات والشرح النموذجي --}}
        @if($exam->show_correct_answers_after_submission && !empty($attempt->student_answers))
            <div class="space-y-4">
                <h2 class="text-base font-heading font-bold text-white flex items-center gap-2 border-b border-slate-700 pb-3">
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

                    <div class="bg-slate-800/90 border {{ $isCorrect ? 'border-emerald-800/60' : 'border-rose-800/60' }} rounded-2xl p-5 sm:p-6 shadow-sm space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <span class="inline-block px-2.5 py-0.5 rounded-lg text-[10px] font-bold {{ $isCorrect ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-800' : 'bg-rose-950/80 text-rose-300 border border-rose-800' }}">
                                    {{ $isCorrect ? 'إجابة صحيحة (+ ' . $ans['marks_earned'] . ')' : 'إجابة خاطئة (0 / ' . $ans['max_marks'] . ')' }}
                                </span>
                                <h3 class="font-heading font-bold text-sm sm:text-base text-white mt-1 leading-relaxed">{{ $question?->question_text }}</h3>
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

                                    <div class="flex items-center justify-between p-3 rounded-xl border text-xs sm:text-sm font-semibold {{ $isThisCorrect ? 'bg-emerald-950/40 border-emerald-800/80 text-emerald-200' : ($isSelected ? 'bg-rose-950/40 border-rose-800/80 text-rose-200' : 'bg-slate-900/80 border-slate-700 text-slate-300') }}">
                                        <div class="flex items-center gap-2">
                                            <span class="px-1.5 py-0.5 min-w-[22px] text-center rounded-md bg-slate-800 flex items-center justify-center font-mono text-[10px] border border-slate-700">
                                                {{ $opt['key'] === 'true' ? 'صواب' : ($opt['key'] === 'false' ? 'خطأ' : $opt['key']) }}
                                            </span>
                                            <span>{{ $opt['text'] }}</span>
                                        </div>

                                        <div>
                                            @if($isThisCorrect)
                                                <span class="text-emerald-400 text-[11px] font-bold">الإجابة الصحيحة</span>
                                            @elseif($isSelected)
                                                <span class="text-rose-400 text-[11px] font-bold">إجابتك</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- الشرح والتفسير العلمي --}}
                        @if(!empty($ans['explanation']))
                            <div class="bg-slate-900/90 border border-slate-700 p-4 rounded-xl text-xs space-y-1">
                                <span class="font-bold text-sky-300 block">التفسير والشرح النموذجي:</span>
                                <p class="text-slate-300 leading-relaxed">{{ $ans['explanation'] }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

    </main>

</body>
</html>
