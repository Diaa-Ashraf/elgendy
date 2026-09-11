<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>شهادة تقدير وتفوق — {{ $student->name }}</title>
    @php
        $settingService = app(\App\Services\SettingService::class);
        $centerName = $settingService->get('center_name', 'سنتر الأستاذ محمد الجندي التعليمي');
        $teacherName = $settingService->get('teacher_name', 'الأستاذ محمد الجندي');
        $teacherSubject = $settingService->get('teacher_subject', 'المادة الأكاديمية التخصصية');
        $academicYear = $settingService->get('academic_year', '2026 / 2027');
        $logoUrl = $settingService->url('center_logo');
        $faviconUrl = $settingService->url('site_favicon');
        $serialNumber = 'CERT-' . date('Y') . '-' . str_pad($student->id, 5, '0', STR_PAD_LEFT);
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=Cairo:wght@400;600;700;800;900&family=Reem+Kufi:wght@600;700&display=swap" rel="stylesheet">
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
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-floating-bar {
                display: none !important;
            }
            .cert-canvas {
                width: 297mm !important;
                height: 210mm !important;
                max-width: 297mm !important;
                max-height: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
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
            padding: 25px 10px;
            font-family: 'Cairo', sans-serif;
            background: #0f172a;
            background-image: radial-gradient(rgba(217, 119, 6, 0.15) 1px, transparent 0);
            background-size: 24px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* حاوية الشهادة الرئيسية A4 Landscape */
        .cert-canvas {
            width: 297mm;
            height: 210mm;
            background: #fffdfa;
            position: relative;
            box-sizing: border-box;
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(217, 119, 6, 0.2);
            padding: 10mm 14mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            border-radius: 6px;
        }

        /* خلفية مائية وزخارف جيولوشيه راقية */
        .cert-bg-pattern {
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(217, 119, 6, 0.03) 0%, transparent 70%),
                linear-gradient(to right, rgba(217, 119, 6, 0.015) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(217, 119, 6, 0.015) 1px, transparent 1px);
            background-size: 100% 100%, 20px 20px, 20px 20px;
            pointer-events: none;
            z-index: 1;
        }

        .cert-watermark {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 160px;
            color: rgba(217, 119, 6, 0.03);
            font-family: 'Amiri', serif;
            font-weight: 700;
            pointer-events: none;
            z-index: 1;
            white-space: nowrap;
            user-select: none;
            letter-spacing: 12px;
        }

        /* إطارات الذهب الملكية */
        .gold-border-outer {
            position: absolute;
            inset: 4.5mm;
            border: 3.5px solid #b45309;
            border-radius: 8px;
            pointer-events: none;
            z-index: 2;
        }
        .gold-border-middle {
            position: absolute;
            inset: 6.5mm;
            border: 1px solid #d97706;
            border-radius: 6px;
            pointer-events: none;
            z-index: 2;
        }
        .gold-border-inner {
            position: absolute;
            inset: 8mm;
            border: 1.5px dashed #f59e0b;
            border-radius: 4px;
            pointer-events: none;
            z-index: 2;
        }

        /* زخارف الأركان الذهبية الإمبراطورية */
        .corner-filigree {
            position: absolute;
            width: 46px;
            height: 46px;
            pointer-events: none;
            z-index: 3;
        }
        .corner-tl { top: 7.5mm; left: 7.5mm; }
        .corner-tr { top: 7.5mm; right: 7.5mm; transform: scaleX(-1); }
        .corner-bl { bottom: 7.5mm; left: 7.5mm; transform: scaleY(-1); }
        .corner-br { bottom: 7.5mm; right: 7.5mm; transform: scale(-1); }

        /* المحتوى الداخلي للشهادة */
        .cert-content {
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* ─── 1. رأس الشهادة ─── */
        .header-section {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 15px;
            padding-bottom: 3px;
        }
        .header-meta-right {
            text-align: right;
        }
        .institution-title {
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .institution-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: #b45309;
            margin-top: 2px;
        }
        .header-center-emblem {
            text-align: center;
        }
        .emblem-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 2px solid #d97706;
            box-shadow: 0 4px 10px rgba(217, 119, 6, 0.2);
            color: #92400e;
            font-size: 22px;
        }
        .header-meta-left {
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
        }
        .serial-tag {
            display: inline-block;
            font-family: monospace;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 2px 8px;
            border-radius: 6px;
            color: #475569;
            font-size: 10px;
            font-weight: 800;
        }

        /* ─── 2. العنوان الرئيسي المزخرف ─── */
        .title-block {
            text-align: center;
            margin: 2px 0 6px;
        }
        .main-heading {
            font-family: 'Amiri', serif;
            font-size: 42px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            line-height: 1.1;
            letter-spacing: 0.5px;
            text-shadow: 1px 1px 0 rgba(217, 119, 6, 0.2);
        }
        .golden-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin: 2px auto;
            max-width: 480px;
        }
        .divider-line {
            flex: 1;
            height: 1.5px;
            background: linear-gradient(to right, transparent, #d97706, transparent);
        }
        .divider-star {
            color: #d97706;
            font-size: 14px;
        }
        .sub-heading {
            font-size: 12px;
            font-weight: 800;
            color: #92400e;
            letter-spacing: 0.5px;
        }

        /* ─── 3. متن الشهادة واسم الطالب ─── */
        .body-section {
            text-align: center;
            padding: 0 10mm;
        }
        .intro-text {
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 4px;
        }
        .intro-text strong {
            color: #0f172a;
        }

        /* كارت اسم الطالب الفاخر */
        .student-honor-plaque {
            display: inline-block;
            margin: 4px auto 8px;
            padding: 3px 40px;
            background: linear-gradient(180deg, #fffef9 0%, #fef3c7 100%);
            border: 2px solid #f59e0b;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.15), inset 0 1px 0 #ffffff;
            position: relative;
        }
        .student-honor-plaque::before, .student-honor-plaque::after {
            content: '✦';
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #d97706;
            font-size: 14px;
        }
        .student-honor-plaque::before { right: 14px; }
        .student-honor-plaque::after { left: 14px; }

        .student-title-name {
            font-family: 'Amiri', serif;
            font-size: 36px;
            font-weight: 900;
            color: #064e3b;
            line-height: 1.2;
            letter-spacing: 0.5px;
        }

        .citation-text {
            font-size: 13.5px;
            line-height: 1.7;
            color: #334155;
            font-weight: 600;
            max-width: 780px;
            margin: 0 auto;
        }
        .citation-text strong {
            color: #0f172a;
            font-weight: 800;
        }
        .highlight-subject {
            color: #92400e;
            font-weight: 900;
            background: #fef3c7;
            padding: 1px 8px;
            border-radius: 6px;
            border: 1px solid #fde68a;
        }

        /* وسام الشرف والترتيب */
        .honor-badge-ribbon {
            margin-top: 6px;
        }
        .honor-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #fef3c7 100%);
            color: #78350f;
            padding: 4px 22px;
            border-radius: 9999px;
            font-weight: 900;
            font-size: 13px;
            border: 1.5px solid #d97706;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.2);
        }

        /* ─── 4. التوقيعات والختم الملكي ثلاثي الأبعاد ─── */
        .footer-section {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: flex-end;
            padding: 0 10mm 1mm;
            position: relative;
        }

        .signature-box {
            text-align: center;
            min-width: 170px;
        }
        .sign-role {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 22px;
        }
        .sign-line {
            width: 140px;
            height: 1.5px;
            background: #cbd5e1;
            margin: 0 auto 6px;
        }
        .sign-name-text {
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
        }
        .sign-subtext {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            margin-top: 1px;
        }

        /* الختم الذهبي الملكي المعتمد ثلاثي الأبعاد مع شريطة الساتان */
        .royal-seal-container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .seal-ribbon-tail-left {
            position: absolute;
            bottom: -8px;
            left: 12px;
            width: 18px;
            height: 32px;
            background: linear-gradient(to bottom, #dc2626, #991b1b);
            clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 80%, 0 100%);
            z-index: 1;
            transform: rotate(12deg);
        }
        .seal-ribbon-tail-right {
            position: absolute;
            bottom: -8px;
            right: 12px;
            width: 18px;
            height: 32px;
            background: linear-gradient(to bottom, #dc2626, #991b1b);
            clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 80%, 0 100%);
            z-index: 1;
            transform: rotate(-12deg);
        }
        .seal-medallion {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #fffbeb 0%, #fef08a 25%, #eab308 55%, #ca8a04 80%, #854d0e 100%);
            border: 2.5px solid #ffffff;
            box-shadow: 0 6px 18px rgba(161, 98, 7, 0.4), inset 0 2px 4px rgba(255, 255, 255, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
            color: #713f12;
            text-align: center;
            outline: 1.5px dashed #a16207;
            outline-offset: -4px;
        }
        .seal-medallion .seal-star {
            font-size: 14px;
            color: #854d0e;
            line-height: 1;
        }
        .seal-medallion .seal-label {
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.5px;
            line-height: 1.1;
            margin-top: 1px;
        }
        .seal-medallion .seal-sub {
            font-size: 7.5px;
            font-weight: 800;
            color: #854d0e;
            opacity: 0.9;
        }

        /* شريط الأدوات العائم للطباعة والإغلاق */
        .print-floating-bar {
            position: fixed;
            top: 18px;
            left: 20px;
            z-index: 99999;
            display: flex;
            gap: 10px;
            align-items: center;
            background: rgba(15, 23, 42, 0.85);
            backdrop-blur-md: 10px;
            padding: 8px 14px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .btn-action-print {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .btn-action-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.45);
        }
        .btn-action-close {
            background: #334155;
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-action-close:hover {
            background: #475569;
        }
    </style>
</head>
<body>

    {{-- شريط التحكم والطباعة --}}
    <div class="print-floating-bar">
        <button class="btn-action-print" onclick="window.print()">
            <span>🖨️</span>
            <span>طباعة الشهادة الرسمية</span>
        </button>
        <button class="btn-action-close" onclick="window.close()">إغلاق</button>
    </div>

    {{-- كارت الشهادة الأكاديمية A4 Landscape --}}
    <div class="cert-canvas">

        {{-- خلفية مائية وزخارف جيولوشيه --}}
        <div class="cert-bg-pattern"></div>
        <div class="cert-watermark">تفوق</div>

        {{-- إطارات الذهب الملكية المتداخلة --}}
        <div class="gold-border-outer"></div>
        <div class="gold-border-middle"></div>
        <div class="gold-border-inner"></div>

        {{-- زخارف الأركان الفاخرة (SVG ذهبي عالي الدقة) --}}
        <svg class="corner-filigree corner-tl" viewBox="0 0 50 50" fill="none">
            <path d="M4 4H26C26 15 15 26 4 26V4Z" fill="#fef3c7" stroke="#b45309" stroke-width="1.5"/>
            <path d="M4 4L35 4C35 21 21 35 4 35L4 4Z" stroke="#d97706" stroke-width="1"/>
            <circle cx="12" cy="12" r="3" fill="#b45309"/>
        </svg>
        <svg class="corner-filigree corner-tr" viewBox="0 0 50 50" fill="none">
            <path d="M4 4H26C26 15 15 26 4 26V4Z" fill="#fef3c7" stroke="#b45309" stroke-width="1.5"/>
            <path d="M4 4L35 4C35 21 21 35 4 35L4 4Z" stroke="#d97706" stroke-width="1"/>
            <circle cx="12" cy="12" r="3" fill="#b45309"/>
        </svg>
        <svg class="corner-filigree corner-bl" viewBox="0 0 50 50" fill="none">
            <path d="M4 4H26C26 15 15 26 4 26V4Z" fill="#fef3c7" stroke="#b45309" stroke-width="1.5"/>
            <path d="M4 4L35 4C35 21 21 35 4 35L4 4Z" stroke="#d97706" stroke-width="1"/>
            <circle cx="12" cy="12" r="3" fill="#b45309"/>
        </svg>
        <svg class="corner-filigree corner-br" viewBox="0 0 50 50" fill="none">
            <path d="M4 4H26C26 15 15 26 4 26V4Z" fill="#fef3c7" stroke="#b45309" stroke-width="1.5"/>
            <path d="M4 4L35 4C35 21 21 35 4 35L4 4Z" stroke="#d97706" stroke-width="1"/>
            <circle cx="12" cy="12" r="3" fill="#b45309"/>
        </svg>

        {{-- المحتوى الداخلي --}}
        <div class="cert-content">

            {{-- 1. رأس الشهادة (اسم السنتر وشعار المنظومة) --}}
            <div class="header-section">
                <div class="header-meta-right">
                    <h2 class="institution-title">
                        <span>🏛️</span>
                        <span>{{ $centerName }}</span>
                    </h2>
                    <p class="institution-subtitle">إشراف: {{ $teacherName }} — {{ $teacherSubject }}</p>
                </div>

                <div class="header-center-emblem">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" style="max-height: 48px; max-width: 110px; object-fit: contain;" alt="Logo">
                    @else
                        <div class="emblem-badge">🎖️</div>
                    @endif
                </div>

                <div class="header-meta-left">
                    <div>كود الاعتماد: <span class="serial-tag">{{ $serialNumber }}</span></div>
                    <div style="margin-top: 3px;">العام الدراسي: <strong>{{ $academicYear }}</strong></div>
                </div>
            </div>

            {{-- 2. العنوان الرئيسي والزخرفة --}}
            <div class="title-block">
                <h1 class="main-heading">شــهـادة شـكـر وتـقـديـر وتـفـوّق</h1>
                <div class="golden-divider">
                    <div class="divider-line"></div>
                    <div class="divider-star">✦ ✦ ✦</div>
                    <div class="divider-line"></div>
                </div>
                <div class="sub-heading">لوحة الشرف للأوائل والمتميزين دراسياً وأخلاقياً</div>
            </div>

            {{-- 3. متن الشهادة واسم الطالب المكرم --}}
            <div class="body-section">
                <div class="intro-text">
                    تتشرف إدارة <strong>{{ $centerName }}</strong> بالتعاون مع <strong>{{ $teacherName }}</strong> بمنح هذه الشهادة للطالب المتميز:
                </div>

                <div class="student-honor-plaque">
                    <span class="student-title-name">{{ $student->name }}</span>
                </div>

                <div class="citation-text">
                    تقديراً لاجتهاده البارز وتفوقه الأكاديمي المشهود وحصوله على أعلى الدرجات والالتزام الكامل بالحضور والمواظبة في مادة <span class="highlight-subject">{{ $teacherSubject }}</span> المقررة على (<strong>{{ $student->educationalStage?->name ?? 'المرحلة العامة' }}</strong>). متمنين له دوام الرفعة والريادة في مسيرته العلمية.
                </div>

                <div class="honor-badge-ribbon">
                    <span class="honor-pill">
                        <span>🌟</span>
                        <span>{{ $rankBadge ?? 'وسام التميز والتفوق الأكاديمي' }}</span>
                        <span>🌟</span>
                    </span>
                </div>
            </div>

            {{-- 4. التوقيعات والختم الملكي --}}
            <div class="footer-section">
                <div class="signature-box">
                    <div class="sign-role">المشرف والمعد الأكاديمي</div>
                    <div class="sign-line"></div>
                    <div class="sign-name-text">{{ $teacherName }}</div>
                    <div class="sign-subtext">معلم {{ $teacherSubject }}</div>
                </div>

                <div class="royal-seal-container">
                    <div class="seal-ribbon-tail-left"></div>
                    <div class="seal-ribbon-tail-right"></div>
                    <div class="seal-medallion">
                        <span class="seal-star">★</span>
                        <span class="seal-label">معتمد رسمياً</span>
                        <span class="seal-sub">تفوق أكاديمي</span>
                    </div>
                </div>

                <div class="signature-box">
                    <div class="sign-role">إدارة السنتر والاعتماد</div>
                    <div class="sign-line"></div>
                    <div class="sign-name-text">{{ $centerName }}</div>
                    <div class="sign-subtext">تحريراً في: {{ date('Y / m / d') }}</div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
