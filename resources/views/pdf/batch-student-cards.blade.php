<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>طباعة كارنيهات — {{ $group->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
        $centerName = app(\App\Services\SettingService::class)->get('center_name', 'المنظومة التعليمية الذكية');
        $logoUrl = app(\App\Services\SettingService::class)->url('center_logo');
        $academicYear = app(\App\Services\SettingService::class)->get('academic_year', date('Y'));
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <style>
        @page {
            size: A4;
            margin: 8mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
            direction: rtl;
            background: #0f172a;
            margin: 0;
            padding: 20px;
        }

        /* ─── Print Bar ─── */
        .print-bar {
            max-width: 800px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #1e293b, #334155);
            padding: 14px 20px;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .print-bar-info {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 700;
        }
        .print-bar-info strong {
            color: #e2e8f0;
        }
        .print-btn {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
            transition: all 0.2s;
        }
        .print-btn:hover {
            transform: translateY(-1px);
        }

        /* ─── Cards Grid ─── */
        .cards-page {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 10mm;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6mm;
            min-height: 277mm;
        }

        /* ─── Individual Card ─── */
        .card {
            width: 100%;
            height: 54mm;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #0f172a 100%);
            color: #ffffff;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #2563eb, #7c3aed, #06b6d4);
        }
        .card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 90% 10%, rgba(37, 99, 235, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 10px 4px;
            position: relative;
            z-index: 1;
        }
        .card-header-right {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .card-logo {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            object-fit: contain;
        }
        .card-header h2 {
            margin: 0;
            font-size: 8px;
            font-weight: 900;
            color: #e2e8f0;
        }
        .card-badge {
            font-size: 6px;
            font-weight: 800;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            padding: 2px 6px;
            border-radius: 4px;
            color: #fff;
        }

        .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 10px;
            flex: 1;
            position: relative;
            z-index: 1;
        }
        .info h3 {
            margin: 0 0 3px;
            font-size: 11px;
            font-weight: 900;
            color: #ffffff;
        }
        .info-row {
            display: flex;
            align-items: center;
            gap: 3px;
            margin: 1px 0;
        }
        .info-label {
            font-size: 7px;
            font-weight: 700;
            color: #64748b;
        }
        .info-value {
            font-size: 8px;
            font-weight: 800;
            color: #cbd5e1;
        }

        .qr-box {
            background: #ffffff;
            padding: 4px;
            border-radius: 6px;
            text-align: center;
        }
        .qr-box img {
            width: 55px;
            height: 55px;
            display: block;
        }
        .qr-label {
            font-size: 5px;
            color: #64748b;
            font-weight: 700;
            margin-top: 1px;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 6px;
            color: #475569;
            font-weight: 700;
            padding: 2px 10px 5px;
            border-top: 1px solid rgba(255,255,255,0.05);
            position: relative;
            z-index: 1;
        }
        .qr-code-text {
            font-family: 'Courier New', monospace;
            font-size: 7px;
            font-weight: 800;
            color: #94a3b8;
            letter-spacing: 0.5px;
            background: rgba(255,255,255,0.05);
            padding: 1px 4px;
            border-radius: 3px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .print-bar { display: none !important; }
            .cards-page {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: none;
            }
        }
    </style>
</head>
<body>

    <div class="print-bar">
        <div class="print-bar-info">
            🎴 طباعة جماعية — <strong>{{ $group->name }}</strong> ({{ $students->count() }} طالب)
        </div>
        <button class="print-btn" onclick="window.print()">🖨️ طباعة الكارنيهات</button>
    </div>

    @foreach($students->chunk(8) as $page)
    <div class="cards-page">
        @foreach($page as $student)
        <div class="card">
            <div class="card-header">
                <div class="card-header-right">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo" class="card-logo">
                    @endif
                    <h2>{{ $centerName }}</h2>
                </div>
                <span class="card-badge">بطاقة طالب</span>
            </div>

            <div class="card-body">
                <div class="info">
                    <h3>{{ $student->name }}</h3>
                    <div class="info-row">
                        <span class="info-label">المرحلة:</span>
                        <span class="info-value">{{ $student->educationalStage?->name ?? 'عام' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">الكود:</span>
                        <span class="info-value">#{{ $student->id }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">الهاتف:</span>
                        <span class="info-value">{{ $student->parent_phone }}</span>
                    </div>
                </div>
                <div class="qr-box">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($student->qr_code) }}&bgcolor=ffffff&color=0f172a" alt="QR">
                    <div class="qr-label">امسح للحضور</div>
                </div>
            </div>

            <div class="card-footer">
                <span class="qr-code-text">{{ $student->qr_code }}</span>
                <span>{{ $academicYear }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach

</body>
</html>
