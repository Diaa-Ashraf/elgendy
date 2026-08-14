<x-filament-panels::page>
    @php
        $data = $this->ledger;
        $balance = $data['balance'] ?? 0;
    @endphp

    {{-- ─── كروت الملخص المالي ─── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-center">
            <div class="text-xs font-bold text-gray-500 mb-2">إجمالي المستحقات</div>
            <div class="text-2xl font-extrabold text-rose-600">{{ number_format($data['total_due'], 2) }} <span class="text-sm font-normal">ج.م</span></div>
            <div class="text-[10px] text-gray-400 mt-1">بناءً على الاشتراكات الشهرية</div>
        </div>

        <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-center">
            <div class="text-xs font-bold text-gray-500 mb-2">إجمالي المدفوع</div>
            <div class="text-2xl font-extrabold text-emerald-600">{{ number_format($data['total_paid'], 2) }} <span class="text-sm font-normal">ج.م</span></div>
            <div class="text-[10px] text-gray-400 mt-1">من سجل المدفوعات الفعلي</div>
        </div>

        <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border {{ $balance >= 0 ? 'border-emerald-300 dark:border-emerald-800' : 'border-rose-300 dark:border-rose-800' }} shadow-sm text-center">
            <div class="text-xs font-bold text-gray-500 mb-2">{{ $balance >= 0 ? 'رصيد زائد (دائن)' : 'مديونية متبقية (مدين)' }}</div>
            <div class="text-2xl font-extrabold {{ $balance >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ number_format(abs($balance), 2) }} <span class="text-sm font-normal">ج.م</span>
            </div>
            <div class="text-[10px] text-gray-400 mt-1">المدفوع {{ $balance >= 0 ? 'أكثر من' : 'أقل من' }} المستحق</div>
        </div>
    </div>

    {{-- ─── تفاصيل الاشتراكات حسب المجموعة ─── --}}
    <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm mb-6">
        <h3 class="font-bold text-base text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <x-heroicon-o-rectangle-stack class="w-5 h-5 text-blue-500" />
            تفاصيل الاشتراكات حسب المجموعة
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-bold text-gray-500 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="p-3">المجموعة</th>
                        <th class="p-3">المادة</th>
                        <th class="p-3">الرسم الشهري</th>
                        <th class="p-3">إجمالي المستحق</th>
                        <th class="p-3">إجمالي المدفوع</th>
                        <th class="p-3">الرصيد</th>
                        <th class="p-3">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs font-medium">
                    @forelse($data['group_summaries'] ?? [] as $gs)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="p-3 font-bold text-gray-900 dark:text-white">{{ $gs['group_name'] }}</td>
                            <td class="p-3 text-gray-600 dark:text-gray-400">{{ $gs['subject'] }}</td>
                            <td class="p-3">{{ number_format($gs['monthly_fee'], 2) }} ج.م</td>
                            <td class="p-3 text-rose-600 font-bold">{{ number_format($gs['total_due'], 2) }} ج.م</td>
                            <td class="p-3 text-emerald-600 font-bold">{{ number_format($gs['total_paid'], 2) }} ج.م</td>
                            <td class="p-3 font-bold {{ $gs['balance'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ number_format(abs($gs['balance']), 2) }} ج.م
                                {{ $gs['balance'] >= 0 ? '✅' : '❌' }}
                            </td>
                            <td class="p-3">
                                @if($gs['status'] === 'active')
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300 rounded-full text-[10px] font-bold">مستمر</span>
                                @elseif($gs['status'] === 'withdrawn')
                                    <span class="px-2 py-1 bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-300 rounded-full text-[10px] font-bold">منسحب</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-full text-[10px] font-bold">{{ $gs['status'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">لا توجد مجموعات مسجلة لهذا الطالب</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── السجل الزمني (Timeline) ─── --}}
    <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
        <h3 class="font-bold text-base text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <x-heroicon-o-clock class="w-5 h-5 text-purple-500" />
            السجل الزمني لكشف الحساب
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-bold text-gray-500 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="p-3">التاريخ</th>
                        <th class="p-3">البيان</th>
                        <th class="p-3 text-rose-600">مدين (مستحق)</th>
                        <th class="p-3 text-emerald-600">دائن (مدفوع)</th>
                        <th class="p-3">الرصيد التراكمي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs font-medium">
                    @forelse($data['timeline'] ?? [] as $entry)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $entry['type'] === 'payment' ? 'bg-emerald-50/30 dark:bg-emerald-950/10' : '' }}">
                            <td class="p-3 text-gray-600 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($entry['date'])->format('Y-m-d') }}
                            </td>
                            <td class="p-3 font-semibold text-gray-900 dark:text-white">
                                @if($entry['type'] === 'payment')
                                    <span class="text-emerald-600">💰</span>
                                @else
                                    <span class="text-rose-500">📋</span>
                                @endif
                                {{ $entry['description'] }}
                            </td>
                            <td class="p-3 text-rose-600 font-bold">
                                {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) . ' ج.م' : '—' }}
                            </td>
                            <td class="p-3 text-emerald-600 font-bold">
                                {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) . ' ج.م' : '—' }}
                            </td>
                            <td class="p-3 font-extrabold {{ $entry['running_balance'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ number_format(abs($entry['running_balance']), 2) }} ج.م
                                <span class="text-[10px] font-normal">{{ $entry['running_balance'] >= 0 ? 'دائن' : 'مدين' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">لا توجد حركات مالية مسجلة لهذا الطالب</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
