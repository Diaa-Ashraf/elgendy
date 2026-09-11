<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>تقرير الأداء الشهري - {{ $report['student']->name }} ({{ $report['month_name'] }})</title>
    @php
        $siteSettings = app(\App\Services\SettingService::class);
        $centerName = $siteSettings->get('center_name', 'المنظومة التعليمية');
        $teacherName = $siteSettings->get('teacher_name', 'أ / محمد الجندي');
        $teacherSubject = $siteSettings->get('teacher_subject', 'مادة الفيزياء والكيمياء');
        $centerPhone = $siteSettings->get('center_phone', '01000000000');
        $faviconUrl = $siteSettings->url('site_favicon');
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 10mm 12mm 10mm 12mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            text-align: right;
            background: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 20px 10px;
            font-size: 13px;
            line-height: 1.5;
        }

        .controls-toolbar {
            max-width: 900px;
            margin: 0 auto 20px auto;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);
        }

        .paper-container {
            max-width: 880px;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px 28px;
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.08);
            border: 2px solid #0f172a;
        }

        /* ─── الترويسة ─── */
        .header-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-title h1 {
            margin: 0 0 4px 0;
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
        }
        .header-title p {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
        }

        /* ─── بطاقة بيانات الطالب ─── */
        .student-profile {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            padding: 12px 18px;
            margin-bottom: 16px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            font-weight: 700;
        }
        .profile-item {
            font-size: 12px;
            color: #475569;
        }
        .profile-item strong {
            display: block;
            font-size: 14px;
            color: #0f172a;
            margin-top: 2px;
        }

        /* ─── بطاقات المؤشرات الأربعة ─── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }
        .kpi-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            text-align: center;
        }
        .kpi-title {
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: 900;
            line-height: 1.2;
        }
        .kpi-sub {
            font-size: 10.5px;
            font-weight: 700;
            margin-top: 4px;
            color: #64748b;
        }

        /* ─── الجداول ─── */
        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
            margin: 16px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #e2e8f0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 12px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: center;
        }
        .data-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 11.5px;
        }
        .data-table tr:nth-child(even) {
            background: #f8fafc;
        }

        /* ─── بطاقة التقييم الشامل ─── */
        .evaluation-card {
            background: #f8fafc;
            border: 2px dashed #0f172a;
            border-radius: 12px;
            padding: 14px 18px;
            margin-top: 16px;
        }
        .eval-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 8px;
            font-weight: 900;
            font-size: 12px;
            color: #ffffff;
        }

        /* ─── الختم والتوقيع ─── */
        .signature-block {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 24px;
            padding-top: 14px;
            border-top: 1.5px solid #cbd5e1;
            font-size: 12px;
            font-weight: 800;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .controls-toolbar {
                display: none !important;
            }
            .paper-container {
                box-shadow: none;
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    {{-- شريط التحكم والطباعة --}}
    <div class="controls-toolbar">
        <div style="font-weight: 800; font-size: 14px;">
            كارت تقرير الأداء الشهري الرسمي — {{ $report['month_name'] }}
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <form method="GET" style="display: flex; gap: 8px; align-items: center; margin: 0;">
                <select name="month" style="font-family: 'Cairo'; padding: 4px 10px; border-radius: 6px; font-weight: 700;">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $report['month'] == $m ? 'selected' : '' }}>
                            شهر {{ $m }}
                        </option>
                    @endfor
                </select>
                <select name="year" style="font-family: 'Cairo'; padding: 4px 10px; border-radius: 6px; font-weight: 700;">
                    <option value="2026" {{ $report['year'] == 2026 ? 'selected' : '' }}>2026</option>
                    <option value="2025" {{ $report['year'] == 2025 ? 'selected' : '' }}>2025</option>
                </select>
                <button type="submit" style="font-family: 'Cairo'; background: #334155; color: #fff; border: none; padding: 5px 12px; border-radius: 6px; font-weight: 700; cursor: pointer;">
                    عرض الشهر
                </button>
            </form>

            <button onclick="window.print()" style="font-family: 'Cairo'; background: #10b981; color: #fff; border: none; padding: 6px 16px; border-radius: 8px; font-weight: 800; cursor: pointer;">
                طباعة التقرير PDF
            </button>
        </div>
    </div>

    {{-- ورقة التقرير الرسمية --}}
    <div class="paper-container">
        {{-- الترويسة --}}
        <div class="header-box">
            <div>
                <div style="font-size: 16px; font-weight: 900; color: #0f172a;">{{ $centerName }}</div>
                <div style="font-size: 12px; font-weight: 700; color: #64748b;">{{ $teacherSubject }} — {{ $teacherName }}</div>
                <div style="font-size: 11px; color: #94a3b8;">هاتف المركز: {{ $centerPhone }}</div>
            </div>

            <div class="header-title" style="text-align: center;">
                <h1>كارت تقرير الأداء الشهري الشامل</h1>
                <p>عن شهر: <strong style="color: #0f172a;">{{ $report['month_name'] }}</strong></p>
            </div>

            <div style="text-align: left; font-size: 11px; font-weight: 700; color: #64748b;">
                <div>تاريخ الطباعة: {{ date('Y-m-d') }}</div>
                <div style="font-size: 12px; font-weight: 900; color: #0f172a; margin-top: 4px;">رقم التقرير: #{{ $report['student']->id }}-{{ $report['month'] }}</div>
            </div>
        </div>

        {{-- بيانات الطالب --}}
        <div class="student-profile">
            <div class="profile-item">
                اسم الطالب
                <strong>{{ $report['student']->name }}</strong>
            </div>
            <div class="profile-item">
                كود الطالب
                <strong style="font-family: monospace;">{{ $report['student']->qr_code }}</strong>
            </div>
            <div class="profile-item">
                المرحلة الدراسية
                <strong>{{ $report['student']->educationalStage?->name ?? '—' }}</strong>
            </div>
            <div class="profile-item">
                المجموعة الحالية
                <strong>{{ $report['student']->groups->pluck('name')->first() ?? '—' }}</strong>
            </div>
        </div>

        {{-- بطاقات مؤشرات الأداء الأربعة --}}
        <div class="kpi-grid">
            <div class="kpi-card" style="border-top: 4px solid #10b981;">
                <div class="kpi-title">نسبة الحضور والالتزام</div>
                <div class="kpi-value" style="color: #10b981;">{{ $report['attendance']['rate'] }}%</div>
                <div class="kpi-sub">حضر {{ $report['attendance']['present'] }} من أصل {{ $report['attendance']['total_sessions'] }} حصص</div>
            </div>

            <div class="kpi-card" style="border-top: 4px solid #3b82f6;">
                <div class="kpi-title">متوسط درجات الامتحانات</div>
                <div class="kpi-value" style="color: #3b82f6;">{{ $report['exams']['average_percentage'] ?? 0 }}%</div>
                <div class="kpi-sub">أدى {{ $report['exams']['total_exams'] }} امتحانات في الشهر</div>
            </div>

            <div class="kpi-card" style="border-top: 4px solid #f59e0b;">
                <div class="kpi-title">التزام تسليم الواجبات</div>
                <div class="kpi-value" style="color: #f59e0b;">{{ $report['homeworks']['commitment_rate'] }}%</div>
                <div class="kpi-sub">سلّم {{ $report['homeworks']['submitted'] }} من أصل {{ $report['homeworks']['total_homeworks'] }}</div>
            </div>

            <div class="kpi-card" style="border-top: 4px solid #8b5cf6;">
                <div class="kpi-title">المعدل التراكمي للشهر</div>
                <div class="kpi-value" style="color: #8b5cf6;">{{ $report['evaluation']['monthly_average'] }}%</div>
                <div class="kpi-sub">{{ $report['evaluation']['rating_badge'] }}</div>
            </div>
        </div>

        {{-- 1. جدول الامتحانات والكويزات --}}
        <div class="section-heading">
            <span>نتائج الامتحانات والاختبارات خلال الشهر</span>
            <span style="font-size: 11px; color: #64748b;">إجمالي الامتحانات: {{ count($report['exams']['list']) }}</span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>عنوان الامتحان / الكويز</th>
                    <th style="width: 80px;">التاريخ</th>
                    <th style="width: 80px;">الدرجة</th>
                    <th style="width: 70px;">النسبة</th>
                    <th style="width: 80px;">الترتيب</th>
                    <th style="width: 90px;">التقدير</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['exams']['list'] as $idx => $exam)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="text-align: right; font-weight: 800;">{{ $exam['title'] }}</td>
                        <td>{{ $exam['date'] }}</td>
                        <td style="font-weight: 900;">{{ $exam['marks_obtained'] }} / {{ $exam['total_marks'] }}</td>
                        <td style="font-weight: 900; color: #3b82f6;">{{ $exam['percentage'] }}%</td>
                        <td style="font-weight: 800;">المركز #{{ $exam['rank'] }}</td>
                        <td style="font-weight: 800;">{{ $exam['grade_text'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="color: #64748b; padding: 12px;">لا توجد امتحانات مسجلة لهذا الطالب خلال هذا الشهر.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- 2. جدول تسليمات الواجبات --}}
        <div class="section-heading">
            <span>موقف تسليم الواجبات والتكليفات</span>
            <span style="font-size: 11px; color: #64748b;">تم تسليم: {{ $report['homeworks']['submitted'] }} / {{ $report['homeworks']['total_homeworks'] }}</span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>عنوان الواجب</th>
                    <th style="width: 100px;">تاريخ التسليم</th>
                    <th>حالة التسليم والدرجة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['homeworks']['list'] as $idx => $hw)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="text-align: right; font-weight: 700;">{{ $hw['title'] }}</td>
                        <td>{{ $hw['due_date'] }}</td>
                        <td style="font-weight: 800; color: {{ $hw['is_submitted'] ? '#10b981' : '#ef4444' }};">
                            {{ $hw['status'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="color: #64748b; padding: 10px;">لا توجد واجبات مسجلة خلال هذا الشهر.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- بطاقة التقييم النهائي وتوصيات المعلم --}}
        <div class="evaluation-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <div style="font-weight: 900; font-size: 14px; color: #0f172a;">
                    التقييم العام لشهر ({{ $report['month_name'] }}):
                    <span style="color: {{ $report['evaluation']['rating_color'] }}; font-weight: 900;">
                        {{ $report['evaluation']['general_rating'] }}
                    </span>
                </div>
                <span class="eval-badge" style="background: {{ $report['evaluation']['rating_color'] }};">
                    {{ $report['evaluation']['rating_badge'] }} ({{ $report['evaluation']['monthly_average'] }}%)
                </span>
            </div>

            <div style="font-size: 12px; color: #334155; line-height: 1.6; font-weight: 700;">
                <strong>توجيه وملاحظة المعلم لولي الأمر:</strong>
                {{ $report['evaluation']['recommendation'] }}
            </div>
        </div>

        {{-- الختم والتوقيع الرسمي --}}
        <div class="signature-block">
            <div>
                <span>توقيع المساعد المشرف:</span>
                <span style="display: inline-block; width: 140px; border-bottom: 1px dotted #94a3b8; margin-right: 8px;"></span>
            </div>

            <div style="text-align: center;">
                <div style="border: 2px solid #0f172a; padding: 4px 14px; border-radius: 8px; font-weight: 900; color: #0f172a;">
                    ختم المنظومة التعليمية الرسمي
                </div>
            </div>

            <div style="text-align: left;">
                <span>توقيع المعلم:</span>
                <strong style="color: #0f172a; margin-right: 6px;">{{ $teacherName }}</strong>
            </div>
        </div>
    </div>

</body>
</html>
