<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>{{ $exam->title }} - ورقة الامتحان</title>
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
        $logoUrl = app(\App\Services\SettingService::class)->url('center_logo');
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 15mm 12mm 15mm 12mm;
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
            background: #ffffff;
            color: #0f172a;
            margin: 0;
            padding: 0;
            font-size: 13px;
            line-height: 1.6;
        }
        
        /* ─── ترويسة الامتحان ─── */
        .exam-header {
            border: 2px solid #1e293b;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 16px;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-center {
            text-align: center;
            flex-grow: 1;
        }
        .header-center h1 {
            margin: 0 0 4px;
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
        }
        .header-center .sub-info {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .header-side {
            width: 140px;
            font-size: 11px;
            font-weight: 600;
            color: #334155;
        }
        .student-box {
            border: 1px dashed #94a3b8;
            border-radius: 8px;
            padding: 6px 10px;
            margin-bottom: 16px;
            background: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            font-size: 12px;
        }
        .student-box .dots {
            border-bottom: 1px dotted #64748b;
            display: inline-block;
            width: 180px;
            height: 14px;
        }

        /* ─── الأسئلة ─── */
        .questions-container {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .question-card {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px 14px;
            background: #ffffff;
            page-break-inside: avoid;
        }
        .question-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 6px;
        }
        .question-number {
            background: #0f172a;
            color: #ffffff;
            font-weight: 900;
            font-size: 12px;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-left: 6px;
        }
        .question-title {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            flex-grow: 1;
            line-height: 1.5;
        }
        .question-marks {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 10.5px;
            font-weight: 800;
            white-space: nowrap;
        }
        .question-img-wrap {
            text-align: center;
            margin: 8px 0;
        }
        .question-img-wrap img {
            max-height: 140px;
            max-width: 100%;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px 12px;
            margin-top: 6px;
        }
        .option-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            padding: 4px 8px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .option-checkbox {
            width: 14px;
            height: 14px;
            border: 1.5px solid #475569;
            border-radius: 3px;
            display: inline-block;
            flex-shrink: 0;
        }
        .option-letter {
            font-weight: 800;
            color: #2563eb;
        }

        /* ─── الفوتر ─── */
        .exam-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px dashed #cbd5e1;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
        }

        /* ─── زر الطباعة عند الفتح في المتصفح ─── */
        .no-print-bar {
            background: #0f172a;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .print-btn {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-family: 'Cairo', sans-serif;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>معاينة طباعة ورقة الاختبار (PDF)</strong> — {{ $exam->title }}
        </div>
        <button class="print-btn" onclick="window.print()">🖨️ طباعة الآن أو حفظ كـ PDF</button>
    </div>

    {{-- ترويسة الاختبار --}}
    <div class="exam-header">
        <div class="header-side" style="text-align: right;">
            <div>نظام الأستاذ محمد الغندي</div>
            <div>المادة: <strong>{{ $exam->subject?->name ?? 'عام' }}</strong></div>
            <div>المرحلة: <strong>{{ $exam->educationalStage?->name ?? '-' }}</strong></div>
        </div>

        <div class="header-center">
            <h1>{{ $exam->title }}</h1>
            <div class="sub-info">
                <span>⏱️ زمن الاختبار: <strong>{{ $exam->duration_minutes ? $exam->duration_minutes . ' دقيقة' : 'مفتوح' }}</strong></span>
                <span>🎯 الدرجة الكلية: <strong>{{ $exam->total_marks }} درجة</strong></span>
                <span>❓ عدد الأسئلة: <strong>{{ $exam->questions->count() }} سؤال</strong></span>
            </div>
        </div>

        <div class="header-side" style="text-align: left;">
            <div>التاريخ: <strong>{{ \Carbon\Carbon::parse($exam->date)->format('Y/m/d') }}</strong></div>
            <div>النوع: <strong>{{ $exam->is_online ? 'إلكتروني & ورقي' : 'ورقي' }}</strong></div>
        </div>
    </div>

    {{-- خانة بيانات الطالب للاختبار الورقي --}}
    <div class="student-box">
        <div>اسم الطالب: <span class="dots"></span></div>
        <div>المجموعة: <span class="dots" style="width: 140px;"></span></div>
        <div>رقم الجلوس / الكود: <span class="dots" style="width: 100px;"></span></div>
        <div>الدرجة: [ &nbsp;&nbsp;&nbsp;&nbsp; / {{ $exam->total_marks }} ]</div>
    </div>

    {{-- الأسئلة --}}
    <div class="questions-container">
        @php
            $letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و'];
        @endphp

        @forelse($exam->questions as $index => $q)
            <div class="question-card">
                <div class="question-head">
                    <div style="display: flex; align-items: flex-start; flex-grow: 1;">
                        <span class="question-number">{{ $index + 1 }}</span>
                        <h3 class="question-title">{{ $q->question_text }}</h3>
                    </div>
                    <span class="question-marks">{{ $q->pivot->marks ?? $q->default_marks }} درجات</span>
                </div>

                @if($q->question_image)
                    <div class="question-img-wrap">
                        <img src="{{ asset('storage/' . $q->question_image) }}" alt="شكل توضيحي">
                    </div>
                @endif

                @php
                    $opts = is_array($q->options) ? $q->options : [];
                @endphp

                @if(count($opts) > 0)
                    <div class="options-grid" style="{{ count($opts) == 2 ? 'grid-template-columns: repeat(2, 1fr);' : '' }}">
                        @foreach($opts as $i => $opt)
                            <div class="option-item">
                                <span class="option-checkbox"></span>
                                <span class="option-letter">({{ $letters[$i] ?? ($i+1) }})</span>
                                <span>{{ is_array($opt) ? ($opt['option_text'] ?? json_encode($opt)) : $opt }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: #64748b; font-weight: bold;">
                لا توجد أسئلة مضافة لهذا الامتحان حتى الآن.
            </div>
        @endforelse
    </div>

    <div class="exam-footer">
        مع تمنياتنا لجميع أبنائنا الطلاب بدوام التفوق والنجاح ✨ — الأستاذ محمد الغندي
    </div>

</body>
</html>
