<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>{{ $exam->title }} - ورقة الامتحان</title>
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 12mm 12mm 12mm 12mm;
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
            background: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            line-height: 1.6;
        }

        .paper-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        
        /* ─── ترويسة الامتحان ─── */
        .exam-header {
            border: 2px solid #0f172a;
            border-radius: 12px;
            padding: 12px 18px;
            margin-bottom: 14px;
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
            font-size: 19px;
            font-weight: 900;
            color: #0f172a;
        }
        .header-center .sub-info {
            font-size: 11.5px;
            font-weight: 700;
            color: #475569;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .header-side {
            width: 150px;
            font-size: 11.5px;
            font-weight: 700;
            color: #334155;
            line-height: 1.5;
        }
        .student-box {
            border: 1.5px dashed #94a3b8;
            border-radius: 10px;
            padding: 8px 14px;
            margin-bottom: 18px;
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
            width: 160px;
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
            border-radius: 12px;
            padding: 14px 16px;
            background: #ffffff;
            page-break-inside: avoid;
        }
        .question-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
        }
        .question-number {
            background: #0f172a;
            color: #ffffff;
            font-weight: 900;
            font-size: 12.5px;
            width: 26px;
            height: 26px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-left: 8px;
        }
        .question-title {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            flex-grow: 1;
            line-height: 1.6;
        }
        .question-marks {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }
        .question-img-wrap {
            text-align: center;
            margin: 10px 0;
        }
        .question-img-wrap img {
            max-height: 160px;
            max-width: 100%;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px 14px;
            margin-top: 8px;
        }
        .option-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            padding: 7px 12px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .option-checkbox {
            width: 16px;
            height: 16px;
            border: 1.5px solid #64748b;
            border-radius: 4px;
            display: inline-block;
            flex-shrink: 0;
            background: #ffffff;
        }
        .option-badge {
            font-weight: 900;
            font-size: 11.5px;
            color: #2563eb;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 1px 7px;
            border-radius: 5px;
            flex-shrink: 0;
        }

        /* ─── الفوتر ─── */
        .exam-footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 2px dashed #cbd5e1;
            text-align: center;
            font-size: 11.5px;
            font-weight: 700;
            color: #64748b;
        }

        /* ─── زر الطباعة عند الفتح في المتصفح ─── */
        .no-print-bar {
            max-width: 850px;
            margin: 0 auto 16px auto;
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
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 9px 22px;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-weight: 900;
            font-size: 13.5px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            transition: all 0.2s;
        }
        .print-btn:hover {
            background: #1d4ed8;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                background: #ffffff;
                padding: 0;
            }
            .paper-container {
                padding: 0;
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div style="font-weight: 800; font-size: 14px;">
            📄 <strong>معاينة طباعة ورقة الاختبار</strong> — {{ $exam->title }}
        </div>
        <button class="print-btn" onclick="window.print()">🖨️ طباعة الآن أو حفظ كـ PDF</button>
    </div>

    <div class="paper-container">
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
            <div>المجموعة: <span class="dots" style="width: 130px;"></span></div>
            <div>رقم الجلوس / الكود: <span class="dots" style="width: 90px;"></span></div>
            <div>الدرجة: [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; / {{ $exam->total_marks }} ]</div>
        </div>

        {{-- الأسئلة --}}
        <div class="questions-container">
            @php
                $arabicLetters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و'];
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
                        $opts = $q->options;
                        if (is_string($opts)) {
                            $opts = json_decode($opts, true) ?? [];
                        }
                        $opts = is_array($opts) ? $opts : [];
                    @endphp

                    @if(count($opts) > 0)
                        <div class="options-grid" style="{{ count($opts) == 2 ? 'grid-template-columns: repeat(2, 1fr);' : '' }}">
                            @foreach($opts as $i => $opt)
                                @php
                                    // إذا كان الخيار عبارة عن مصفوفة (key, text) أو نص عادي
                                    if (is_array($opt)) {
                                        $rawKey = $opt['key'] ?? ($i + 1);
                                        $optText = $opt['text'] ?? ($opt['option_text'] ?? json_encode($opt, JSON_UNESCAPED_UNICODE));
                                    } else {
                                        $rawKey = $i + 1;
                                        $optText = $opt;
                                    }

                                    // تحويل المفتاح لرمز عربي أنيق
                                    if ($rawKey === 'true' || $rawKey === true) {
                                        $badge = '✔ صواب';
                                    } elseif ($rawKey === 'false' || $rawKey === false) {
                                        $badge = '✖ خطأ';
                                    } elseif (is_numeric($rawKey)) {
                                        $badge = '(' . ($arabicLetters[(int)$rawKey - 1] ?? $rawKey) . ')';
                                    } elseif (in_array(strtoupper($rawKey), ['A', 'B', 'C', 'D', 'E'])) {
                                        $map = ['A' => 'أ', 'B' => 'ب', 'C' => 'ج', 'D' => 'د', 'E' => 'هـ'];
                                        $badge = '(' . ($map[strtoupper($rawKey)] ?? $rawKey) . ')';
                                    } else {
                                        $badge = '(' . $rawKey . ')';
                                    }
                                @endphp

                                <div class="option-item">
                                    <span class="option-checkbox"></span>
                                    <span class="option-badge">{{ $badge }}</span>
                                    <span>{{ $optText }}</span>
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
    </div>

</body>
</html>
