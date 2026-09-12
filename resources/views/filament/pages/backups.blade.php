<x-filament-panels::page>
    <style>
        .bk-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .bk-table-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .bk-table-header {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dark .bk-table-header {
            background: #1f2937;
            border-bottom-color: #374151;
        }

        .bk-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.84rem;
        }

        .bk-table th {
            padding: 0.85rem 1.1rem;
            background: #f1f5f9;
            color: #334155;
            font-weight: 800;
            font-size: 0.76rem;
            border-bottom: 1.5px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .bk-table th {
            background: #182234;
            color: #cbd5e1;
            border-bottom-color: #374151;
        }

        .bk-table td {
            padding: 0.85rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            white-space: nowrap;
        }
        .dark .bk-table td {
            border-bottom-color: #1f2937;
            color: #e2e8f0;
        }

        .bk-table tr:hover td {
            background: rgba(241, 245, 249, 0.7);
        }
        .dark .bk-table tr:hover td {
            background: rgba(31, 41, 55, 0.6);
        }
    </style>

    <div class="bk-table-card">
        <div class="bk-table-header">
            <h3 style="font-weight: 900; font-size: 0.98rem; margin: 0; display: flex; align-items: center; gap: 0.5rem; color: #0f172a;" class="dark:text-white">
                <x-heroicon-o-server-stack style="width: 1.35rem; height: 1.35rem; color: #059669;" />
                <span>سجل ملفات النسخ الاحتياطي المحفوظة</span>
            </h3>
            <span style="font-size: 0.72rem; font-weight: 800; padding: 0.2rem 0.65rem; border-radius: 9999px; background: rgba(16, 185, 129, 0.12); color: #059669;">
                {{ count($backups) }} ملفات متوفرة
            </span>
        </div>

        @if(count($backups) === 0)
            <div style="padding: 2.5rem; text-align: center; color: #94a3b8; font-weight: 700;">
                لا توجد ملفات نسخ احتياطي حالياً. اضغط على "إنشاء نسخة احتياطية الآن" لبدء نسخة جديدة.
            </div>
        @else
            <div style="width: 100%; overflow-x: auto;">
                <table class="bk-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>اسم الملف</th>
                            <th>الحجم</th>
                            <th>تاريخ الإنشاء</th>
                            <th style="text-align: center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $index => $b)
                            <tr>
                                <td style="font-weight: 900; color: #94a3b8;">{{ $index + 1 }}</td>
                                <td style="font-family: monospace; font-weight: 800; color: #2563eb;" class="dark:text-blue-400 dir-ltr text-right">
                                    {{ $b['filename'] }}
                                </td>
                                <td style="font-family: monospace; font-weight: 800;">{{ $b['size'] }}</td>
                                <td style="font-family: monospace; color: #64748b;" class="dir-ltr text-right">{{ $b['created_at'] }}</td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                        <button wire:click="downloadBackup('{{ $b['filename'] }}')" type="button" 
                                                style="padding: 0.35rem 0.75rem; border-radius: 0.6rem; background: #059669; color: #ffffff; border: none; font-size: 0.72rem; font-weight: 800; cursor: pointer;">
                                            تحميل SQL
                                        </button>
                                        <button wire:click="deleteBackup('{{ $b['filename'] }}')" wire:confirm="هل أنت متأكد من حذف هذا الملف النهائي؟" type="button" 
                                                style="padding: 0.35rem 0.75rem; border-radius: 0.6rem; background: #dc2626; color: #ffffff; border: none; font-size: 0.72rem; font-weight: 800; cursor: pointer;">
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
