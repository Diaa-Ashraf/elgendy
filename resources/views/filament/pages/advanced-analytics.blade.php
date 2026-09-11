<x-filament-panels::page>
    @php
        $analytics = $this->getAnalyticsData();
        $metrics = $analytics['academicMetrics'];
    @endphp

    <div class="space-y-6">

        {{-- ─── 1. كروت مؤشرات الجودة والأداء الكبرى (KPIs) ─── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            {{-- إجمالي الطلاب --}}
            <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-blue-500/60 shadow-lg hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/25 transition-all"></div>
                <div class="flex items-center justify-between text-gray-400 mb-3 relative z-10">
                    <span class="text-xs font-black text-gray-200 group-hover:text-blue-400 transition">إجمالي الطلاب</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-950/80 border border-blue-800/60 text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-user-group class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-3xl font-black text-blue-400 my-1 font-mono tracking-tight relative z-10">
                    {{ number_format($metrics['total_students']) }}
                </div>
                <div class="text-[10px] font-bold text-blue-300 bg-blue-950/60 border border-blue-800/40 py-1 px-2 rounded-lg mt-2 relative z-10">
                    طالب مقيد بالمنظومة
                </div>
            </div>

            {{-- نسبة الحضور العامة --}}
            <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-teal-500/60 shadow-lg hover:shadow-2xl hover:shadow-teal-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-teal-500/10 rounded-full blur-xl group-hover:bg-teal-500/25 transition-all"></div>
                <div class="flex items-center justify-between text-gray-400 mb-3 relative z-10">
                    <span class="text-xs font-black text-gray-200 group-hover:text-teal-400 transition">معدل الحضور العام</span>
                    <div class="w-8 h-8 rounded-xl bg-teal-950/80 border border-teal-800/60 text-teal-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-check-badge class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-3xl font-black text-teal-400 my-1 font-mono tracking-tight relative z-10">
                    {{ $metrics['overall_attendance_rate'] }}%
                </div>
                <div class="text-[10px] font-bold text-teal-300 bg-teal-950/60 border border-teal-800/40 py-1 px-2 rounded-lg mt-2 relative z-10">
                    التزام ومواظبة الطلاب
                </div>
            </div>

            {{-- متوسط درجات الامتحانات --}}
            <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-amber-500/60 shadow-lg hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-amber-500/10 rounded-full blur-xl group-hover:bg-amber-500/25 transition-all"></div>
                <div class="flex items-center justify-between text-gray-400 mb-3 relative z-10">
                    <span class="text-xs font-black text-gray-200 group-hover:text-amber-400 transition">متوسط درجات الطلاب</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-950/80 border border-amber-800/60 text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-academic-cap class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-3xl font-black text-amber-400 my-1 font-mono tracking-tight relative z-10">
                    {{ $metrics['avg_exam_score'] }}%
                </div>
                <div class="text-[10px] font-bold text-amber-300 bg-amber-950/60 border border-amber-800/40 py-1 px-2 rounded-lg mt-2 relative z-10">
                    مستوى التحصيل الأكاديمي
                </div>
            </div>

            {{-- إجمالي الامتحانات --}}
            <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-purple-500/60 shadow-lg hover:shadow-2xl hover:shadow-purple-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-purple-500/10 rounded-full blur-xl group-hover:bg-purple-500/25 transition-all"></div>
                <div class="flex items-center justify-between text-gray-400 mb-3 relative z-10">
                    <span class="text-xs font-black text-gray-200 group-hover:text-purple-400 transition">إجمالي الامتحانات</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-950/80 border border-purple-800/60 text-purple-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-3xl font-black text-purple-400 my-1 font-mono tracking-tight relative z-10">
                    {{ number_format($metrics['total_exams']) }}
                </div>
                <div class="text-[10px] font-bold text-purple-300 bg-purple-950/60 border border-purple-800/40 py-1 px-2 rounded-lg mt-2 relative z-10">
                    امتحانات ورقية وإلكترونية
                </div>
            </div>

        </div>

        {{-- ─── 2. جدول اتجاه الأرباح والإيرادات الشهرية (آخر 6 أشهر) ─── --}}
        <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-xl overflow-hidden">
            <div class="p-5 border-b border-gray-800 flex items-center justify-between bg-gray-850">
                <h3 class="font-black text-base text-white flex items-center gap-2">
                    <x-heroicon-o-chart-bar-square class="w-5 h-5 text-amber-500" />
                    <span>تحليل حركة الأرباح والإيرادات والمصروفات الشهرية (آخر 6 أشهر)</span>
                </h3>
                <span class="text-xs px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-full font-bold">
                    تحديث لحظي ومحمي بالكاش
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-800 text-xs font-black text-gray-400 uppercase border-b border-gray-700">
                        <tr>
                            <th class="p-4">الشهر والبيان</th>
                            <th class="p-4 text-emerald-400">الإيرادات المحصلة</th>
                            <th class="p-4 text-rose-400">المصروفات والرواتب</th>
                            <th class="p-4 text-indigo-400">صافي الربح الفعلي</th>
                            <th class="p-4 text-center">مؤشر الأداء المالي</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800 font-bold">
                        @foreach($analytics['monthlyChart'] as $m)
                            <tr class="hover:bg-gray-800/60 transition-colors duration-150">
                                <td class="p-4 font-black text-gray-100 text-sm">{{ $m['label'] }}</td>
                                <td class="p-4 text-emerald-400 font-black font-mono text-sm">{{ number_format($m['revenue'], 2) }} ج.م</td>
                                <td class="p-4 text-rose-400 font-black font-mono text-sm">{{ number_format($m['expenses'], 2) }} ج.م</td>
                                <td class="p-4 font-black font-mono text-sm {{ $m['profit'] >= 0 ? 'text-indigo-400' : 'text-rose-400' }}">
                                    {{ number_format($m['profit'], 2) }} ج.م
                                </td>
                                <td class="p-4 text-center">
                                    @if($m['profit'] > 0)
                                        <span class="px-3 py-1 bg-emerald-950/80 border border-emerald-800 text-emerald-300 rounded-xl font-black text-xs shadow-sm">
                                            📈 فائض أرباح ممتاز
                                        </span>
                                    @elseif($m['profit'] < 0)
                                        <span class="px-3 py-1 bg-rose-950/80 border border-rose-800 text-rose-300 rounded-xl font-black text-xs shadow-sm">
                                            📉 عجز تشغيلي
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-800 border border-gray-700 text-gray-300 rounded-xl font-black text-xs">
                                            ⚖️ نقطة التعادل
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── 3. توزيع الطلاب على المراحل وتحليل ربحية المجموعات ─── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- كروت توزيع المراحل الدراسية --}}
            <div class="lg:col-span-1 bg-gray-900 p-5 rounded-2xl border border-gray-800 shadow-xl space-y-4">
                <h3 class="font-black text-base text-white flex items-center gap-2 border-b border-gray-800 pb-3">
                    <x-heroicon-o-academic-cap class="w-5 h-5 text-emerald-400" />
                    <span>توزيع الطلاب حسب المراحل الدراسية</span>
                </h3>

                <div class="space-y-3">
                    @forelse($analytics['stageDistribution'] as $stg)
                        <div class="group p-4 rounded-xl bg-gray-850 hover:bg-gray-800 border border-gray-800 hover:border-emerald-500/50 transition-all flex items-center justify-between">
                            <div>
                                <span class="text-sm font-black text-gray-100 block">{{ $stg->name }}</span>
                                <span class="text-xs text-gray-400 font-bold mt-0.5 block">{{ $stg->groups_count }} مجموعات دراسية</span>
                            </div>
                            <div class="text-left">
                                <span class="text-2xl font-black text-emerald-400 font-mono">{{ $stg->count }}</span>
                                <span class="text-[10px] text-gray-400 block">طالب مقيد</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 text-center py-4">لا توجد مراحل مسجلة</p>
                    @endforelse
                </div>
            </div>

            {{-- تحليل كفاءة وربحية المجموعات الدراسية --}}
            <div class="lg:col-span-2 bg-gray-900 rounded-2xl border border-gray-800 shadow-xl overflow-hidden">
                <div class="p-5 border-b border-gray-800 flex items-center justify-between bg-gray-850">
                    <h3 class="font-black text-base text-white flex items-center gap-2">
                        <x-heroicon-o-presentation-chart-bar class="w-5 h-5 text-blue-400" />
                        <span>تحليل الطاقة الاستيعابية والربحية المتوقعة للمجموعات</span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-800 text-xs font-black text-gray-400 uppercase border-b border-gray-700">
                            <tr>
                                <th class="p-3.5">اسم المجموعة</th>
                                <th class="p-3.5">المرحلة</th>
                                <th class="p-3.5 text-center">الطلاب المقيدين</th>
                                <th class="p-3.5 text-center">سعر الاشتراك</th>
                                <th class="p-3.5 text-left text-emerald-400">الدخل المتوقع شهرياً</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 font-bold">
                            @forelse($analytics['groupsAnalytics'] as $grp)
                                <tr class="hover:bg-gray-800/60 transition-colors">
                                    <td class="p-3.5 font-black text-gray-100 text-xs">{{ $grp['name'] }}</td>
                                    <td class="p-3.5 text-gray-400">{{ $grp['stage'] }}</td>
                                    <td class="p-3.5 text-center">
                                        <span class="px-2.5 py-1 bg-blue-950/70 border border-blue-800 text-blue-300 rounded-lg font-black">
                                            {{ $grp['students_count'] }} طالب
                                        </span>
                                    </td>
                                    <td class="p-3.5 text-center font-mono text-gray-300">{{ number_format($grp['price']) }} ج.م</td>
                                    <td class="p-3.5 text-left font-black text-emerald-400 font-mono text-sm">
                                        {{ number_format($grp['expected_revenue']) }} ج.م
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-gray-500">لا توجد مجموعات نشطة حالياً</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
