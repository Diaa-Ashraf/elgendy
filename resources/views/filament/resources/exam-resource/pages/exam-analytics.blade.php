<x-filament-panels::page>
    <div class="space-y-6">

        {{-- 1. بطاقات الإحصائيات العامة --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                <div class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">إجمالي الطلاب المتقدمين</div>
                <div class="text-3xl font-black text-primary-600 dark:text-primary-400">{{ $analytics['total_attempts'] ?? 0 }} <span class="text-xs font-normal text-gray-400">طالب</span></div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                <div class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">متوسط درجات المجموعة</div>
                <div class="text-3xl font-black text-amber-500">{{ $analytics['average_score'] ?? 0 }}%</div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                <div class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">نسبة النجاح العامة</div>
                <div class="text-3xl font-black text-emerald-500">{{ $analytics['pass_rate'] ?? 0 }}%</div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                <div class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">عدد أسئلة الاختبار</div>
                <div class="text-3xl font-black text-indigo-500">{{ $record->questions->count() }} <span class="text-xs font-normal text-gray-400">سؤال</span></div>
            </div>
        </div>

        @if(($analytics['total_attempts'] ?? 0) === 0)
            <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 p-6 rounded-2xl text-center">
                <span class="text-3xl mb-2 block">⏳</span>
                <h3 class="font-black text-base text-amber-800 dark:text-amber-300">لم يقم أي طالب بأداء هذا الاختبار حتى الآن</h3>
                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">بمجرد أن يبدأ الطلاب في حل الامتحان وتسليمه أونلاين، سيتم تلقائياً تجميع وتحليل نقاط الضعف ورصد الأخطاء هنا فوراً.</p>
            </div>
        @else

            {{-- 2. مصفوفة تحليل المفاهيم / الدروس الأضعف (Weak Topics) --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                <h3 class="font-black text-base text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span>🎯</span> تشخيص استيعاب الدروس والمفاهيم (Topics Mastery)
                </h3>

                <div class="space-y-4">
                    @foreach($analytics['topics_analysis'] as $topic)
                        <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200/80 dark:border-gray-700/50">
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <span class="font-extrabold text-sm text-gray-900 dark:text-white">{{ $topic['topic'] }}</span>
                                    <span class="text-xs text-gray-500 mr-2">({{ $topic['total_errors'] }} إجابة خاطئة من أصل {{ $topic['total_questions_answered'] }})</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-gray-500">نسبة الإتقان:</span>
                                    <span class="text-xs font-black px-2.5 py-0.5 rounded-full {{ $topic['mastery_rate'] >= 70 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : ($topic['mastery_rate'] >= 50 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300') }}">
                                        {{ $topic['mastery_rate'] }}%
                                    </span>
                                </div>
                            </div>
                            {{-- شريط التقدم --}}
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 {{ $topic['mastery_rate'] >= 70 ? 'bg-emerald-500' : ($topic['mastery_rate'] >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                     style="width: {{ $topic['mastery_rate'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 3. أكثر الأسئلة التي أخطأ فيها الطلاب (Top Missed Questions) --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4 border-b border-gray-200 dark:border-gray-800 pb-3">
                    <h3 class="font-black text-base text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-rose-500">⚠️</span> الأسئلة ذات أعلى معدل خطأ (نقاط الضعف الحرجة)
                    </h3>
                    <span class="text-xs text-gray-500">مرتبة من الأكثر صعوبة على الطلاب</span>
                </div>

                <div class="space-y-3">
                    @foreach($analytics['questions_analysis'] as $idx => $q)
                        <div class="p-4 rounded-xl border {{ $q['error_rate'] >= 50 ? 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/50' : 'bg-gray-50 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700/60' }} flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-xs font-black flex items-center justify-center text-gray-700 dark:text-gray-300">
                                        #{{ $idx + 1 }}
                                    </span>
                                    <span class="font-bold text-sm text-gray-900 dark:text-white">{{ $q['text'] }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mr-8">
                                    <span>الدرس: <strong class="text-primary-600 dark:text-primary-400">{{ $q['topic'] }}</strong></span>
                                    <span>•</span>
                                    <span>أجاب خطأ: <strong class="text-rose-600 dark:text-rose-400">{{ $q['wrong_answers'] }} طالب</strong></span>
                                    <span>•</span>
                                    <span>أجاب صح: <strong class="text-emerald-600 dark:text-emerald-400">{{ $q['correct_answers'] }} طالب</strong></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 self-end md:self-center">
                                <div class="text-left">
                                    <div class="text-xs font-bold text-gray-500 dark:text-gray-400">معدل الخطأ</div>
                                    <div class="text-base font-black {{ $q['error_rate'] >= 50 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-500' }}">
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
