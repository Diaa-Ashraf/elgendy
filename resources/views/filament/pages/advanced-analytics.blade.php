<x-filament-panels::page>
    @php
        $analytics = $this->getAnalyticsData();
    @endphp

    <div class="space-y-6">

        {{-- ─── 1. رسم بياني اتجاه الأرباح والإيرادات (آخر 6 أشهر) ─── --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <h3 class="font-bold text-base text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-chart-bar-square class="w-5 h-5 text-blue-500" />
                تحليل حركة الأرباح والإيرادات الشهرية (آخر 6 أشهر)
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 font-bold text-gray-500 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="p-3">الشهر</th>
                            <th class="p-3 text-emerald-600">الإيرادات (ج.م)</th>
                            <th class="p-3 text-rose-600">المصروفات (ج.م)</th>
                            <th class="p-3 text-purple-600">صافي الربح (ج.م)</th>
                            <th class="p-3 text-center">مستوى الأداء المالي</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-semibold">
                        @foreach($analytics['monthlyChart'] as $m)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $m['label'] }}</td>
                                <td class="p-3 text-emerald-600 font-extrabold">{{ number_format($m['revenue'], 2) }}</td>
                                <td class="p-3 text-rose-600 font-extrabold">{{ number_format($m['expenses'], 2) }}</td>
                                <td class="p-3 text-purple-600 font-extrabold">{{ number_format($m['profit'], 2) }}</td>
                                <td class="p-3 text-center">
                                    @if($m['profit'] > 0)
                                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-full font-bold">فائض أرباح ممتاز 📈</span>
                                    @elseif($m['profit'] < 0)
                                        <span class="px-2.5 py-1 bg-rose-100 text-rose-700 rounded-full font-bold">عجز مالي 📉</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full font-bold">متعادل ⚖️</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── 2. توزيع الطلاب حسب المراحل الدراسية ─── --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <h3 class="font-bold text-base text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-academic-cap class="w-5 h-5 text-emerald-500" />
                توزيع الطلاب المقيدين حسب المراحل الدراسية
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($analytics['stageDistribution'] as $stg)
                    <div class="bg-slate-50 dark:bg-gray-800 p-4 rounded-xl text-center border border-slate-200 dark:border-gray-700">
                        <span class="text-xs font-bold text-gray-500 block mb-1">{{ $stg->name }}</span>
                        <span class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">{{ $stg->count }}</span>
                        <span class="text-[10px] text-gray-400 block mt-1">طالب مسجل</span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-filament-panels::page>
