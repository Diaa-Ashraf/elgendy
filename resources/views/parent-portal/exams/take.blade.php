<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>جاري أداء الاختبار — {{ $exam->title }}</title>
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        body { font-family: 'Cairo', sans-serif; } 
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen pb-24"
      x-data="examRunner({
          remainingSeconds: {{ $remainingSeconds ?? 'null' }},
          totalQuestions: {{ $questions->count() }}
      })"
      x-init="initTimer()">

    {{-- رأس الصفحة الثابت مع المؤقت --}}
    <header class="bg-slate-900/95 border-b border-slate-800 backdrop-blur-md sticky top-0 z-50 px-4 py-3 shadow-xl">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="space-y-0.5">
                <h1 class="font-extrabold text-sm sm:text-base text-white truncate max-w-[200px] sm:max-w-md">{{ $exam->title }}</h1>
                <span class="text-[11px] text-slate-400 font-bold">الطالب: <strong class="text-indigo-400">{{ $student->name }}</strong></span>
            </div>

            <div class="flex items-center gap-3">
                @if($remainingSeconds !== null)
                    <div class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border font-mono font-black text-sm"
                         :class="remainingSeconds < 180 ? 'bg-rose-950/90 border-rose-600 text-rose-300 animate-pulse' : 'bg-slate-950 border-slate-700 text-amber-400'">
                        <span>⏱️</span>
                        <span x-text="formatTimer()"></span>
                    </div>
                @endif

                <button type="button" @click="openConfirmModal()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-black shadow-lg shadow-emerald-950 transition flex items-center gap-1">
                    <span>تسليم الاختبار</span> 🚀
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        <form id="examForm" action="{{ route('parent.exams.submit', ['id' => $exam->id]) }}" method="POST" class="space-y-6">
            @csrf

            @foreach($questions as $index => $question)
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-7 shadow-xl space-y-5">
                    {{-- رأس السؤال --}}
                    <div class="flex items-start justify-between gap-3 border-b border-slate-800/80 pb-4">
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center font-black text-sm shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <h3 class="font-extrabold text-base text-white leading-relaxed whitespace-pre-line">{{ $question->question_text }}</h3>
                                @if($question->topic)
                                    <span class="inline-block text-[10px] font-bold text-slate-400 bg-slate-950 px-2 py-0.5 rounded-md mt-1 border border-slate-800">
                                        📌 الدرس: {{ $question->topic }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <span class="text-xs font-black text-slate-400 bg-slate-950 px-2.5 py-1 rounded-xl border border-slate-800 shrink-0">
                            {{ $question->pivot->marks ?? $question->default_marks }} درجات
                        </span>
                    </div>

                    {{-- صورة السؤال إن وجدت --}}
                    @if($question->question_image)
                        <div class="rounded-2xl overflow-hidden border border-slate-800 max-h-80 bg-slate-950 flex items-center justify-center p-2">
                            <img src="{{ asset('storage/' . $question->question_image) }}" alt="سؤال" class="max-h-72 object-contain rounded-xl">
                        </div>
                    @endif

                    {{-- خيارات الإجابة --}}
                    <div class="space-y-2.5">
                        @php
                            $options = is_array($question->options) ? $question->options : [];
                            $isMultiple = $question->type === 'multiple_choice';
                        @endphp

                        @foreach($options as $opt)
                            <label class="flex items-center gap-3.5 p-3.5 sm:p-4 rounded-2xl border border-slate-800 bg-slate-950/70 hover:bg-slate-800/50 hover:border-slate-700 cursor-pointer transition select-none group">
                                @if($isMultiple)
                                    <input type="checkbox"
                                           name="answers[{{ $question->id }}][]"
                                           value="{{ $opt['key'] }}"
                                           class="w-4 h-4 rounded text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500">
                                @else
                                    <input type="radio"
                                           name="answers[{{ $question->id }}]"
                                           value="{{ $opt['key'] }}"
                                           class="w-4 h-4 text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500">
                                @endif

                                <div class="flex items-center gap-2.5 text-sm font-bold text-slate-200 group-hover:text-white">
                                    <span class="px-2 py-0.5 min-w-[28px] text-center rounded-lg bg-slate-900 text-slate-300 font-mono text-xs flex items-center justify-center border border-slate-800">
                                        {{ $opt['key'] === 'true' ? '✔' : ($opt['key'] === 'false' ? '✖' : $opt['key']) }}
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
                <button type="button" @click="openConfirmModal()" class="w-full sm:w-auto px-12 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-2xl text-base font-black shadow-xl shadow-emerald-950 transition transform hover:-translate-y-0.5">
                    ✅ إنهاء وتسليم الإجابات
                </button>
            </div>
        </form>

    </main>

    {{-- مودال التأكيد الاحترافي المطور (Custom Beautiful Confirmation Modal) --}}
    <div x-cloak x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        {{-- خلفية التعتيم مع تأثير البلور --}}
        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showModal = false"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-md"></div>

        {{-- جسم المودال --}}
        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full relative z-10 shadow-2xl text-center space-y-5">
            
            <div class="w-16 h-16 mx-auto bg-gradient-to-tr from-emerald-600 to-teal-500 text-white rounded-2xl flex items-center justify-center text-3xl shadow-xl shadow-emerald-950 animate-bounce">
                🚀
            </div>

            <div class="space-y-2">
                <h3 class="text-xl font-black text-white">هل أنت متأكد من تسليم الامتحان؟</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    بمجرد الضغط على تأكيد، سيتم رصد إجاباتك فوراً وتصحيحها أوتوماتيكياً ولن تتمكن من تعديل أي إجابة بعد ذلك.
                </p>
            </div>

            <div class="p-3 bg-slate-950 rounded-2xl border border-slate-800/80 text-xs text-indigo-300 font-bold flex items-center justify-center gap-2">
                <span>🎯</span> ستظهر نتيجتك ونقاط ضعفك والتفسير فوراً!
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" @click="showModal = false" class="px-5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition">
                    مراجعة الإجابات ↩
                </button>
                <button type="button" @click="submitFinal()" class="px-5 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-black shadow-lg shadow-emerald-950 transition flex items-center justify-center gap-1.5">
                    <span>نعم، تسليم الآن</span> ✔
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
