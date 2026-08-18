<x-filament-panels::page>
    <div class="space-y-6">

        {{-- 1. بطاقات الإحصائيات العامة --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-900/80 dark:bg-gray-900 border border-gray-800 rounded-2xl p-5 shadow-lg backdrop-blur-sm relative overflow-hidden">
                <div class="absolute -right-2 -bottom-2 opacity-10 text-primary-500 text-6xl select-none">👥</div>
                <div class="text-xs font-bold text-gray-400 mb-1">إجمالي الطلاب المتقدمين</div>
                <div class="text-3xl font-black text-amber-400 flex items-baseline gap-1">
                    {{ $analytics['total_attempts'] ?? 0 }}
                    <span class="text-xs font-medium text-gray-400">طالب</span>
                </div>
            </div>

            <div class="bg-gray-900/80 dark:bg-gray-900 border border-gray-800 rounded-2xl p-5 shadow-lg backdrop-blur-sm relative overflow-hidden">
                <div class="absolute -right-2 -bottom-2 opacity-10 text-amber-500 text-6xl select-none">📊</div>
                <div class="text-xs font-bold text-gray-400 mb-1">متوسط درجات المجموعة</div>
                <div class="text-3xl font-black text-amber-400">{{ $analytics['average_score'] ?? 0 }}%</div>
            </div>

            <div class="bg-gray-900/80 dark:bg-gray-900 border border-gray-800 rounded-2xl p-5 shadow-lg backdrop-blur-sm relative overflow-hidden">
                <div class="absolute -right-2 -bottom-2 opacity-10 text-emerald-500 text-6xl select-none">🏆</div>
                <div class="text-xs font-bold text-gray-400 mb-1">نسبة النجاح العامة</div>
                <div class="text-3xl font-black text-emerald-400">{{ $analytics['pass_rate'] ?? 0 }}%</div>
            </div>

            <div class="bg-gray-900/80 dark:bg-gray-900 border border-gray-800 rounded-2xl p-5 shadow-lg backdrop-blur-sm relative overflow-hidden">
                <div class="absolute -right-2 -bottom-2 opacity-10 text-cyan-500 text-6xl select-none">❓</div>
                <div class="text-xs font-bold text-gray-400 mb-1">عدد أسئلة الاختبار</div>
                <div class="text-3xl font-black text-cyan-400 flex items-baseline gap-1">
                    {{ $record->questions->count() }}
                    <span class="text-xs font-medium text-gray-400">سؤال</span>
                </div>
            </div>
        </div>

        @if(($analytics['total_attempts'] ?? 0) === 0)
            <div class="bg-amber-950/20 border border-amber-800/40 p-8 rounded-2xl text-center backdrop-blur-sm">
                <span class="text-4xl mb-3 block">⏳</span>
                <h3 class="font-black text-lg text-amber-300">لم يقم أي طالب بأداء هذا الاختبار حتى الآن</h3>
                <p class="text-sm text-gray-300 mt-2 max-w-md mx-auto">بمجرد أن يبدأ الطلاب في حل الامتحان وتسليمه أونلاين، سيتم تلقائياً تجميع وتحليل نقاط الضعف ورصد الأخطاء هنا فوراً.</p>
            </div>
        @else

            {{-- 2. مصفوفة تحليل المفاهيم / الدروس الأضعف (Weak Topics) --}}
            <div class="bg-gray-900/90 border border-gray-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                    <h3 class="font-black text-base text-white flex items-center gap-2">
                        <span class="text-lg">🎯</span> تشخيص استيعاب الدروس والمفاهيم (Topics Mastery)
                    </h3>
                    <span class="text-xs text-gray-400">مقياس إتقان موضوعات الاختبار</span>
                </div>

                <div class="space-y-3">
                    @foreach($analytics['topics_analysis'] as $topic)
                        <div class="p-4 bg-gray-950/70 rounded-xl border border-gray-800/80 hover:border-gray-700 transition">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-3">
                                <div>
                                    <span class="font-bold text-sm text-white">{{ $topic['topic'] }}</span>
                                    <span class="text-xs text-gray-400 mr-2">({{ $topic['total_errors'] }} إجابة خاطئة من أصل {{ $topic['total_questions_answered'] }})</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-gray-400">نسبة الإتقان:</span>
                                    <span class="text-xs font-black px-3 py-1 rounded-full {{ $topic['mastery_rate'] >= 70 ? 'bg-emerald-950/80 text-emerald-400 border border-emerald-800/60' : ($topic['mastery_rate'] >= 50 ? 'bg-amber-950/80 text-amber-400 border border-amber-800/60' : 'bg-rose-950/80 text-rose-400 border border-rose-800/60') }}">
                                        {{ $topic['mastery_rate'] }}%
                                    </span>
                                </div>
                            </div>

                            {{-- شريط التقدم --}}
                            <div class="w-full bg-gray-800 h-2.5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 {{ $topic['mastery_rate'] >= 70 ? 'bg-emerald-500' : ($topic['mastery_rate'] >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                     style="width: {{ $topic['mastery_rate'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 3. أكثر الأسئلة التي أخطأ فيها الطلاب (Top Missed Questions) --}}
            <div class="bg-gray-900/90 border border-gray-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                    <h3 class="font-black text-base text-white flex items-center gap-2">
                        <span class="text-rose-400 text-lg">⚠️</span> الأسئلة ذات أعلى معدل خطأ (نقاط الضعف الحرجة)
                    </h3>
                    <span class="text-xs text-gray-400">مرتبة من الأكثر صعوبة على الطلاب</span>
                </div>

                <div class="space-y-3">
                    @foreach($analytics['questions_analysis'] as $idx => $q)
                        <div class="p-4 rounded-xl border {{ $q['error_rate'] >= 50 ? 'bg-rose-950/20 border-rose-900/50' : 'bg-gray-950/70 border-gray-800/80' }} flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-2 flex-1">
                                <div class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-gray-800 text-xs font-black flex items-center justify-center text-amber-400 shrink-0 border border-gray-700 mt-0.5">
                                        #{{ $idx + 1 }}
                                    </span>
                                    <div class="font-bold text-sm text-white leading-relaxed">
                                        {{ $q['text'] }}
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-400 mr-10">
                                    <span>الدرس: <strong class="text-amber-400">{{ $q['topic'] }}</strong></span>
                                    <span>•</span>
                                    <span>أجاب خطأ: <strong class="text-rose-400">{{ $q['wrong_answers'] }} طالب</strong></span>
                                    <span>•</span>
                                    <span>أجاب صح: <strong class="text-emerald-400">{{ $q['correct_answers'] }} طالب</strong></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 self-end md:self-center shrink-0">
                                <div class="text-left bg-gray-900 px-3 py-1.5 rounded-lg border border-gray-800">
                                    <div class="text-[11px] font-medium text-gray-400">معدل الخطأ</div>
                                    <div class="text-sm font-black {{ $q['error_rate'] >= 50 ? 'text-rose-400' : 'text-emerald-400' }}">
                                        {{ $q['error_rate'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        @endif

    </div>
</x-filament-panels::page>
