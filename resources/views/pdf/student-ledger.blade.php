<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>كشف حساب طالب - {{ $student->name }}</title>
    <style>
        body {
            font-family: 'Xcharter', sans-serif, 'Segoe UI', Tahoma;
            direction: rtl;
            text-align: right;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1e3a8a;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 14px;
        }
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 4px;
            font-size: 13px;
        }
        .summary-cards {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            background: #f1f5f9;
            padding: 10px;
            text-align: center;
            border-radius: 6px;
        }
        .summary-card h4 {
            margin: 0 0 5px;
            font-size: 12px;
            color: #475569;
        }
        .summary-card p {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-size: 12px;
            text-align: right;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-success { color: #16a34a; font-weight: bold; }
    </style>
</head>
    @php
        $centerName = app(\App\Services\SettingService::class)->get('center_name', 'المنظومة التعليمية الذكية');
    @endphp

    <div class="header">
        <h1>كشف حساب طالب تفصيلي</h1>
        <p>{{ $centerName }}</p>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td><strong>اسم الطالب:</strong> {{ $student->name }}</td>
                <td><strong>المرحلة الدراسية:</strong> {{ $student->educationalStage?->name ?? 'غير محدد' }}</td>
            </tr>
            <tr>
                <td><strong>هاتف ولي الأمر:</strong> {{ $student->parent_phone }}</td>
                <td><strong>تاريخ استخراج التقرير:</strong> {{ now()->format('Y-m-d h:i A') }}</td>
            </tr>
        </table>
    </div>

    <table class="summary-cards">
        <tr>
            <td width="33%">
                <div class="summary-card">
                    <h4>إجمالي المستحق</h4>
                    <p class="text-danger">{{ number_format($ledger['total_due'], 2) }} ج.م</p>
                </div>
            </td>
            <td width="33%">
                <div class="summary-card">
                    <h4>إجمالي المدفوع</h4>
                    <p class="text-success">{{ number_format($ledger['total_paid'], 2) }} ج.م</p>
                </div>
            </td>
            <td width="33%">
                <div class="summary-card">
                    <h4>الرصيد المتبقي</h4>
                    <p class="{{ $ledger['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format(abs($ledger['balance']), 2) }} ج.م ({{ $ledger['balance'] >= 0 ? 'دائن' : 'مدين' }})
                    </p>
                </div>
            </td>
        </tr>
    </table>

    <h3>السجل الزمني لكشف الحساب</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">التاريخ</th>
                <th width="45%">البيان</th>
                <th width="13%">مستحق (مدين)</th>
                <th width="13%">مدفوع (دائن)</th>
                <th width="14%">الرصيد</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledger['timeline'] as $entry)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($entry['date'])->format('Y-m-d') }}</td>
                    <td>{{ $entry['description'] }}</td>
                    <td class="text-danger">{{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '—' }}</td>
                    <td class="text-success">{{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '—' }}</td>
                    <td>
                        {{ number_format(abs($entry['running_balance']), 2) }}
                        ({{ $entry['running_balance'] >= 0 ? 'دائن' : 'مدين' }})
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        تم استخراج هذا الكشف آلياً من نظام إدارة المراكز التعليمية — جميع الحقوق محفوظة {{ date('Y') }}
    </div>

</body>
</html>
