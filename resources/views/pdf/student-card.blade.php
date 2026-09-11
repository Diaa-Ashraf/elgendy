<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>بطاقة طالب — {{ $student->name }}</title>
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
            size: 85.6mm 54mm;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
            direction: rtl;
            background-color: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 30px;
        }

        /* ─── زر الطباعة ─── */
        .print-bar {
            max-width: 400px;
            width: 100%;
            background: linear-gradient(135deg, #1e293b, #334155);
            padding: 14px 20px;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .print-bar span {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 700;
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
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
        }

        /* ─── الكارنيه ─── */
        .card {
            width: 340px;
            height: 214px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #0f172a 100%);
            color: #ffffff;
            border-radius: 14px;
            padding: 0;
            box-shadow: 0 20px 50px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Decorative gradient overlay */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #7c3aed, #06b6d4);
        }

        /* Subtle pattern overlay */
        .card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 90% 10%, rgba(37, 99, 235, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 10% 90%, rgba(124, 58, 237, 0.06) 0%, transparent 50%);
            pointer-events: none;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px 6px;
            position: relative;
            z-index: 1;
        }

        .card-header-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-logo {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            object-fit: contain;
            background: rgba(255,255,255,0.1);
            padding: 2px;
        }

        .card-header h2 {
            margin: 0;
            font-size: 11px;
            font-weight: 900;
            color: #e2e8f0;
            letter-spacing: 0.3px;
        }

        .card-badge {
            font-size: 8px;
            font-weight: 800;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            padding: 3px 8px;
            border-radius: 6px;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 14px;
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .info {
            flex: 1;
        }

        .info h3 {
            margin: 0 0 6px;
            font-size: 15px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.2;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 4px;
            margin: 3px 0;
        }

        .info-label {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
        }

        .info-value {
            font-size: 10px;
            font-weight: 800;
            color: #cbd5e1;
        }

        .qr-box {
            background: #ffffff;
            padding: 6px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .qr-box img {
            width: 72px;
            height: 72px;
            display: block;
        }

        .qr-label {
            font-size: 7px;
            color: #64748b;
            font-weight: 700;
            margin-top: 2px;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8px;
            color: #475569;
            font-weight: 700;
            padding: 4px 14px 8px;
            border-top: 1px solid rgba(255,255,255,0.05);
            position: relative;
            z-index: 1;
        }

        .card-footer .qr-code-text {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            font-weight: 800;
            color: #94a3b8;
            letter-spacing: 1px;
            background: rgba(255,255,255,0.05);
            padding: 2px 6px;
            border-radius: 4px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                min-height: auto;
            }
            .print-bar { display: none !important; }
            .card {
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="print-bar no-print">
        <span>🎴 معاينة كارنيه الطالب</span>
        <button class="print-btn" onclick="window.print()">🖨️ طباعة الكارنيه</button>
    </div>

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
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($student->qr_code) }}&bgcolor=ffffff&color=0f172a" alt="QR Code">
                <div class="qr-label">امسح للحضور</div>
            </div>
        </div>

        <div class="card-footer">
            <span class="qr-code-text">{{ $student->qr_code }}</span>
            <span>العام الدراسي {{ app(\App\Services\SettingService::class)->get('academic_year', date('Y')) }}</span>
        </div>
    </div>

</body>
</html>
