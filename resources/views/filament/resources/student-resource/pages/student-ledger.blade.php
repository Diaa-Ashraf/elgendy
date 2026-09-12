<x-filament-panels::page>
    @php
        $data = $this->ledger;
        $balance = $data['balance'] ?? 0;
    @endphp

    <style>
        .sl-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        .sl-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .sl-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.25rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            text-align: center;
            transition: all 0.2s ease;
        }
        .dark .sl-kpi-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
        }
        .sl-kpi-card:hover {
            transform: translateY(-2px);
        }

        .sl-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .sl-table-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .sl-table-header {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dark .sl-table-header {
            background: #1f2937;
            border-bottom-color: #374151;
        }

        .sl-table-h3 {
            font-size: 0.98rem;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .sl-table-h3 {
            color: #f8fafc;
        }

        .sl-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.84rem;
        }

        .sl-table th {
            padding: 0.85rem 1.1rem;
            background: #f1f5f9;
            color: #334155;
            font-weight: 800;
            font-size: 0.76rem;
            border-bottom: 1.5px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .sl-table th {
            background: #182234;
            color: #cbd5e1;
            border-bottom-color: #374151;
        }

        .sl-table td {
            padding: 0.85rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            white-space: nowrap;
        }
        .dark .sl-table td {
            border-bottom-color: #1f2937;
            color: #e2e8f0;
        }

        .sl-table tr:hover td {
            background: rgba(241, 245, 249, 0.7);
        }
        .dark .sl-table tr:hover td {
            background: rgba(31, 41, 55, 0.6);
        }
    </style>

    <div class="sl-container">
        {{-- ─── كروت الملخص المالي ─── --}}
        <div class="sl-kpi-grid">
            <div class="sl-kpi-card">
                <div style="font-size: 0.78rem; font-weight: 800; color: #64748b; margin-bottom: 0.4rem;">إجمالي المستحقات</div>
                <div style="font-size: 1.85rem; font-weight: 900; font-family: monospace; color: #dc2626;">{{ number_format($data['total_due'], 2) }} <span style="font-size: 0.75rem;">ج.م</span></div>
                <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; margin-top: 0.3rem;">بناءً على الاشتراكات الشهرية</div>
            </div>

            <div class="sl-kpi-card">
                <div style="font-size: 0.78rem; font-weight: 800; color: #64748b; margin-bottom: 0.4rem;">إجمالي المدفوع</div>
                <div style="font-size: 1.85rem; font-weight: 900; font-family: monospace; color: #059669;">{{ number_format($data['total_paid'], 2) }} <span style="font-size: 0.75rem;">ج.م</span></div>
                <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; margin-top: 0.3rem;">من سجل المدفوعات الفعلي</div>
            </div>

            <div class="sl-kpi-card" style="border: 1.5px solid {{ $balance >= 0 ? '#10b981' : '#f43f5e' }};">
                <div style="font-size: 0.78rem; font-weight: 800; color: #64748b; margin-bottom: 0.4rem;">{{ $balance >= 0 ? 'رصيد زائد (دائن)' : 'مديونية متبقية (مدين)' }}</div>
                <div style="font-size: 1.85rem; font-weight: 900; font-family: monospace; color: {{ $balance >= 0 ? '#059669' : '#dc2626' }};">
                    {{ number_format(abs($balance), 2) }} <span style="font-size: 0.75rem;">ج.م</span>
                </div>
                <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; margin-top: 0.3rem;">المدفوع {{ $balance >= 0 ? 'أكثر من' : 'أقل من' }} المستحق</div>
            </div>
        </div>

        {{-- ─── تفاصيل الاشتراكات حسب المجموعة ─── --}}
        <div class="sl-table-card">
            <div class="sl-table-header">
                <h3 class="sl-table-h3">
                    <x-heroicon-o-academic-cap style="width: 1.35rem; height: 1.35rem; color: #f59e0b;" />
                    <span>تفاصيل الاشتراكات حسب المجموعة الدراسية</span>
                </h3>
            </div>

            <div style="width: 100%; overflow-x: auto;">
                <table class="sl-table">
                    <thead>
                        <tr>
                            <th>المجموعة</th>
                            <th>المادة الدراسية</th>
                            <th>الرسم الشهري</th>
                            <th style="color: #dc2626;">إجمالي المستحق</th>
                            <th style="color: #059669;">إجمالي المدفوع</th>
                            <th>الرصيد</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['group_summaries'] ?? [] as $gs)
                            <tr>
                                <td style="font-weight: 900;">{{ $gs['group_name'] }}</td>
                                <td style="color: #64748b;">{{ $gs['subject'] }}</td>
                                <td style="font-family: monospace;">{{ number_format($gs['monthly_fee'], 2) }} ج.م</td>
                                <td style="font-family: monospace; font-weight: 900; color: #dc2626;">{{ number_format($gs['total_due'], 2) }} ج.م</td>
                                <td style="font-family: monospace; font-weight: 900; color: #059669;">{{ number_format($gs['total_paid'], 2) }} ج.م</td>
                                <td style="font-family: monospace; font-weight: 900; color: {{ $gs['balance'] >= 0 ? '#059669' : '#dc2626' }};">
                                    {{ number_format(abs($gs['balance']), 2) }} ج.م
                                    {{ $gs['balance'] >= 0 ? '✅' : '❌' }}
                                </td>
                                <td>
                                    @if($gs['status'] === 'active')
                                        <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.72rem; font-weight: 800; background: rgba(16, 185, 129, 0.15); color: #059669;">مستمر</span>
                                    @elseif($gs['status'] === 'withdrawn')
                                        <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.72rem; font-weight: 800; background: rgba(239, 68, 68, 0.15); color: #dc2626;">منسحب</span>
                                    @else
                                        <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.72rem; font-weight: 800; background: #e2e8f0; color: #475569;">{{ $gs['status'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: #94a3b8;">لا توجد مجموعات مسجلة لهذا الطالب</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── السجل الزمني لكشف الحساب ─── --}}
        <div class="sl-table-card">
            <div class="sl-table-header">
                <h3 class="sl-table-h3">
                    <x-heroicon-o-clock style="width: 1.35rem; height: 1.35rem; color: #9333ea;" />
                    <span>السجل الزمني لكشف الحساب</span>
                </h3>
            </div>

            <div style="width: 100%; overflow-x: auto;">
                <table class="sl-table">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>البيان والوصف</th>
                            <th style="color: #dc2626;">مدين (مستحق)</th>
                            <th style="color: #059669;">دائن (مدفوع)</th>
                            <th>الرصيد التراكمي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['timeline'] ?? [] as $entry)
                            <tr>
                                <td style="font-family: monospace; color: #64748b;">{{ $entry['date'] }}</td>
                                <td style="font-weight: 800;">{{ $entry['description'] }}</td>
                                <td style="font-family: monospace; font-weight: 900; color: #dc2626;">
                                    {{ $entry['due'] > 0 ? number_format($entry['due'], 2) . ' ج.م' : '—' }}
                                </td>
                                <td style="font-family: monospace; font-weight: 900; color: #059669;">
                                    {{ $entry['paid'] > 0 ? number_format($entry['paid'], 2) . ' ج.م' : '—' }}
                                </td>
                                <td style="font-family: monospace; font-weight: 900; color: {{ $entry['running_balance'] >= 0 ? '#059669' : '#dc2626' }};">
                                    {{ number_format(abs($entry['running_balance']), 2) }} ج.م
                                    {{ $entry['running_balance'] >= 0 ? '(دائن)' : '(مدين)' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: #94a3b8;">لا توجد حركات مالية مسجلة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
