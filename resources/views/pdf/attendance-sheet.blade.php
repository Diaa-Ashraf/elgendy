<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>كشف حضور — {{ $session->group?->name }} — {{ \Carbon\Carbon::parse($session->date)->format('Y-m-d') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
        $centerName = app(\App\Services\SettingService::class)->get('center_name', 'المنظومة التعليمية الذكية');
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

        .container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        /* Print Bar */
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
        }

        /* Header */
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
        .header h1 {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            font-weight: 900;
        }
        .header p {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        /* Session Info */
        .session-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .session-info-item {
            font-size: 13px;
            font-weight: 600;
        }
        .session-info-item strong {
            color: #475569;
        }

        /* Stats */
        .stats-row {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        .stat-card {
            flex: 1;
            text-align: center;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .stat-card h4 {
            margin: 0 0 4px;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
        }
        .stat-card p {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
        }
        .stat-present { background: #f0fdf4; }
        .stat-present p { color: #16a34a; }
        .stat-absent { background: #fef2f2; }
        .stat-absent p { color: #dc2626; }
        .stat-total { background: #eff6ff; }
        .stat-total p { color: #2563eb; }
        .stat-rate { background: #faf5ff; }
        .stat-rate p { color: #7c3aed; }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            font-size: 12px;
            text-align: right;
            font-weight: 600;
        }
        .data-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 11px;
        }
        .data-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .status-present {
            color: #16a34a;
            font-weight: 800;
        }
        .status-absent {
            color: #dc2626;
            font-weight: 800;
        }

        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-weight: 600;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .print-bar { display: none !important; }
            .container { box-shadow: none; border: none; padding: 0; border-radius: 0; }
        }
    </style>
</head>
<body>

    <div class="print-bar">
        <div style="font-weight: 800; font-size: 14px;">
            📋 <strong>كشف حضور</strong> — {{ $session->group?->name }} — {{ \Carbon\Carbon::parse($session->date)->format('Y-m-d') }}
        </div>
        <button class="print-btn" onclick="window.print()">🖨️ طباعة</button>
    </div>

    <div class="container">
        <div class="header">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" class="header-logo">
            @endif
            <div>
                <h1>كشف حضور الحصة</h1>
                <p>{{ $centerName }}</p>
            </div>
        </div>

        <div class="session-info">
            <div class="session-info-item"><strong>المجموعة:</strong> {{ $session->group?->name }}</div>
            <div class="session-info-item"><strong>المادة:</strong> {{ $session->group?->subject?->name ?? 'عام' }}</div>
            <div class="session-info-item"><strong>التاريخ:</strong> {{ \Carbon\Carbon::parse($session->date)->format('Y-m-d') }}</div>
            <div class="session-info-item"><strong>العنوان:</strong> {{ $session->title ?? 'حصة عادية' }}</div>
            <div class="session-info-item"><strong>تاريخ الاستخراج:</strong> {{ now()->format('Y-m-d h:i A') }}</div>
        </div>

        <div class="stats-row">
            <div class="stat-card stat-present">
                <h4>حاضرين</h4>
                <p>{{ $stats['present'] }} ✅</p>
            </div>
            <div class="stat-card stat-absent">
                <h4>غايبين</h4>
                <p>{{ $stats['absent'] }} ❌</p>
            </div>
            <div class="stat-card stat-total">
                <h4>إجمالي الطلاب</h4>
                <p>{{ $stats['total'] }}</p>
            </div>
            <div class="stat-card stat-rate">
                <h4>نسبة الحضور</h4>
                <p>{{ $stats['rate'] }}%</p>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="6%">#</th>
                    <th width="34%">اسم الطالب</th>
                    <th width="18%">كود الطالب</th>
                    <th width="22%">هاتف ولي الأمر</th>
                    <th width="20%">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceData as $index => $entry)
                <tr>
                    <td style="text-align: center; font-weight: 800;">{{ $index + 1 }}</td>
                    <td style="font-weight: 800;">{{ $entry['name'] }}</td>
                    <td style="font-family: 'Courier New', monospace; font-size: 10px;">{{ $entry['code'] }}</td>
                    <td>{{ $entry['phone'] }}</td>
                    <td class="{{ $entry['status'] === 'present' ? 'status-present' : 'status-absent' }}" style="text-align: center;">
                        {{ $entry['status'] === 'present' ? '✅ حاضر' : '❌ غائب' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            تم استخراج هذا الكشف آلياً — {{ $centerName }} — {{ date('Y') }}
        </div>
    </div>

</body>
</html>
