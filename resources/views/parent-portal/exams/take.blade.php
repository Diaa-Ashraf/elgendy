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
          remainingSeconds: {{ $remainingSeconds !== null ? (int) $remainingSeconds : 'null' }},
          totalQuestions: {{ $questions->count() }}
      })"
      x-init="initTimer()">

    {{-- رأس الصفحة الثابت مع المؤقت --}}
    <header class="bg-brand-teal border-b border-brand-teal-dark/50 sticky top-0 z-50 px-4 py-3 shadow-md shadow-brand-teal/10 backdrop-blur-md bg-opacity-95">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                    <h1 class="font-heading font-black text-sm sm:text-base text-white truncate max-w-[180px] sm:max-w-md">{{ $exam->title }}</h1>
                    @if(!empty($attempt->exam_model))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-black bg-white/15 text-amber-300 border border-white/20 shadow-sm shrink-0">
                            النموذج: ({{ $attempt->exam_model }})
                        </span>
                    @endif
                </div>
                <span class="text-[11px] text-slate-300 font-medium">الطالب: <strong class="text-brand-coral">{{ $student->name }}</strong></span>
            </div>

            <div class="flex items-center gap-3">
                @if($remainingSeconds !== null)
                    <div class="flex items-center gap-2 px-3 sm:px-4 py-1.5 rounded-xl border font-mono font-bold text-xs sm:text-sm transition-all"
                         :class="remainingSeconds < 180 ? 'bg-rose-500/25 border-rose-400 text-rose-200 animate-pulse' : 'bg-white/10 border-white/20 text-amber-300'">
                        <span class="text-[11px] sm:text-xs text-slate-300 font-sans font-medium">الوقت المتبقي:</span>
                        <span x-text="formatTimer()"></span>
                    </div>
                @endif

                <button type="button" @click="openConfirmModal()" class="px-3.5 sm:px-4 py-2 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white rounded-xl text-xs font-bold shadow-md shadow-brand-coral/25 transition flex items-center gap-1">
                    <span>تسليم الاختبار</span>
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">

        <form id="examForm" action="{{ route('parent.exams.submit', ['id' => $exam->id]) }}" method="POST" class="space-y-6">
            @csrf

            @foreach($questions as $index => $question)
                @php
                    $options = is_array($question->options) ? $question->options : [];
                    $correctAnswers = is_array($question->correct_answers) ? $question->correct_answers : [];
                    $correctCount = count($correctAnswers);
                    
                    // تحديد هل السؤال اختيار من متعدد حقيقي (أكثر من إجابة صحيحة)
                    $isMultiple = ($question->type === 'multiple_choice' && $correctCount > 1);
                    $maxAllowed = $isMultiple ? max(2, $correctCount) : 1;
                @endphp

                <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-sm space-y-5 transition"
                     x-data="questionPicker({
                         id: {{ $question->id }},
                         isMultiple: {{ $isMultiple ? 'true' : 'false' }},
                         maxAllowed: {{ $maxAllowed }}
                     })">
                     
                    {{-- رأس السؤال --}}
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                        <div class="flex items-start gap-3">
                            <span class="w-9 h-9 rounded-2xl bg-brand-teal/10 text-brand-teal border border-brand-teal/20 flex items-center justify-center font-black text-sm shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <div class="space-y-1.5">
                                <h3 class="font-heading font-black text-sm sm:text-base text-brand-slate leading-relaxed whitespace-pre-line">{{ $question->question_text }}</h3>
                                
                                <div class="flex flex-wrap items-center gap-2 pt-1">
                                    @if($question->topic)
                                        <span class="inline-block text-[10px] font-bold text-brand-teal bg-brand-teal/5 px-2.5 py-0.5 rounded-md border border-brand-teal/10">
                                            الدرس: {{ $question->topic }}
                                        </span>
                                    @endif

                                    @if($isMultiple)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-md border transition"
                                              :class="selected.length === maxAllowed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-brand-coral/10 text-brand-coral border-brand-coral/20'">
                                            <span>اختر {{ $maxAllowed }} إجابات</span>
                                            <span class="font-mono text-xs">(محدد: <strong x-text="selected.length"></strong>/{{ $maxAllowed }})</span>
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                                            اختيار إجابة واحدة فقط
                                        </span>
                                    @endif
                                </div>
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

                    {{-- تنبيه الخطأ عند محاولة تجاوز الحد المسموح للاختيارات --}}
                    <div x-show="errorMessage" x-cloak x-transition class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span x-text="errorMessage"></span>
                    </div>

                    {{-- خيارات الإجابة التفاعلية --}}
                    <div class="space-y-2.5">
                        @foreach($options as $opt)
                            <div @click="toggleOption('{{ $opt['key'] }}')"
                                 class="flex items-center justify-between p-4 rounded-2xl border cursor-pointer transition select-none group shadow-sm"
                                 :class="isSelected('{{ $opt['key'] }}') ? 'bg-brand-coral/5 border-brand-coral ring-1 ring-brand-coral/30' : 'bg-slate-50/60 border-slate-200 hover:bg-white hover:border-brand-teal/40'">

                                <div class="flex items-center gap-3 text-xs sm:text-sm font-semibold"
                                     :class="isSelected('{{ $opt['key'] }}') ? 'text-brand-coral font-bold' : 'text-brand-slate'">
                                    
                                    {{-- رمز الخيار (A, B, C / صواب / خطأ) --}}
                                    <span class="px-2.5 py-1 min-w-[30px] text-center rounded-xl font-mono text-xs font-black flex items-center justify-center border transition"
                                          :class="isSelected('{{ $opt['key'] }}') ? 'bg-brand-coral text-white border-brand-coral shadow-sm' : 'bg-white text-brand-teal border-slate-200 shadow-sm'">
                                        {{ $opt['key'] === 'true' ? 'صواب' : ($opt['key'] === 'false' ? 'خطأ' : $opt['key']) }}
                                    </span>

                                    <span>{{ $opt['text'] }}</span>
                                </div>

                                {{-- دائرة الاختيار أو المربع --}}
                                <div>
                                    @if($isMultiple)
                                        {{-- مربع للـ Multiple Choice --}}
                                        <div class="w-5 h-5 rounded-lg border flex items-center justify-center transition"
                                             :class="isSelected('{{ $opt['key'] }}') ? 'border-brand-coral bg-brand-coral text-white shadow-sm' : 'border-slate-300 bg-white group-hover:border-slate-400'">
                                            <svg x-show="isSelected('{{ $opt['key'] }}')" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    @else
                                        {{-- دائرة Radio للـ Single Choice --}}
                                        <div class="w-5 h-5 rounded-full border flex items-center justify-center transition"
                                             :class="isSelected('{{ $opt['key'] }}') ? 'border-brand-coral bg-white ring-2 ring-brand-coral/20' : 'border-slate-300 bg-white group-hover:border-slate-400'">
                                            <div class="w-2.5 h-2.5 rounded-full bg-brand-coral" x-show="isSelected('{{ $opt['key'] }}')"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- الحقول المخفية لإرسال الإجابات عبر الـ Form --}}
                    @if($isMultiple)
                        <template x-for="ansKey in selected" :key="ansKey">
                            <input type="hidden" name="answers[{{ $question->id }}][]" :value="ansKey">
                        </template>
                    @else
                        <input type="hidden" name="answers[{{ $question->id }}]" :value="selected.length > 0 ? selected[0] : ''">
                    @endif

                </div>
            @endforeach

            {{-- زر التسليم بالأسفل --}}
            <div class="text-center pt-4">
                <button type="button" @click="openConfirmModal()" class="w-full sm:w-auto px-12 py-4 bg-gradient-to-r from-brand-coral to-[#FF7552] hover:from-brand-coral-hover hover:to-brand-coral text-white rounded-2xl font-heading font-bold text-sm sm:text-base shadow-lg shadow-brand-coral/30 transition transform active:scale-95">
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

    {{-- شاشة قفل وتسليم تلقائي عند انتهاء الوقت أو الضغط على تسليم --}}
    <div x-cloak x-show="isTimeUp || isSubmitting" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-brand-slate/80 backdrop-blur-md">
        <div class="bg-white border border-slate-200 rounded-3xl p-8 max-w-sm w-full text-center space-y-4 shadow-2xl">
            <div class="w-16 h-16 bg-brand-coral/10 text-brand-coral rounded-3xl flex items-center justify-center mx-auto text-3xl">
                ⏳
            </div>
            <h3 class="font-heading font-black text-lg text-brand-slate" x-text="isTimeUp ? 'انتهى وقت الاختبار!' : 'جاري تسليم إجاباتك...'"></h3>
            <p class="text-xs text-slate-500 font-medium leading-relaxed">
                يتم الآن حفظ إجاباتك ورصد درجتك وعرض النتيجة فوراً...
            </p>
            <div class="flex justify-center pt-2">
                <div class="w-8 h-8 border-4 border-brand-coral border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>
    </div>

    <script>
        function examRunner(config) {
            return {
                remainingSeconds: config.remainingSeconds !== null && !isNaN(config.remainingSeconds) ? Math.max(0, Math.floor(Number(config.remainingSeconds))) : null,
                timerInterval: null,
                showModal: false,
                isSubmitting: false,
                isTimeUp: false,

                initTimer() {
                    if (this.remainingSeconds === null) return;

                    this.timerInterval = setInterval(() => {
                        if (this.remainingSeconds > 0) {
                            this.remainingSeconds--;
                            if (this.remainingSeconds <= 0) {
                                clearInterval(this.timerInterval);
                                this.isTimeUp = true;
                                this.submitFinal();
                            }
                        } else {
                            clearInterval(this.timerInterval);
                            this.isTimeUp = true;
                            this.submitFinal();
                        }
                    }, 1000);
                },

                formatTimer() {
                    if (this.remainingSeconds === null || isNaN(this.remainingSeconds)) return '';
                    let totalSecs = Math.max(0, Math.floor(Number(this.remainingSeconds)));
                    let hours = Math.floor(totalSecs / 3600);
                    let minutes = Math.floor((totalSecs % 3600) / 60);
                    let seconds = totalSecs % 60;

                    if (hours > 0) {
                        return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                    }
                    return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                },

                openConfirmModal() {
                    if (this.isSubmitting || this.isTimeUp) return;
                    this.showModal = true;
                },

                submitFinal() {
                    this.showModal = false;
                    this.isSubmitting = true;
                    document.getElementById('examForm').submit();
                }
            };
        }

        function questionPicker(config) {
            return {
                id: config.id,
                isMultiple: config.isMultiple,
                maxAllowed: config.maxAllowed,
                selected: [],
                errorMessage: '',
                errorTimeout: null,

                isSelected(key) {
                    return this.selected.includes(key);
                },

                toggleOption(key) {
                    if (!this.isMultiple) {
                        // سؤال اختيار فردي: تحديد خيار واحد فقط دائماً
                        this.selected = [key];
                        this.errorMessage = '';
                        return;
                    }

                    // سؤال اختيار متعدد (مثلاً إجابتين):
                    if (this.selected.includes(key)) {
                        // إزالة التحديد إن كان محدداً بالفعل
                        this.selected = this.selected.filter(k => k !== key);
                        this.errorMessage = '';
                    } else {
                        // إذا لم يتجاوز الحد الأقصى
                        if (this.selected.length < this.maxAllowed) {
                            this.selected.push(key);
                            this.errorMessage = '';
                        } else {
                            // تم تجاوز الحد الأقصى للمحدد
                            this.showError('يمكنك اختيار ' + this.maxAllowed + ' إجابات فقط لهذا السؤال. قم بإلغاء إجابة سابقة لتتمكن من اختيار أخرى.');
                        }
                    }
                },

                showError(msg) {
                    this.errorMessage = msg;
                    if (this.errorTimeout) clearTimeout(this.errorTimeout);
                    this.errorTimeout = setTimeout(() => {
                        this.errorMessage = '';
                    }, 4000);
                }
            };
        }
    </script>

</body>
</html>
