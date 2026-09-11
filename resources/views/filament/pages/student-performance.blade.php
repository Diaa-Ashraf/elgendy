<x-filament-panels::page>
    <div class="space-y-6">
        <!-- شريط اختيار الطالب والتحكم باللون الأبيض الناصع والخط الأسود -->
        <div style="background-color: #111827; border: 1px solid #1f2937;" class="p-5 rounded-2xl shadow-xl">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-1/2">
                    <label class="block text-xs font-black text-gray-200 mb-2">🔍 اختر الطالب لعرض السجل والتحليل الشامل:</label>
                    <select wire:model.live="selectedStudentId" style="background-color: #ffffff !important; color: #000000 !important; font-weight: 800 !important; border: 1px solid #d1d5db !important;" class="w-full rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-amber-500 shadow-sm cursor-pointer">
                        @foreach($this->studentsList as $st)
                            <option value="{{ $st->id }}" style="background-color: #ffffff; color: #000000;">{{ $st->name }} (كود: {{ $st->qr_code }})</option>
                        @endforeach
                    </select>
                </div>

                @if($this->analytics)
                    <div class="flex items-center gap-2.5 w-full md:w-auto justify-end pt-2 md:pt-0 flex-wrap">
                        <a href="{{ route('student.monthly-report.pdf', ['record' => $this->analytics['student']->id]) }}" target="_blank" style="background-color: #10b981 !important; color: #ffffff !important;" class="px-4 py-2.5 hover:bg-emerald-600 rounded-xl text-xs font-black shadow-md hover:shadow-lg transition-all duration-200 inline-flex items-center gap-2 hover:scale-105">
                            <span style="color: #ffffff !important; font-weight: 900 !important;">كارت التقرير الشهري (PDF)</span>
                        </a>
                        <a href="{{ route('student.certificate.print', ['record' => $this->analytics['student']->id]) }}" target="_blank" style="background-color: #ffffff !important; color: #000000 !important; border: 1px solid #e5e7eb;" class="px-4 py-2.5 hover:bg-gray-100 rounded-xl text-xs font-black shadow-md hover:shadow-lg transition-all duration-200 inline-flex items-center gap-2 hover:scale-105">
                            <span style="color: #000000 !important; font-weight: 900 !important;">شهادة تقدير</span>
                        </a>
                        <a href="{{ route('student.card.print', ['record' => $this->analytics['student']->id]) }}" target="_blank" style="background-color: #ffffff !important; color: #000000 !important; border: 1px solid #e5e7eb;" class="px-4 py-2.5 hover:bg-gray-100 rounded-xl text-xs font-black shadow-md hover:shadow-lg transition-all duration-200 inline-flex items-center gap-2 hover:scale-105">
                            <span style="color: #000000 !important; font-weight: 900 !important;">طباعة الكارنيه</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- رادار الإنذار المبكر للطلاب المتعثرين (Early Warning Radar) --}}
        @if(!empty($this->atRiskStudents) && count($this->atRiskStudents) > 0)
            <div class="bg-gradient-to-r from-rose-950/60 via-gray-900 to-rose-950/60 border border-rose-600/40 rounded-3xl p-5 shadow-2xl relative overflow-hidden">
                <div class="flex items-center justify-between gap-4 mb-4 border-b border-rose-800/40 pb-3">
                    <div class="flex items-center gap-3">
                        <div>
                            <h3 class="text-base font-black text-white">رادار الإنذار المبكر للطلاب المتعثرين (Early Warning)</h3>
                            <p class="text-xs text-rose-200/80 font-bold">تم رصد {{ count($this->atRiskStudents) }} طلاب بحاجة إلى تدخل فوري لمتابعة الغياب المتكرر أو هبوط الدرجات.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($this->atRiskStudents as $item)
                        <div class="bg-gray-900/90 border {{ $item['severity'] === 'high' ? 'border-rose-500/50' : 'border-amber-500/40' }} rounded-2xl p-3.5 flex flex-col justify-between gap-2 shadow-lg">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-black text-sm text-white">{{ $item['student']->name }}</div>
                                    <div class="text-[11px] text-gray-400 font-mono">كود: {{ $item['student']->qr_code }} • {{ $item['student']->educationalStage?->name ?? '—' }}</div>
                                </div>
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black {{ $item['severity'] === 'high' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40' }}">
                                    {{ $item['severity'] === 'high' ? 'إنذار مرتفع' : 'تنبيه متابعة' }}
                                </span>
                            </div>

                            <div class="space-y-1 my-1">
                                @foreach($item['reasons'] as $reason)
                                    <div class="text-xs font-bold {{ $item['severity'] === 'high' ? 'text-rose-300' : 'text-amber-300' }} flex items-center gap-1.5">
                                        <span>•</span>
                                        <span>{{ $reason }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pt-2 border-t border-gray-800 flex items-center justify-between gap-2">
                                <button wire:click="$set('selectedStudentId', {{ $item['student']->id }})" class="text-xs text-indigo-300 hover:text-indigo-200 font-bold underline">
                                    عرض السجل الكامل
                                </button>
                                <a href="{{ route('student.monthly-report.pdf', $item['student']->id) }}" target="_blank" class="px-2.5 py-1 bg-white/10 hover:bg-white/20 text-white rounded-lg text-[11px] font-black transition">
                                    التقرير الشهري
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($this->analytics)
            @php
                $student = $this->analytics['student'];
                $att = $this->analytics['attendance'];
                $exm = $this->analytics['exams'];
                $hw = $this->analytics['homeworks'];
                $ovr = $this->analytics['overall'];
            @endphp

            <!-- كرت التقييم العام ومؤشر الطالب الملكي -->
            <div class="bg-gradient-to-r from-gray-900 via-indigo-950 to-gray-900 text-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-indigo-800/60 relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-36 h-36 bg-indigo-500/10 rounded-full blur-2xl"></div>
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                    <div class="flex items-center gap-5">
                        <div class="w-20 h-20 rounded-2xl bg-indigo-500/20 border border-indigo-400/40 flex items-center justify-center text-4xl shadow-inner shrink-0">
                            🎓
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-2xl font-black text-white">{{ $student->name }}</h2>
                                <span class="px-3 py-1 rounded-full text-xs font-black bg-indigo-500/30 text-indigo-300 border border-indigo-400/40 font-mono">
                                    {{ $student->qr_code }}
                                </span>
                            </div>
                            <p class="text-xs text-indigo-200 mt-1 font-semibold">
                                {{ $student->educationalStage?->name ?? 'المرحلة العامة' }} • هاتف ولي الأمر: <span dir="ltr" class="font-mono text-white font-bold">{{ $student->parent_phone }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 bg-gray-950/60 border border-indigo-700/50 rounded-2xl p-4 shadow-inner">
                        <div class="text-center px-2">
                            <div class="text-[11px] text-indigo-300 font-bold mb-1">المستوى والتقدير</div>
                            <div class="text-sm font-black text-amber-300">{{ $ovr['grade']['label'] }}</div>
                        </div>
                        <div class="h-10 w-px bg-indigo-700/40"></div>
                        <div class="text-center px-2">
                            <div class="text-[11px] text-indigo-300 font-bold mb-1">المعدل العام المركب</div>
                            <div class="text-3xl font-black text-emerald-400 font-mono">{{ $ovr['score'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- شبكة الإحصائيات الأربع بتصميم داكن متناسق مع توهج ملون -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- الحضور -->
                <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-blue-500/60 shadow-lg hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                    <div class="flex items-center justify-between text-gray-400 mb-2">
                        <span class="text-xs font-black text-gray-200 group-hover:text-blue-400 transition">نسبة الحضور</span>
                        <div class="w-8 h-8 rounded-xl bg-blue-950/80 border border-blue-800/60 text-blue-400 flex items-center justify-center">
                            <x-heroicon-o-calendar class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="text-3xl font-black text-blue-400 my-1 font-mono tracking-tight">{{ $att['rate'] }}%</div>
                    <div class="text-[10px] font-bold text-gray-400 mt-1">حضر {{ $att['present'] }} من {{ $att['total'] }} حصة (غياب: {{ $att['absent'] }})</div>
                </div>

                <!-- الامتحانات -->
                <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-emerald-500/60 shadow-lg hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                    <div class="flex items-center justify-between text-gray-400 mb-2">
                        <span class="text-xs font-black text-gray-200 group-hover:text-emerald-400 transition">متوسط الامتحانات</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-950/80 border border-emerald-800/60 text-emerald-400 flex items-center justify-center">
                            <x-heroicon-o-academic-cap class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="text-3xl font-black text-emerald-400 my-1 font-mono tracking-tight">{{ $exm['average'] }}%</div>
                    <div class="text-[10px] font-bold text-gray-400 mt-1">خاض {{ $exm['total'] }} اختباراً (أعلى: {{ $exm['highest'] }}%)</div>
                </div>

                <!-- الواجبات -->
                <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-purple-500/60 shadow-lg hover:shadow-2xl hover:shadow-purple-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                    <div class="flex items-center justify-between text-gray-400 mb-2">
                        <span class="text-xs font-black text-gray-200 group-hover:text-purple-400 transition">الواجبات والتكليفات</span>
                        <div class="w-8 h-8 rounded-xl bg-purple-950/80 border border-purple-800/60 text-purple-400 flex items-center justify-center">
                            <x-heroicon-o-book-open class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="text-3xl font-black text-purple-400 my-1 font-mono tracking-tight">{{ $hw['submitted'] }}</div>
                    <div class="text-[10px] font-bold text-gray-400 mt-1">متوسط تقييم: {{ $hw['average'] ? $hw['average'].' / 10' : 'مكتمل' }}</div>
                </div>

                <!-- التقييم والشارة -->
                <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-amber-500/60 shadow-lg hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                    <div class="flex items-center justify-between text-gray-400 mb-2">
                        <span class="text-xs font-black text-gray-200 group-hover:text-amber-400 transition">الرتبة الأكاديمية</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-950/80 border border-amber-800/60 text-amber-400 flex items-center justify-center">
                            <x-heroicon-o-trophy class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="text-3xl font-black text-amber-400 my-1 font-mono tracking-tight">{{ $ovr['grade']['badge'] }}</div>
                    <div class="text-[10px] font-bold text-amber-300/90 mt-1">{{ $ovr['grade']['label'] }}</div>
                </div>
            </div>

            <!-- جدول سجل الامتحانات التفصيلي -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between bg-gray-850">
                    <h4 class="font-black text-sm text-white flex items-center gap-2">
                        <x-heroicon-o-chart-bar-square class="w-5 h-5 text-emerald-400" />
                        <span>السجل الزمني لنتائج امتحانات الطالب</span>
                    </h4>
                    <span class="text-xs px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full font-bold">
                        {{ count($exm['history']) }} اختبارات مرصودة
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead>
                            <tr class="text-xs font-black text-gray-400 bg-gray-800 border-b border-gray-700">
                                <th class="py-3.5 px-4">عنوان الامتحان</th>
                                <th class="py-3.5 px-4">التاريخ</th>
                                <th class="py-3.5 px-4 text-center text-emerald-400">الدرجة المحصلة</th>
                                <th class="py-3.5 px-4 text-center text-gray-300">الدرجة العظمى</th>
                                <th class="py-3.5 px-4 text-center text-amber-400">النسبة المئوية</th>
                                <th class="py-3.5 px-4 text-center">التقييم والتقدير</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 font-semibold">
                            @forelse($exm['history'] as $item)
                                <tr class="hover:bg-gray-800/60 transition-colors">
                                    <td class="py-3.5 px-4 font-black text-white text-sm">{{ $item['title'] }}</td>
                                    <td class="py-3.5 px-4 text-xs text-gray-400 font-mono">{{ $item['date'] }}</td>
                                    <td class="py-3.5 px-4 text-center font-black text-emerald-400 font-mono text-base">{{ $item['score'] }}</td>
                                    <td class="py-3.5 px-4 text-center text-xs text-gray-400 font-mono">{{ $item['max'] }}</td>
                                    <td class="py-3.5 px-4 text-center font-black font-mono {{ $item['percentage'] >= 85 ? 'text-emerald-400' : ($item['percentage'] >= 65 ? 'text-amber-400' : 'text-rose-400') }}">
                                        {{ $item['percentage'] }}%
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($item['percentage'] >= 90)
                                            <span class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-950/80 border border-emerald-800 text-emerald-300">ممتاز </span>
                                        @elseif($item['percentage'] >= 75)
                                            <span class="px-3 py-1 rounded-xl text-xs font-black bg-blue-950/80 border border-blue-800 text-blue-300">جيد جداً 👍</span>
                                        @elseif($item['percentage'] >= 50)
                                            <span class="px-3 py-1 rounded-xl text-xs font-black bg-amber-950/80 border border-amber-800 text-amber-300">مقبول ⚠️</span>
                                        @else
                                            <span class="px-3 py-1 rounded-xl text-xs font-black bg-rose-950/80 border border-rose-800 text-rose-300">راسب ❌</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-500 text-xs font-bold">
                                        لا توجد امتحانات مسجلة لهذا الطالب حتى الآن.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
