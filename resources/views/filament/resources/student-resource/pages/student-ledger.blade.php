<x-filament-panels::page>
    @php
        $data = $this->ledger;
        $balance = $data['balance'] ?? 0;
    @endphp

    {{-- ─── كروت الملخص المالي ─── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-900/90 border border-gray-800 p-5 rounded-2xl shadow-lg text-center backdrop-blur-sm relative overflow-hidden">
            <div class="text-xs font-bold text-gray-400 mb-2">إجمالي المستحقات</div>
            <div class="text-3xl font-black text-rose-400">{{ number_format($data['total_due'], 2) }} <span class="text-sm font-normal text-gray-400">ج.م</span></div>
            <div class="text-[11px] text-gray-400 mt-1">بناءً على الاشتراكات الشهرية</div>
        </div>

        <div class="bg-gray-900/90 border border-gray-800 p-5 rounded-2xl shadow-lg text-center backdrop-blur-sm relative overflow-hidden">
            <div class="text-xs font-bold text-gray-400 mb-2">إجمالي المدفوع</div>
            <div class="text-3xl font-black text-emerald-400">{{ number_format($data['total_paid'], 2) }} <span class="text-sm font-normal text-gray-400">ج.م</span></div>
            <div class="text-[11px] text-gray-400 mt-1">من سجل المدفوعات الفعلي</div>
        </div>

        <div class="bg-gray-900/90 border {{ $balance >= 0 ? 'border-emerald-800/80' : 'border-rose-800/80' }} p-5 rounded-2xl shadow-lg text-center backdrop-blur-sm relative overflow-hidden">
            <div class="text-xs font-bold text-gray-400 mb-2">{{ $balance >= 0 ? 'رصيد زائد (دائن)' : 'مديونية متبقية (مدين)' }}</div>
            <div class="text-3xl font-black {{ $balance >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ number_format(abs($balance), 2) }} <span class="text-sm font-normal text-gray-400">ج.م</span>
            </div>
            <div class="text-[11px] text-gray-400 mt-1">المدفوع {{ $balance >= 0 ? 'أكثر من' : 'أقل من' }} المستحق</div>
        </div>
    </div>

    {{-- ─── تفاصيل الاشتراكات حسب المجموعة ─── --}}
    <div class="bg-gray-900/90 border border-gray-800 p-6 rounded-2xl shadow-xl mb-6">
        <h3 class="font-black text-base text-white mb-4 flex items-center gap-2">
            <span class="text-primary-400 text-lg">📚</span>
            تفاصيل الاشتراكات حسب المجموعة
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-950/80 text-xs font-bold text-gray-300 border-b border-gray-800">
                    <tr>
                        <th class="p-3.5">المجموعة</th>
                        <th class="p-3.5">المادة</th>
                        <th class="p-3.5">الرسم الشهري</th>
                        <th class="p-3.5 text-rose-400">إجمالي المستحق</th>
                        <th class="p-3.5 text-emerald-400">إجمالي المدفوع</th>
                        <th class="p-3.5">الرصيد</th>
                        <th class="p-3.5">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60 text-xs font-medium">
                    @forelse($data['group_summaries'] ?? [] as $gs)
                        <tr class="hover:bg-gray-800/40 transition">
                            <td class="p-3.5 font-bold text-white">{{ $gs['group_name'] }}</td>
                            <td class="p-3.5 text-gray-300">{{ $gs['subject'] }}</td>
                            <td class="p-3.5 text-gray-200 font-semibold">{{ number_format($gs['monthly_fee'], 2) }} ج.م</td>
                            <td class="p-3.5 text-rose-400 font-bold">{{ number_format($gs['total_due'], 2) }} ج.م</td>
                            <td class="p-3.5 text-emerald-400 font-bold">{{ number_format($gs['total_paid'], 2) }} ج.م</td>
                            <td class="p-3.5 font-black {{ $gs['balance'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ number_format(abs($gs['balance']), 2) }} ج.م
                                {{ $gs['balance'] >= 0 ? '✅' : '❌' }}
                            </td>
                            <td class="p-3.5">
                                @if($gs['status'] === 'active')
                                    <span class="px-2.5 py-1 bg-emerald-950 text-emerald-300 border border-emerald-800/60 rounded-full text-[10px] font-bold">مستمر</span>
                                @elseif($gs['status'] === 'withdrawn')
                                    <span class="px-2.5 py-1 bg-rose-950 text-rose-300 border border-rose-800/60 rounded-full text-[10px] font-bold">منسحب</span>
                                @else
                                    <span class="px-2.5 py-1 bg-gray-800 text-gray-300 border border-gray-700 rounded-full text-[10px] font-bold">{{ $gs['status'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-400 font-semibold">لا توجد مجموعات مسجلة لهذا الطالب</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── السجل الزمني (Timeline) ─── --}}
    <div class="bg-gray-900/90 border border-gray-800 p-6 rounded-2xl shadow-xl">
        <h3 class="font-black text-base text-white mb-4 flex items-center gap-2">
            <span class="text-purple-400 text-lg">🕒</span>
            السجل الزمني لكشف الحساب
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-950/80 text-xs font-bold text-gray-300 border-b border-gray-800">
                    <tr>
                        <th class="p-3.5">التاريخ</th>
                        <th class="p-3.5">البيان</th>
                        <th class="p-3.5 text-rose-400">مدين (مستحق)</th>
                        <th class="p-3.5 text-emerald-400">دائن (مدفوع)</th>
                        <th class="p-3.5">الرصيد التراكمي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60 text-xs font-medium">
                    @forelse($data['timeline'] ?? [] as $entry)
                        <tr class="hover:bg-gray-800/40 transition {{ $entry['type'] === 'payment' ? 'bg-gray-950/40' : 'bg-transparent' }}">
                            <td class="p-3.5 text-gray-300 font-mono text-xs">
                                {{ \Carbon\Carbon::parse($entry['date'])->format('Y-m-d') }}
                            </td>
                            <td class="p-3.5 font-bold text-white">
                                @if($entry['type'] === 'payment')
                                    <span class="text-emerald-400 ml-1">💰</span>
                                @else
                                    <span class="text-rose-400 ml-1">📋</span>
                                @endif
                                <span class="{{ $entry['type'] === 'payment' ? 'text-amber-300' : 'text-gray-100' }}">
                                    {{ $entry['description'] }}
                                </span>
                            </td>
                            <td class="p-3.5 text-rose-400 font-bold">
                                {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) . ' ج.م' : '—' }}
                            </td>
                            <td class="p-3.5 text-emerald-400 font-bold">
                                {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) . ' ج.م' : '—' }}
                            </td>
                            <td class="p-3.5 font-black {{ $entry['running_balance'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ number_format(abs($entry['running_balance']), 2) }} ج.م
                                <span class="text-[10px] font-normal text-gray-400">({{ $entry['running_balance'] >= 0 ? 'دائن' : 'مدين' }})</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-400 font-semibold">لا توجد حركات مالية مسجلة لهذا الطالب</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
