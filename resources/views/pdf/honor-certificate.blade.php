<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>شهادة تقدير وتفوق - {{ $student->name }}</title>
    @php
        $settingService = app(\App\Services\SettingService::class);
        $centerName = $settingService->get('center_name', 'سنتر الأستاذ محمد الجندي التعليمي');
        $teacherName = $settingService->get('teacher_name', 'الأستاذ محمد الجندي');
        $teacherSubject = $settingService->get('teacher_subject', 'المادة الأكاديمية التخصصية');
        $academicYear = $settingService->get('academic_year', '2026/2027');
        $logoUrl = $settingService->url('center_logo');
        $faviconUrl = $settingService->url('site_favicon');
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=Cairo:wght@400;600;700;800;900&family=Reem+Kufi:wght@700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        @media print {
            html, body {
                width: 297mm !important;
                height: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                overflow: hidden !important;
            }
            .print-floating-bar {
                display: none !important;
            }
            .cert-outer-wrapper {
                width: 297mm !important;
                height: 210mm !important;
                max-width: 297mm !important;
                max-height: 210mm !important;
                margin: 0 !important;
                padding: 8mm 12mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                overflow: hidden !important;
            }
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Cairo', sans-serif;
            background: #0b1120;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .cert-outer-wrapper {
            width: 297mm;
            height: 210mm;
            background: #ffffff;
            position: relative;
            box-sizing: border-box;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            padding: 8mm 12mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            border-radius: 4px;
        }
        
        /* الإطار الخارجي والزخارف الذهبية الكلاسيكية */
        .cert-border-outer {
            position: absolute;
            top: 5mm;
            left: 5mm;
            right: 5mm;
            bottom: 5mm;
            border: 3.5px solid #b45309;
            border-radius: 6px;
            pointer-events: none;
        }
        .cert-border-inner {
            position: absolute;
            top: 7mm;
            left: 7mm;
            right: 7mm;
            bottom: 7mm;
            border: 1.5px dashed #d97706;
            border-radius: 4px;
            pointer-events: none;
        }
        
        /* زخارف الأركان الفاخرة */
        .corner-ornament {
            position: absolute;
            width: 35px;
            height: 35px;
            border-color: #b45309;
            border-style: solid;
            pointer-events: none;
        }
        .corner-tl { top: 8mm; left: 8mm; border-width: 4px 0 0 4px; }
        .corner-tr { top: 8mm; right: 8mm; border-width: 4px 4px 0 0; }
        .corner-bl { bottom: 8mm; left: 8mm; border-width: 0 0 4px 4px; }
        .corner-br { bottom: 8mm; right: 8mm; border-width: 0 4px 4px 0; }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 200px;
            color: rgba(217, 119, 6, 0.035);
            font-family: 'Amiri', serif;
            font-weight: 700;
            pointer-events: none;
            z-index: 1;
            white-space: nowrap;
            user-select: none;
        }

        /* الرأس */
        .cert-header {
            text-align: center;
            position: relative;
            z-index: 10;
            padding-top: 2mm;
        }
        .cert-logo-box {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 2px;
        }
        .cert-logo {
            max-height: 48px;
            max-width: 110px;
            object-fit: contain;
        }
        .cert-institution {
            font-size: 15px;
            font-weight: 800;
            color: #92400e;
            letter-spacing: 0.5px;
        }
        .cert-main-title {
            font-family: 'Amiri', serif;
            font-size: 38px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 2px;
            letter-spacing: 1px;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.08);
            line-height: 1.1;
        }
        .cert-subtitle {
            font-size: 13px;
            color: #64748b;
            font-weight: 700;
        }

        /* متن الشهادة */
        .cert-body {
            text-align: center;
            padding: 0 15mm;
            position: relative;
            z-index: 10;
        }
        .cert-intro {
            font-size: 15px;
            color: #475569;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .student-name-container {
            display: inline-block;
            margin: 4px 0 8px;
            padding: 2px 35px;
            border-bottom: 3px double #d97706;
            background: linear-gradient(180deg, rgba(254, 243, 199, 0.4) 0%, rgba(255, 255, 255, 0) 100%);
            border-radius: 8px 8px 0 0;
        }
        .student-name {
            font-family: 'Amiri', serif;
            font-size: 34px;
            font-weight: 900;
            color: #065f46;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .cert-description {
            font-size: 14px;
            line-height: 1.7;
            color: #334155;
            font-weight: 600;
            max-width: 720px;
            margin: 0 auto;
        }
        .cert-description strong {
            color: #0f172a;
            font-weight: 800;
        }
        .rank-badge-box {
            margin-top: 6px;
        }
        .rank-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #78350f;
            padding: 4px 20px;
            border-radius: 9999px;
            font-weight: 900;
            font-size: 14px;
            border: 1.5px solid #f59e0b;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.15);
        }

        /* ذيل الشهادة والتوقيعات */
        .cert-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 0 12mm 2mm;
            position: relative;
            z-index: 10;
        }
        .footer-sign-block {
            text-align: center;
            min-width: 150px;
        }
        .sign-title {
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 18px;
        }
        .sign-name {
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
            border-top: 1.5px dashed #cbd5e1;
            padding-top: 4px;
        }

        /* الختم الذهبي الملكي */
        .seal-wrapper {
            text-align: center;
        }
        .royal-seal {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: radial-gradient(circle, #fde047 15%, #d97706 85%, #92400e 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #451a03;
            font-weight: 900;
            font-size: 10px;
            box-shadow: 0 4px 14px rgba(180, 83, 9, 0.3);
            border: 2.5px solid #ffffff;
            outline: 2px dashed #b45309;
            user-select: none;
        }

        /* زر الطباعة العائم */
        .print-floating-bar {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 9999;
            display: flex;
            gap: 10px;
        }
        .btn-print {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-family: 'Cairo', sans-serif;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-print:hover {
            transform: scale(1.05);
        }
        .btn-close {
            background: #334155;
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
    </style>
</head>
<body>

    <div class="print-floating-bar">
        <button class="btn-print" onclick="window.print()">🖨️ طباعة الشهادة التقديرية الفاخرة</button>
        <button class="btn-close" onclick="window.close()">إغلاق النافذة</button>
    </div>

    <div class="cert-outer-wrapper">

        <!-- الإطارات والزخارف الذهبية الكلاسيكية -->
        <div class="cert-border-outer"></div>
        <div class="cert-border-inner"></div>
        <div class="corner-ornament corner-tl"></div>
        <div class="corner-ornament corner-tr"></div>
        <div class="corner-ornament corner-bl"></div>
        <div class="corner-ornament corner-br"></div>
        <div class="watermark">تفوق</div>

        <!-- رأس الشهادة -->
        <div class="cert-header">
            <div class="cert-logo-box">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" class="cert-logo" alt="Logo">
                @endif
                <div class="cert-institution">{{ $centerName }}</div>
            </div>
            <h1 class="cert-main-title">شهادة شكر وتقدير وتفوق</h1>
            <div class="cert-subtitle">لوحة الشرف للأوائل والمتميزين — العام الدراسي {{ $academicYear }}</div>
        </div>

        <!-- متن الشهادة -->
        <div class="cert-body">
            <div class="cert-intro">
                تتشرف إدارة المنظومة التعليمية تحت إشراف الأستاذ <strong>{{ $teacherName }}</strong> بمنح هذه الشهادة للطالب المتميز:
            </div>
            
            <div class="student-name-container">
                <span class="student-name">{{ $student->name }}</span>
            </div>

            <div class="cert-description">
                تقديراً لاجتهاده وتفوقه الأكاديمي الباهر وحصوله على أعلى الدرجات والالتزام الكامل بالحضور والمواظبة في مادة <strong>{{ $teacherSubject }}</strong> بالمرحلة الدراسية (<strong>{{ $student->educationalStage?->name ?? 'المرحلة العامة' }}</strong>).
            </div>

            @if(isset($rankBadge))
                <div class="rank-badge-box">
                    <span class="rank-badge-pill">
                        <span></span>
                        <span>{{ $rankBadge }}</span>
                    </span>
                </div>
            @endif
        </div>

        <!-- ذيل الشهادة والتوقيعات والختم الملكي -->
        <div class="cert-footer">
            <div class="footer-sign-block">
                <div class="sign-title">المستشار والمشرف التربوي</div>
                <div class="sign-name">{{ $teacherName }}</div>
            </div>

            <div class="seal-wrapper">
                <div class="royal-seal">
                    <span style="font-size: 16px;"></span>
                    <span style="letter-spacing: 0.5px;">تفوق أكاديمي</span>
                    <span style="font-size: 9px; opacity: 0.85;">معتمد رسمياً</span>
                </div>
            </div>

            <div class="footer-sign-block">
                <div class="sign-title">إدارة السنتر والمنظومة</div>
                <div class="sign-name">{{ date('Y/m/d') }}</div>
            </div>
        </div>
    </div>

</body>
</html>
