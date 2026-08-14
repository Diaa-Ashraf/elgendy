<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>بطاقة طالب — {{ $student->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            direction: rtl;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-h: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card {
            width: 340px;
            height: 210px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 8px;
        }
        .card-header h2 {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 5px;
        }
        .info {
            flex: 1;
        }
        .info h3 {
            margin: 0 0 4px;
            font-size: 15px;
            font-weight: 800;
        }
        .info p {
            margin: 2px 0;
            font-size: 11px;
            opacity: 0.9;
        }
        .qr-box {
            background: #ffffff;
            padding: 6px;
            border-radius: 10px;
            text-align: center;
        }
        .qr-box img {
            width: 70px;
            height: 70px;
        }
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            opacity: 0.8;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 6px;
        }
        @media print {
            body { background: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="position: absolute; top: 20px; left: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
            🖨️ طباعة الكارنيه
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>نظام الأستاذ محمد الغندي التعليمي</h2>
            <span style="font-size: 10px; background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 4px;">بطاقة طالب</span>
        </div>

        <div class="card-body">
            <div class="info">
                <h3>{{ $student->name }}</h3>
                <p><strong>المرحلة:</strong> {{ $student->educationalStage?->name ?? 'عام' }}</p>
                <p><strong>الكود:</strong> #{{ $student->id }}</p>
                <p><strong>الهاتف:</strong> {{ $student->parent_phone }}</p>
            </div>

            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($student->qr_code) }}" alt="QR Code">
            </div>
        </div>

        <div class="card-footer">
            <span>رمز الـ QR: <strong>{{ $student->qr_code }}</strong></span>
            <span>عام {{ date('Y') }}</span>
        </div>
    </div>

</body>
</html>
