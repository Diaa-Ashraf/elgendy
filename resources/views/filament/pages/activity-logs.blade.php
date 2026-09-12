<x-filament-panels::page>
    @php
        $logs = $this->getLogs();
    @endphp

    <style>
        .al-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .al-table-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .al-table-header {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dark .al-table-header {
            background: #1f2937;
            border-bottom-color: #374151;
        }

        .al-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.84rem;
        }

        .al-table th {
            padding: 0.85rem 1.1rem;
            background: #f1f5f9;
            color: #334155;
            font-weight: 800;
            font-size: 0.76rem;
            border-bottom: 1.5px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .al-table th {
            background: #182234;
            color: #cbd5e1;
            border-bottom-color: #374151;
        }

        .al-table td {
            padding: 0.85rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            white-space: nowrap;
        }
        .dark .al-table td {
            border-bottom-color: #1f2937;
            color: #e2e8f0;
        }

        .al-table tr:hover td {
            background: rgba(241, 245, 249, 0.7);
        }
        .dark .al-table tr:hover td {
            background: rgba(31, 41, 55, 0.6);
        }
    </style>

    <div class="al-table-card">
        <div class="al-table-header">
            <h3 style="font-weight: 900; font-size: 0.98rem; margin: 0; display: flex; align-items: center; gap: 0.5rem; color: #0f172a;" class="dark:text-white">
                <x-heroicon-o-finger-print style="width: 1.35rem; height: 1.35rem; color: #9333ea;" />
                <span>سجل تتبع التعديلات والعمليات آلياً</span>
            </h3>
            <span style="font-size: 0.72rem; font-weight: 800; padding: 0.2rem 0.65rem; border-radius: 9999px; background: rgba(147, 51, 234, 0.12); color: #9333ea;">
                تحديث لحظي
            </span>
        </div>

        <div style="width: 100%; overflow-x: auto;">
            <table class="al-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>المستخدم / الفاعل</th>
                        <th>نوع العملية</th>
                        <th>الوصف</th>
                        <th>تاريخ ووقت الحركة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td style="color: #94a3b8; font-family: monospace;">#{{ $log->id }}</td>
                            <td style="font-weight: 900;">
                                {{ $log->causer?->name ?? ($log->causer?->email ?? 'النظام / تلقائي') }}
                            </td>
                            <td>
                                @if($log->event === 'created')
                                    <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 0.5rem; font-size: 0.72rem; font-weight: 800; background: rgba(16, 185, 129, 0.15); color: #059669;">إضافة ➕</span>
                                @elseif($log->event === 'updated')
                                    <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 0.5rem; font-size: 0.72rem; font-weight: 800; background: rgba(245, 158, 11, 0.15); color: #d97706;">تعديل ✏️</span>
                                @elseif($log->event === 'deleted')
                                    <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 0.5rem; font-size: 0.72rem; font-weight: 800; background: rgba(239, 68, 68, 0.15); color: #dc2626;">حذف 🗑️</span>
                                @else
                                    <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 0.5rem; font-size: 0.72rem; font-weight: 800; background: rgba(37, 99, 235, 0.15); color: #2563eb;">{{ $log->event ?? 'عملية' }}</span>
                                @endif
                            </td>
                            <td style="color: #475569;" class="dark:text-slate-300">
                                {{ $log->description }}
                            </td>
                            <td style="font-family: monospace; font-size: 0.78rem; color: #64748b;">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d h:i:s A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: #94a3b8;">لا توجد سجلات نشاط مسجلة حالياً</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 1rem 1.5rem; border-top: 1.5px solid #e2e8f0;" class="dark:border-gray-800">
            {{ $logs->links() }}
        </div>
    </div>
</x-filament-panels::page>
