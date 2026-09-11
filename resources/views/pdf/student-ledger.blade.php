<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>كشف حساب طالب - {{ $student->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
        $centerName = app(\App\Services\SettingService::class)->get('center_name', 'المنظومة التعليمية الذكية');
        $centerPhone = app(\App\Services\SettingService::class)->get('center_phone', '');
        $logoUrl = app(\App\Services\SettingService::class)->url('center_logo');
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
            direction: rtl;
            text-align: right;
            padding: 20px;
            color: #0f172a;
            background: #f1f5f9;
            margin: 0;
        }

        .ledger-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        /* ─── Print Bar ─── */
        .print-bar {
            max-width: 850px;
            margin: 0 auto 16px;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);
        }
        .print-btn {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #ffffff;
            border: none;
            padding: 9px 22px;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-weight: 900;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            transition: all 0.2s;
        }
        .print-btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #6d28d9);
        }

        /* ─── Header ─── */
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }
        .header-logo {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: contain;
        }
        .header-text h1 {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            font-weight: 900;
        }
        .header-text p {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        /* ─── Info Box ─── */
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 5px 8px;
            font-size: 13px;
            font-weight: 600;
        }
        .info-box strong {
            color: #475569;
        }

        /* ─── Summary Cards ─── */
        .summary-cards {
            width: 100%;
            margin-bottom: 24px;
            border-spacing: 8px;
        }
        .summary-card {
            background: #f8fafc;
            padding: 14px;
            text-align: center;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .summary-card h4 {
            margin: 0 0 6px;
            font-size: 11px;
            color: #475569;
            font-weight: 700;
        }
        .summary-card p {
            margin: 0;
            font-size: 18px;
            font-weight: 900;
        }

        /* ─── Data Table ─── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #e2e8f0;
            padding: 9px 12px;
            font-size: 12px;
            text-align: right;
            font-weight: 600;
        }
        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 11px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tr:hover {
            background-color: #f1f5f9;
        }

        .section-title {
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
            margin: 0 0 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ─── Footer ─── */
        .footer {
            margin-top: 28px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            font-weight: 600;
        }
        .text-danger { color: #dc2626; font-weight: 800; }
        .text-success { color: #16a34a; font-weight: 800; }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .print-bar { display: none !important; }
            .ledger-container {
                box-shadow: none;
                border: none;
                padding: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

    <div class="print-bar">
        <div style="font-weight: 800; font-size: 14px;">
            📊 <strong>كشف حساب طالب تفصيلي</strong> — {{ $student->name }}
        </div>
        <button class="print-btn" onclick="window.print()">🖨️ طباعة أو حفظ PDF</button>
    </div>

    <div class="ledger-container">
        <div class="header">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" class="header-logo">
            @endif
            <div class="header-text">
                <h1>كشف حساب طالب تفصيلي</h1>
                <p>{{ $centerName }}</p>
            </div>
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

        <h3 class="section-title">📋 السجل الزمني لكشف الحساب</h3>
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
            تم استخراج هذا الكشف آلياً من نظام إدارة المراكز التعليمية — {{ $centerName }} — جميع الحقوق محفوظة {{ date('Y') }}
        </div>
    </div>

</body>
</html>
