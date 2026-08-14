<x-filament-panels::page>
    @php
        $data = $this->getAnalyticsData();
    @endphp

    {{-- ─── 1. شريط الأدوات والتصفية الفعلي ─── --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-lg font-bold text-sm hover:bg-blue-100 transition flex items-center gap-2">
                <x-heroicon-o-printer class="w-4 h-4" />
                <span>تصدير وطباعة التقرير</span>
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="period_type" class="text-sm font-semibold border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-200">
                <option value="this_month">هذا الشهر</option>
                <option value="last_month">الشهر السابق</option>
                <option value="this_year">هذه السنة</option>
            </select>

            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500">
                <span>من</span>
                <input type="date" wire:model.live="from_date" class="text-sm border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-1.5 text-gray-700 dark:text-gray-200">
            </div>

            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500">
                <span>إلى</span>
                <input type="date" wire:model.live="to_date" class="text-sm border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-1.5 text-gray-700 dark:text-gray-200">
            </div>
        </div>
    </div>

    {{-- ─── 2. كروت الإحصائيات الـ 6 (ديناميكية 100% من قاعدة البيانات) ─── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">

        {{-- إجمالي الإيرادات --}}
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-center flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-bold">إجمالي الإيرادات</span>
                <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
            </div>
            <div class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 my-1">
                {{ number_format($data['revenue'], 2) }} <span class="text-xs font-normal">ج.م</span>
            </div>
            <div class="text-[10px] font-semibold text-gray-400">
                محسوب من السداد المالي
            </div>
        </div>

        {{-- إجمالي المصروفات --}}
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-center flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-bold">إجمالي المصروفات</span>
                <x-heroicon-o-arrow-trending-down class="w-5 h-5 text-rose-500" />
            </div>
            <div class="text-xl font-extrabold text-rose-600 dark:text-rose-400 my-1">
                {{ number_format($data['expenses'], 2) }} <span class="text-xs font-normal">ج.م</span>
            </div>
            <div class="text-[10px] font-semibold text-gray-400">
                مصروفات + رواتب
            </div>
        </div>

        {{-- صافي الربح --}}
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-center flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-bold">صافي الربح</span>
                <x-heroicon-o-chart-bar class="w-5 h-5 text-purple-500" />
            </div>
            <div class="text-xl font-extrabold {{ $data['net_profit'] >= 0 ? 'text-purple-600 dark:text-purple-400' : 'text-rose-600' }} my-1">
                {{ number_format($data['net_profit'], 2) }} <span class="text-xs font-normal">ج.م</span>
            </div>
            <div class="text-[10px] font-semibold text-gray-400">
                الإيرادات - المصروفات
            </div>
        </div>

        {{-- إجمالي الطلاب --}}
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-center flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-bold">إجمالي الطلاب</span>
                <x-heroicon-o-users class="w-5 h-5 text-blue-500" />
            </div>
            <div class="text-xl font-extrabold text-blue-600 dark:text-blue-400 my-1">
                {{ number_format($data['total_students']) }} <span class="text-xs font-normal">طالب</span>
            </div>
            <div class="text-[10px] font-semibold text-gray-400">
                الطلاب المقيدين بالسنتر
            </div>
        </div>

        {{-- إجمالي الحضور --}}
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-center flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-bold">إجمالي الحضور</span>
                <x-heroicon-o-check-circle class="w-5 h-5 text-teal-500" />
            </div>
            <div class="text-xl font-extrabold text-teal-600 dark:text-teal-400 my-1">
                {{ number_format($data['today_present']) }} <span class="text-xs font-normal">طالب</span>
            </div>
            <div class="text-[10px] font-semibold text-gray-400">
                حالة الحضور للفترة
            </div>
        </div>

        {{-- نسبة الحضور --}}
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-center flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-bold">نسبة الحضور</span>
                <x-heroicon-o-chart-pie class="w-5 h-5 text-amber-500" />
            </div>
            <div class="text-xl font-extrabold text-amber-600 dark:text-amber-400 my-1">
                {{ $data['attendance_rate'] }}%
            </div>
            <div class="text-[10px] font-semibold text-gray-400">
                معدل الالتزام الحركي
            </div>
        </div>

    </div>

    {{-- ─── 3. التقارير الجاهزة للتصدير + ملخص الأداء الفعلي ─── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- جدول روابط التقارير المباشرة --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">قائمة التقارير المتاحة بالنظام</h3>
                    <p class="text-xs text-gray-500">الانتقال والتصدير المباشر لبيانات السجل</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-bold text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="p-3">نوع التقرير</th>
                            <th class="p-3">الوصف والوحدة</th>
                            <th class="p-3 text-center">الانتقال المباشر</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs font-medium">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="p-3 font-bold text-gray-900 dark:text-white">سجل مدفوعات الطلاب</td>
                            <td class="p-3 text-gray-500">جميع إيصالات وتحصيلات الرسوم الدراسية</td>
                            <td class="p-3 text-center">
                                <a href="{{ url('/admin/student-payments') }}" class="px-3 py-1.5 bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 rounded font-bold hover:bg-blue-100 transition inline-block">
                                    عرض السجل 👁️
                                </a>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="p-3 font-bold text-gray-900 dark:text-white">سجل المصروفات التشغيلية</td>
                            <td class="p-3 text-gray-500">تفاصيل جميع النفقات والمصروفات حسب التصنيف</td>
                            <td class="p-3 text-center">
                                <a href="{{ url('/admin/expenses') }}" class="px-3 py-1.5 bg-rose-50 text-rose-600 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 rounded font-bold hover:bg-rose-100 transition inline-block">
                                    عرض المصروفات 👁️
                                </a>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="p-3 font-bold text-gray-900 dark:text-white">سجل الرواتب والأجور</td>
                            <td class="p-3 text-gray-500">سجل الرواتب والمستحقات المصروفة للموظفين</td>
                            <td class="p-3 text-center">
                                <a href="{{ url('/admin/salaries') }}" class="px-3 py-1.5 bg-teal-50 text-teal-600 dark:bg-teal-950 dark:text-teal-300 border border-teal-200 rounded font-bold hover:bg-teal-100 transition inline-block">
                                    عرض الرواتب 👁️
                                </a>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="p-3 font-bold text-gray-900 dark:text-white">تقرير الحضور والغياب</td>
                            <td class="p-3 text-gray-500">سجلات حضور وغياب الطلاب بالجلسات</td>
                            <td class="p-3 text-center">
                                <a href="{{ url('/admin/group-sessions') }}" class="px-3 py-1.5 bg-purple-50 text-purple-600 dark:bg-purple-950 dark:text-purple-300 border border-purple-200 rounded font-bold hover:bg-purple-100 transition inline-block">
                                    عرض الحضور 👁️
                                </a>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="p-3 font-bold text-gray-900 dark:text-white">تقرير الامتحانات والنتائج</td>
                            <td class="p-3 text-gray-500">درجات ونتائج الامتحانات المرصودة</td>
                            <td class="p-3 text-center">
                                <a href="{{ url('/admin/exams') }}" class="px-3 py-1.5 bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 rounded font-bold hover:bg-amber-100 transition inline-block">
                                    عرض الامتحانات 👁️
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ملخص الأداء (ديناميكي 100%) --}}
        <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4">ملخص الأداء الفعلي</h3>

            <div class="space-y-3">

                <div class="flex items-center justify-between p-3 bg-blue-50/60 dark:bg-blue-950/20 rounded-xl">
                    <div class="flex items-center gap-2.5">
                        <x-heroicon-o-currency-dollar class="w-5 h-5 text-blue-500" />
                        <span class="font-semibold text-xs text-gray-700 dark:text-gray-300">متوسط دخل الطالب للفترة</span>
                    </div>
                    <span class="font-extrabold text-xs text-blue-600 dark:text-blue-400">{{ number_format($data['avg_fee'], 2) }} ج.م</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-amber-50/60 dark:bg-amber-950/20 rounded-xl">
                    <div class="flex items-center gap-2.5">
                        <x-heroicon-o-credit-card class="w-5 h-5 text-amber-500" />
                        <span class="font-semibold text-xs text-gray-700 dark:text-gray-300">نسبة الطلاب المسددين</span>
                    </div>
                    <span class="font-extrabold text-xs text-amber-600 dark:text-amber-400">{{ $data['payment_rate'] }}%</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-rose-50/60 dark:bg-rose-950/20 rounded-xl">
                    <div class="flex items-center gap-2.5">
                        <x-heroicon-o-exclamation-circle class="w-5 h-5 text-rose-500" />
                        <span class="font-semibold text-xs text-gray-700 dark:text-gray-300">الطلاب غير المسددين</span>
                    </div>
                    <span class="font-extrabold text-xs text-rose-600 dark:text-rose-400">{{ number_format($data['late_students']) }} طلاب</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-purple-50/60 dark:bg-purple-950/20 rounded-xl">
                    <div class="flex items-center gap-2.5">
                        <x-heroicon-o-document-text class="w-5 h-5 text-purple-500" />
                        <span class="font-semibold text-xs text-gray-700 dark:text-gray-300">إجمالي الحركات المالية بالدورة</span>
                    </div>
                    <span class="font-extrabold text-xs text-purple-600 dark:text-purple-400">{{ number_format($data['total_transactions']) }} حركة</span>
                </div>

            </div>
        </div>

    </div>
</x-filament-panels::page>
