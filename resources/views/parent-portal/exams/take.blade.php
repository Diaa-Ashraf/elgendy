<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>أداء الاختبار — {{ $exam->title }}</title>
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; } 
        h1, h2, h3, h4, .font-heading { font-family: 'Alexandria', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen pb-24 selection:bg-sky-600 selection:text-white"
      x-data="examRunner({
          remainingSeconds: {{ $remainingSeconds ?? 'null' }},
          totalQuestions: {{ $questions->count() }}
      })"
      x-init="initTimer()">

    {{-- رأس الصفحة الثابت مع المؤقت --}}
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50 px-4 py-3 shadow-md">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="space-y-0.5">
                <h1 class="font-heading font-bold text-sm sm:text-base text-white truncate max-w-[200px] sm:max-w-md">{{ $exam->title }}</h1>
                <span class="text-[11px] text-slate-400 font-medium">الطالب: <strong class="text-sky-400">{{ $student->name }}</strong></span>
            </div>

            <div class="flex items-center gap-3">
                @if($remainingSeconds !== null)
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl border font-mono font-bold text-sm"
                         :class="remainingSeconds < 180 ? 'bg-rose-950/90 border-rose-600 text-rose-300 animate-pulse' : 'bg-slate-800 border-slate-700 text-amber-300'">
                        <span class="text-xs text-slate-400 font-sans font-medium">الوقت المتبقي:</span>
                        <span x-text="formatTimer()"></span>
                    </div>
                @endif

                <button type="button" @click="openConfirmModal()" class="px-4 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-sm transition">
                    تسليم الاختبار
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        <form id="examForm" action="{{ route('parent.exams.submit', ['id' => $exam->id]) }}" method="POST" class="space-y-6">
            @csrf

            @foreach($questions as $index => $question)
                <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-5 sm:p-7 shadow-sm space-y-5">
                    {{-- رأس السؤال --}}
                    <div class="flex items-start justify-between gap-3 border-b border-slate-700 pb-4">
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-xl bg-slate-700 text-sky-300 border border-slate-600 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <h3 class="font-heading font-bold text-sm sm:text-base text-white leading-relaxed whitespace-pre-line">{{ $question->question_text }}</h3>
                                @if($question->topic)
                                    <span class="inline-block text-[10px] font-semibold text-slate-400 bg-slate-900 px-2 py-0.5 rounded-md mt-1 border border-slate-700">
                                        الدرس: {{ $question->topic }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <span class="text-xs font-bold text-slate-300 bg-slate-900 px-2.5 py-1 rounded-xl border border-slate-700 shrink-0">
                            {{ $question->pivot->marks ?? $question->default_marks }} درجات
                        </span>
                    </div>

                    {{-- صورة السؤال إن وجدت --}}
                    @if($question->question_image)
                        <div class="rounded-xl overflow-hidden border border-slate-700 max-h-80 bg-slate-900 flex items-center justify-center p-2">
                            <img src="{{ asset('storage/' . $question->question_image) }}" alt="صورة السؤال" class="max-h-72 object-contain rounded-lg">
                        </div>
                    @endif

                    {{-- خيارات الإجابة --}}
                    <div class="space-y-2.5">
                        @php
                            $options = is_array($question->options) ? $question->options : [];
                            $isMultiple = $question->type === 'multiple_choice';
                        @endphp

                        @foreach($options as $opt)
                            <label class="flex items-center gap-3.5 p-3.5 sm:p-4 rounded-xl border border-slate-700 bg-slate-900/90 hover:bg-slate-750 hover:border-slate-600 cursor-pointer transition select-none group">
                                @if($isMultiple)
                                    <input type="checkbox"
                                           name="answers[{{ $question->id }}][]"
                                           value="{{ $opt['key'] }}"
                                           class="w-4 h-4 rounded text-sky-600 bg-slate-800 border-slate-600 focus:ring-sky-500">
                                @else
                                    <input type="radio"
                                           name="answers[{{ $question->id }}]"
                                           value="{{ $opt['key'] }}"
                                           class="w-4 h-4 text-sky-600 bg-slate-800 border-slate-600 focus:ring-sky-500">
                                @endif

                                <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-slate-200 group-hover:text-white">
                                    <span class="px-2 py-0.5 min-w-[28px] text-center rounded-lg bg-slate-800 text-slate-300 font-mono text-xs flex items-center justify-center border border-slate-700">
                                        {{ $opt['key'] === 'true' ? 'صواب' : ($opt['key'] === 'false' ? 'خطأ' : $opt['key']) }}
                                    </span>
                                    <span>{{ $opt['text'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- زر التسليم بالأسفل --}}
            <div class="text-center pt-4">
                <button type="button" @click="openConfirmModal()" class="w-full sm:w-auto px-12 py-3.5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-xl text-sm sm:text-base font-bold shadow-md transition">
                    إنهاء وتسليم الإجابات
                </button>
            </div>
        </form>

    </main>

    {{-- مودال التأكيد --}}
    <div x-cloak x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="showModal" 
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showModal = false"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"></div>

        <div x-show="showModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-slate-900 border border-slate-700 rounded-2xl p-6 sm:p-8 max-w-md w-full relative z-10 shadow-2xl text-center space-y-5">
            
            <div class="space-y-2">
                <h3 class="text-lg font-heading font-bold text-white">تأكيد تسليم الاختبار</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    بمجرد الضغط على تأكيد، سيتم رصد إجاباتك فوراً وتصحيحها أوتوماتيكياً ولن تتمكن من تعديل أي إجابة بعد ذلك.
                </p>
            </div>

            <div class="p-3 bg-slate-800/90 rounded-xl border border-slate-700 text-xs text-sky-300 font-medium text-center">
                ستظهر نتيجة الاختبار والتقرير النموذجي فور التسليم
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition border border-slate-700">
                    مراجعة الإجابات
                </button>
                <button type="button" @click="submitFinal()" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow transition flex items-center justify-center gap-1.5">
                    تأكيد التسليم
                </button>
            </div>
        </div>
    </div>

    <script>
        function examRunner(config) {
            return {
                remainingSeconds: config.remainingSeconds,
                timerInterval: null,
                showModal: false,

                initTimer() {
                    if (this.remainingSeconds === null) return;

                    this.timerInterval = setInterval(() => {
                        if (this.remainingSeconds > 0) {
                            this.remainingSeconds--;
                        } else {
                            clearInterval(this.timerInterval);
                            this.submitFinal();
                        }
                    }, 1000);
                },

                formatTimer() {
                    if (this.remainingSeconds === null) return '';
                    let minutes = Math.floor(this.remainingSeconds / 60);
                    let seconds = this.remainingSeconds % 60;
                    return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                },

                openConfirmModal() {
                    this.showModal = true;
                },

                submitFinal() {
                    document.getElementById('examForm').submit();
                }
            };
        }
    </script>

</body>
</html>
