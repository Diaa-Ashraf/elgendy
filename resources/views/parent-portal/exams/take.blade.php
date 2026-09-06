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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; } 
        h1, h2, h3, h4, .font-heading { font-family: 'Alexandria', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-brand-bg text-brand-slate min-h-screen pb-24 selection:bg-brand-coral selection:text-white"
      x-data="examRunner({
          remainingSeconds: {{ $remainingSeconds ?? 'null' }},
          totalQuestions: {{ $questions->count() }}
      })"
      x-init="initTimer()">

    {{-- رأس الصفحة الثابت مع المؤقت --}}
    <header class="bg-brand-teal border-b border-brand-teal-dark/50 sticky top-0 z-50 px-4 py-3 shadow-md shadow-brand-teal/10">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="space-y-0.5">
                <h1 class="font-heading font-black text-sm sm:text-base text-white truncate max-w-[200px] sm:max-w-md">{{ $exam->title }}</h1>
                <span class="text-[11px] text-slate-300 font-medium">الطالب: <strong class="text-brand-coral">{{ $student->name }}</strong></span>
            </div>

            <div class="flex items-center gap-3">
                @if($remainingSeconds !== null)
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl border font-mono font-bold text-sm"
                         :class="remainingSeconds < 180 ? 'bg-rose-500/20 border-rose-400 text-rose-200 animate-pulse' : 'bg-white/10 border-white/20 text-amber-300'">
                        <span class="text-xs text-slate-300 font-sans font-medium">الوقت المتبقي:</span>
                        <span x-text="formatTimer()"></span>
                    </div>
                @endif

                <button type="button" @click="openConfirmModal()" class="px-4 py-2 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white rounded-xl text-xs font-bold shadow-md shadow-brand-coral/25 transition">
                    تسليم الاختبار
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        <form id="examForm" action="{{ route('parent.exams.submit', ['id' => $exam->id]) }}" method="POST" class="space-y-6">
            @csrf

            @foreach($questions as $index => $question)
                <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-sm space-y-5">
                    {{-- رأس السؤال --}}
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                        <div class="flex items-start gap-3">
                            <span class="w-9 h-9 rounded-2xl bg-brand-teal/10 text-brand-teal border border-brand-teal/20 flex items-center justify-center font-black text-sm shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <h3 class="font-heading font-black text-sm sm:text-base text-brand-slate leading-relaxed whitespace-pre-line">{{ $question->question_text }}</h3>
                                @if($question->topic)
                                    <span class="inline-block text-[10px] font-bold text-brand-teal bg-brand-teal/5 px-2.5 py-0.5 rounded-md mt-1.5 border border-brand-teal/10">
                                        الدرس: {{ $question->topic }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <span class="text-xs font-bold text-brand-slate bg-slate-100 px-3 py-1 rounded-xl border border-slate-200 shrink-0">
                            {{ $question->pivot->marks ?? $question->default_marks }} درجات
                        </span>
                    </div>

                    {{-- صورة السؤال إن وجدت --}}
                    @if($question->question_image)
                        <div class="rounded-2xl overflow-hidden border border-slate-200 max-h-80 bg-slate-50 flex items-center justify-center p-2">
                            <img src="{{ asset('storage/' . $question->question_image) }}" alt="صورة السؤال" class="max-h-72 object-contain rounded-xl">
                        </div>
                    @endif

                    {{-- خيارات الإجابة --}}
                    <div class="space-y-2.5">
                        @php
                            $options = is_array($question->options) ? $question->options : [];
                            $isMultiple = $question->type === 'multiple_choice';
                        @endphp

                        @foreach($options as $opt)
                            <label class="flex items-center gap-3.5 p-4 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-white hover:border-brand-teal/40 cursor-pointer transition select-none group shadow-sm">
                                @if($isMultiple)
                                    <input type="checkbox"
                                           name="answers[{{ $question->id }}][]"
                                           value="{{ $opt['key'] }}"
                                           class="w-4 h-4 rounded text-brand-coral bg-white border-slate-300 focus:ring-brand-coral">
                                @else
                                    <input type="radio"
                                           name="answers[{{ $question->id }}]"
                                           value="{{ $opt['key'] }}"
                                           class="w-4 h-4 text-brand-coral bg-white border-slate-300 focus:ring-brand-coral">
                                @endif

                                <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-brand-slate">
                                    <span class="px-2 py-0.5 min-w-[28px] text-center rounded-lg bg-white text-brand-teal font-mono text-xs font-black flex items-center justify-center border border-slate-200 shadow-sm">
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
                <button type="button" @click="openConfirmModal()" class="w-full sm:w-auto px-12 py-4 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white rounded-2xl font-heading font-bold text-sm sm:text-base shadow-lg shadow-brand-coral/30 transition">
                    إنهاء وتسليم الإجابات ➔
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
             class="fixed inset-0 bg-brand-slate/60 backdrop-blur-sm"></div>

        <div x-show="showModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-md w-full relative z-10 shadow-2xl text-center space-y-5">
            
            <div class="space-y-2">
                <h3 class="text-lg font-heading font-black text-brand-slate">تأكيد تسليم الاختبار</h3>
                <p class="text-xs text-brand-muted leading-relaxed">
                    بمجرد الضغط على تأكيد، سيتم رصد إجاباتك فوراً وتصحيحها أوتوماتيكياً ولن تتمكن من تعديل أي إجابة بعد ذلك.
                </p>
            </div>

            <div class="p-3 bg-brand-teal/5 rounded-2xl border border-brand-teal/15 text-xs text-brand-teal font-bold text-center">
                ستظهر نتيجة الاختبار والتقرير النموذجي فور التسليم
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" @click="showModal = false" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-brand-slate rounded-2xl text-xs font-bold transition border border-slate-200">
                    مراجعة الإجابات
                </button>
                <button type="button" @click="submitFinal()" class="px-5 py-3 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white rounded-2xl text-xs font-bold shadow-md shadow-brand-coral/25 transition flex items-center justify-center gap-1.5">
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
