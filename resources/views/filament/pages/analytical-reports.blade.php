<x-filament-panels::page>
    @php
    $data = $this->getAnalyticsData();
    @endphp

    <div class="space-y-6">

        {{-- ─── 1. شريط الأدوات والتصفية (باللون الأبيض الناصع والخط الأسود العريض) ─── --}}
        <div style="background-color: #111827; border: 1px solid #1f2937;" class="p-4 sm:p-5 rounded-2xl shadow-xl mb-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">

                {{-- زر الطباعة والتصدير باللون الأبيض الساطع والنص الأسود --}}
                <div class="w-full md:w-auto">
                    <button type="button" onclick="window.print()" style="background-color: #ffffff !important; color: #000000 !important; border: 1px solid #e5e7eb;" class="w-full md:w-auto px-5 py-2.5 hover:bg-gray-100 rounded-xl text-xs font-black shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="#000000" class="w-4 h-4 group-hover:scale-110 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                        </svg>
                        <span style="color: #000000 !important; font-weight: 900 !important; font-size: 13px !important;">تصدير وطباعة التقرير الشامل</span>
                    </button>
                </div>

                {{-- الفلاتر الزمنية بحقول بيضاء ناصعة وخط أسود عريض --}}
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                    <div>
                        <select wire:model.live="period_type" style="background-color: #ffffff !important; color: #000000 !important; border: 1px solid #d1d5db !important; font-weight: 800 !important;" class="text-xs rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500 transition cursor-pointer shadow-md">
                            <option value="this_month" style="background-color: #ffffff; color: #000000;">📅 هذا الشهر الحالي</option>
                            <option value="last_month" style="background-color: #ffffff; color: #000000;">🗓️ الشهر السابق</option>
                            <option value="this_year" style="background-color: #ffffff; color: #000000;">📆 هذه السنة كاملة</option>
                        </select>
                    </div>

                    <div style="background-color: #ffffff !important; color: #000000 !important; border: 1px solid #d1d5db !important;" class="flex items-center gap-2 text-xs font-black px-3.5 py-2 rounded-xl shadow-md">
                        <span style="color: #000000 !important; font-weight: 900 !important;">من:</span>
                        <input type="date" wire:model.live="from_date" style="color-scheme: light; background: transparent; color: #000000 !important; font-weight: 800 !important;" class="text-xs bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                    </div>

                    <div style="background-color: #ffffff !important; color: #000000 !important; border: 1px solid #d1d5db !important;" class="flex items-center gap-2 text-xs font-black px-3.5 py-2 rounded-xl shadow-md">
                        <span style="color: #000000 !important; font-weight: 900 !important;">إلى:</span>
                        <input type="date" wire:model.live="to_date" style="color-scheme: light; background: transparent; color: #000000 !important; font-weight: 800 !important;" class="text-xs bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── 2. كروت الإحصائيات الـ 6 (ألوان داكنة راقية مع توهج ملون وتأثير Hover جذاب) ─── --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">

            {{-- إجمالي الإيرادات --}}
            <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-emerald-500/60 shadow-lg hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/25 transition-all"></div>
                <div class="flex items-center justify-between text-gray-400 mb-3 relative z-10">
                    <span class="text-xs font-black text-gray-200 group-hover:text-emerald-400 transition">إجمالي الإيرادات</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-950/80 border border-emerald-800/60 text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-banknotes class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-black text-emerald-400 my-1 font-mono tracking-tight relative z-10">
                    {{ number_format($data['revenue'], 2) }} <span class="text-xs font-bold text-gray-400">ج.م</span>
                </div>
                <div class="text-[10px] font-bold text-emerald-300 bg-emerald-950/60 border border-emerald-800/40 py-1 px-2 rounded-lg mt-2 relative z-10">
                    محصلة من رسوم الطلاب
                </div>
            </div>

            {{-- إجمالي المصروفات --}}
            <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-rose-500/60 shadow-lg hover:shadow-2xl hover:shadow-rose-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-rose-500/10 rounded-full blur-xl group-hover:bg-rose-500/25 transition-all"></div>
                <div class="flex items-center justify-between text-gray-400 mb-3 relative z-10">
                    <span class="text-xs font-black text-gray-200 group-hover:text-rose-400 transition">إجمالي المصروفات</span>
                    <div class="w-8 h-8 rounded-xl bg-rose-950/80 border border-rose-800/60 text-rose-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-arrow-trending-down class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-black text-rose-400 my-1 font-mono tracking-tight relative z-10">
                    {{ number_format($data['expenses'], 2) }} <span class="text-xs font-bold text-gray-400">ج.م</span>
                </div>
                <div class="text-[10px] font-bold text-rose-300 bg-rose-950/60 border border-rose-800/40 py-1 px-2 rounded-lg mt-2 relative z-10">
                    نفقات تشغيلية + رواتب
                </div>
            </div>

            {{-- صافي الربح --}}
            <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-indigo-500/60 shadow-lg hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl group-hover:bg-indigo-500/25 transition-all"></div>
                <div class="flex items-center justify-between text-gray-400 mb-3 relative z-10">
                    <span class="text-xs font-black text-gray-200 group-hover:text-indigo-400 transition">صافي الأرباح</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-950/80 border border-indigo-800/60 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-chart-bar class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-black {{ $data['net_profit'] >= 0 ? 'text-indigo-400' : 'text-rose-400' }} my-1 font-mono tracking-tight relative z-10">
                    {{ number_format($data['net_profit'], 2) }} <span class="text-xs font-bold text-gray-400">ج.م</span>
                </div>
                <div class="text-[10px] font-bold text-indigo-300 bg-indigo-950/60 border border-indigo-800/40 py-1 px-2 rounded-lg mt-2 relative z-10">
                    الإيرادات - المصروفات
                </div>
            </div>

            {{-- إجمالي الطلاب --}}
            <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-blue-500/60 shadow-lg hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/25 transition-all"></div>
                <div class="flex items-center justify-between text-gray-400 mb-3 relative z-10">
                    <span class="text-xs font-black text-gray-200 group-hover:text-blue-400 transition">إجمالي الطلاب</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-950/80 border border-blue-800/60 text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-users class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-black text-blue-400 my-1 font-mono tracking-tight relative z-10">
                    {{ number_format($data['total_students']) }} <span class="text-xs font-bold text-gray-400">طالب</span>
                </div>
                <div class="text-[10px] font-bold text-blue-300 bg-blue-950/60 border border-blue-800/40 py-1 px-2 rounded-lg mt-2 relative z-10">
                    الطلاب المقيدين بالسنتر
                </div>
            </div>

            {{-- إجمالي الحضور --}}
            <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-teal-500/60 shadow-lg hover:shadow-2xl hover:shadow-teal-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-teal-500/10 rounded-full blur-xl group-hover:bg-teal-500/25 transition-all"></div>
                <div class="flex items-center justify-between text-gray-400 mb-3 relative z-10">
                    <span class="text-xs font-black text-gray-200 group-hover:text-teal-400 transition">سجلات الحضور</span>
                    <div class="w-8 h-8 rounded-xl bg-teal-950/80 border border-teal-800/60 text-teal-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-check-circle class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-black text-teal-400 my-1 font-mono tracking-tight relative z-10">
                    {{ number_format($data['today_present']) }} <span class="text-xs font-bold text-gray-400">حضور</span>
                </div>
                <div class="text-[10px] font-bold text-teal-300 bg-teal-950/60 border border-teal-800/40 py-1 px-2 rounded-lg mt-2 relative z-10">
                    حضور الحصص بالفترة
                </div>
            </div>

            {{-- نسبة الحضور --}}
            <div class="group bg-gray-900 p-5 rounded-2xl border border-gray-800 hover:border-amber-500/60 shadow-lg hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-300 transform hover:-translate-y-1 text-center flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-amber-500/10 rounded-full blur-xl group-hover:bg-amber-500/25 transition-all"></div>
                <div class="flex items-center justify-between text-gray-400 mb-3 relative z-10">
                    <span class="text-xs font-black text-gray-200 group-hover:text-amber-400 transition">معدل الالتزام</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-950/80 border border-amber-800/60 text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-chart-pie class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-black text-amber-400 my-1 font-mono tracking-tight relative z-10">
                    {{ $data['attendance_rate'] }}%
                </div>
                <div class="text-[10px] font-bold text-amber-300 bg-amber-950/60 border border-amber-800/40 py-1 px-2 rounded-lg mt-2 relative z-10">
                    معدل حضور الطلاب
                </div>
            </div>

        </div>

        {{-- ─── 3. التقارير الجاهزة للتصدير + ملخص الأداء الفعلي ─── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- جدول روابط التقارير المباشرة --}}
            <div class="lg:col-span-2 bg-gray-900 rounded-2xl border border-gray-800 shadow-lg overflow-hidden">
                <div class="p-5 border-b border-gray-800 flex items-center justify-between bg-gray-850">
                    <div>
                        <h3 class="font-black text-base text-white flex items-center gap-2">
                            <span>📋 مراكز وتقارير المنظومة المباشرة</span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">الانتقال السريع لجميع سجلات وتقارير النظام مع إمكانية التصدير</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-gray-800 text-xs font-bold text-gray-400 uppercase border-b border-gray-700">
                            <tr>
                                <th class="p-4">نوع التقرير والسجل</th>
                                <th class="p-4">الوصف</th>
                                <th class="p-4 text-left">الانتقال المباشر</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 text-xs font-medium">
                            <tr class="hover:bg-blue-950/30 transition-colors duration-150">
                                <td class="p-4 font-extrabold text-gray-100 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm shadow-blue-500/50"></span>
                                    سجل مدفوعات واشتراكات الطلاب
                                </td>
                                <td class="p-4 text-gray-400">جميع إيصالات وتحصيلات الرسوم الدراسية وحسابات الطلاب</td>
                                <td class="p-4 text-left">
                                    <a href="{{ url('/admin/student-payments') }}" class="px-3.5 py-1.5 bg-blue-950/70 hover:bg-blue-900 text-blue-300 border border-blue-800/80 rounded-xl font-bold transition shadow-sm inline-flex items-center gap-1.5 hover:scale-105">
                                        <span>عرض السجل</span>
                                        <span>👁️</span>
                                    </a>
                                </td>
                            </tr>
                            <tr class="hover:bg-rose-950/30 transition-colors duration-150">
                                <td class="p-4 font-extrabold text-gray-100 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-sm shadow-rose-500/50"></span>
                                    سجل المصروفات التشغيلية
                                </td>
                                <td class="p-4 text-gray-400">تفاصيل جميع النفقات والمصروفات ومستلزمات السنتر</td>
                                <td class="p-4 text-left">
                                    <a href="{{ url('/admin/expenses') }}" class="px-3.5 py-1.5 bg-rose-950/70 hover:bg-rose-900 text-rose-300 border border-rose-800/80 rounded-xl font-bold transition shadow-sm inline-flex items-center gap-1.5 hover:scale-105">
                                        <span>عرض المصروفات</span>
                                        <span>👁️</span>
                                    </a>
                                </td>
                            </tr>
                            <tr class="hover:bg-teal-950/30 transition-colors duration-150">
                                <td class="p-4 font-extrabold text-gray-100 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500 shadow-sm shadow-teal-500/50"></span>
                                    سجل الرواتب والأجور
                                </td>
                                <td class="p-4 text-gray-400">سجل الرواتب والمستحقات المصروفة للمساعدين والموظفين</td>
                                <td class="p-4 text-left">
                                    <a href="{{ url('/admin/salaries') }}" class="px-3.5 py-1.5 bg-teal-950/70 hover:bg-teal-900 text-teal-300 border border-teal-800/80 rounded-xl font-bold transition shadow-sm inline-flex items-center gap-1.5 hover:scale-105">
                                        <span>عرض الرواتب</span>
                                        <span>👁️</span>
                                    </a>
                                </td>
                            </tr>
                            <tr class="hover:bg-purple-950/30 transition-colors duration-150">
                                <td class="p-4 font-extrabold text-gray-100 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500 shadow-sm shadow-purple-500/50"></span>
                                    تقرير الحضور والغياب وسجل الجلسات
                                </td>
                                <td class="p-4 text-gray-400">سجلات حضور وغياب الطلاب والملاحظات الأكاديمية للحصص</td>
                                <td class="p-4 text-left">
                                    <a href="{{ url('/admin/group-sessions') }}" class="px-3.5 py-1.5 bg-purple-950/70 hover:bg-purple-900 text-purple-300 border border-purple-800/80 rounded-xl font-bold transition shadow-sm inline-flex items-center gap-1.5 hover:scale-105">
                                        <span>عرض الجلسات</span>
                                        <span>👁️</span>
                                    </a>
                                </td>
                            </tr>
                            <tr class="hover:bg-amber-950/30 transition-colors duration-150">
                                <td class="p-4 font-extrabold text-gray-100 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-sm shadow-amber-500/50"></span>
                                    تقرير الامتحانات وبنوك الأسئلة
                                </td>
                                <td class="p-4 text-gray-400">درجات ونتائج الامتحانات الورقية والإلكترونية المرصودة</td>
                                <td class="p-4 text-left">
                                    <a href="{{ url('/admin/exams') }}" class="px-3.5 py-1.5 bg-amber-950/70 hover:bg-amber-900 text-amber-300 border border-amber-800/80 rounded-xl font-bold transition shadow-sm inline-flex items-center gap-1.5 hover:scale-105">
                                        <span>عرض الامتحانات</span>
                                        <span>👁️</span>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ملخص الأداء المالي والأكاديمي --}}
            <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 shadow-lg flex flex-col justify-between">
                <div>
                    <h3 class="font-black text-base text-white mb-1 flex items-center gap-2">
                        <span> مؤشرات الأداء الحيوية</span>
                    </h3>
                    <p class="text-xs text-gray-400 mb-5">تحليل ديناميكي للفترة المختارة</p>
                </div>

                <div class="space-y-3.5">

                    <div class="group flex items-center justify-between p-3.5 bg-blue-950/30 hover:bg-blue-950/60 rounded-xl border border-blue-900/60 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-900/80 text-blue-300 flex items-center justify-center font-bold text-sm">
                                💵
                            </div>
                            <span class="font-bold text-xs text-gray-200">متوسط دخل الطالب للفترة</span>
                        </div>
                        <span class="font-black text-xs text-blue-400 font-mono">{{ number_format($data['avg_fee'], 2) }} ج.م</span>
                    </div>

                    <div class="group flex items-center justify-between p-3.5 bg-emerald-950/30 hover:bg-emerald-950/60 rounded-xl border border-emerald-900/60 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-900/80 text-emerald-300 flex items-center justify-center font-bold text-sm">
                                💳
                            </div>
                            <span class="font-bold text-xs text-gray-200">نسبة سداد الاشتراكات</span>
                        </div>
                        <span class="font-black text-xs text-emerald-400 font-mono">{{ $data['payment_rate'] }}%</span>
                    </div>

                    <div class="group flex items-center justify-between p-3.5 bg-rose-950/30 hover:bg-rose-950/60 rounded-xl border border-rose-900/60 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-rose-900/80 text-rose-300 flex items-center justify-center font-bold text-sm">
                                ⚠️
                            </div>
                            <span class="font-bold text-xs text-gray-200">الطلاب غير المسددين</span>
                        </div>
                        <span class="font-black text-xs text-rose-400 font-mono">{{ number_format($data['late_students']) }} طلاب</span>
                    </div>

                    <div class="group flex items-center justify-between p-3.5 bg-purple-950/30 hover:bg-purple-950/60 rounded-xl border border-purple-900/60 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-900/80 text-purple-300 flex items-center justify-center font-bold text-sm">
                                📊
                            </div>
                            <span class="font-bold text-xs text-gray-200">إجمالي الحركات بالدورة</span>
                        </div>
                        <span class="font-black text-xs text-purple-400 font-mono">{{ number_format($data['total_transactions']) }} حركة</span>
                    </div>

                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>