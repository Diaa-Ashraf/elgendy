<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>{{ $exam->title }} - نماذج الامتحان والطباعة</title>
    @php
        $faviconUrl = app(\App\Services\SettingService::class)->url('site_favicon');
        $siteSettings = app(\App\Services\SettingService::class);
        $centerName = $siteSettings->get('center_name', 'المنظومة التعليمية');
        $teacherName = $siteSettings->get('teacher_name', 'أ / محمد الجندي');
        $teacherSubject = $siteSettings->get('teacher_subject', 'مادة الفيزياء والكيمياء');
        $logoUrl = $siteSettings->url('site_logo');
        $headerNotes = $siteSettings->get('exam_header_notes', 'اقرأ الأسئلة بعناية قبل الإجابة، واستعن بالله');
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
            line-height: 1.6;
        }

        /* ─── شريط أدوات التحكم والطباعة ─── */
        .controls-toolbar {
            max-width: 900px;
            margin: 0 auto 20px auto;
            background: #0f172a;
            color: #ffffff;
            padding: 14px 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);
        }
        .controls-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 15px;
        }
        .controls-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
            font-family: 'Cairo', sans-serif;
        }
        .btn-print {
            background: #10b981;
            color: #ffffff;
        }
        .btn-print:hover {
            background: #059669;
        }
        .btn-mode {
            background: #334155;
            color: #f8fafc;
        }
        .btn-mode.active, .btn-mode:hover {
            background: #f59e0b;
            color: #0f172a;
        }

        /* ─── ورقة الامتحان ─── */
        .paper-container {
            max-width: 880px;
            margin: 0 auto 30px auto;
            background: #ffffff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            page-break-after: always;
        }
        .paper-container:last-child {
            page-break-after: auto;
        }

        /* ─── ترويسة الامتحان ─── */
        .exam-header {
            border: 2px solid #0f172a;
            border-radius: 12px;
            padding: 12px 18px;
            margin-bottom: 12px;
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
            width: 160px;
            font-size: 11.5px;
            font-weight: 700;
            color: #334155;
            line-height: 1.5;
        }
        .model-badge {
            display: inline-block;
            background: #0f172a;
            color: #ffffff;
            font-weight: 900;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 4px;
        }
        .student-box {
            border: 1.5px dashed #94a3b8;
            border-radius: 10px;
            padding: 8px 14px;
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
            width: 140px;
            height: 14px;
        }

        /* ─── الأسئلة ─── */
        .questions-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .question-card {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
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
            margin-left: 8px;
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
            max-height: 150px;
            max-width: 100%;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        /* ─── شبكة الخيارات ─── */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-top: 8px;
        }
        .option-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            font-weight: 700;
            color: #1e293b;
        }
        .option-checkbox {
            width: 16px;
            height: 16px;
            border: 1.5px solid #94a3b8;
            border-radius: 4px;
            display: inline-block;
            flex-shrink: 0;
            background: #ffffff;
        }
        .option-badge {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 900;
            padding: 1px 6px;
            border-radius: 5px;
            font-size: 11px;
            flex-shrink: 0;
        }

        /* ─── التذييل ─── */
        .exam-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1.5px solid #e2e8f0;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
        }

        /* ─── جدول مفتاح الإجابة للمعلم ─── */
        .answer-key-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 12px;
        }
        .answer-key-table th, .answer-key-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: center;
        }
        .answer-key-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
        }
        .answer-key-table tr:nth-child(even) {
            background: #f8fafc;
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
                margin: 0 0 20px 0;
            }
        }
    </style>
</head>
<body>

    {{-- شريط التحكم والخيارات العلوية --}}
    <div class="controls-toolbar">
        <div class="controls-title">
            <span>طباعة نماذج الامتحان: {{ $exam->title }}</span>
        </div>
        <div class="controls-actions">
            <span style="font-size: 12px; color: #94a3b8;">النماذج المتاحة:</span>
            <a href="?models_count=1&include_answer_key={{ $includeAnswerKey ? 1 : 0 }}" class="btn-toolbar btn-mode {{ $modelsCount == 1 ? 'active' : '' }}">
                نموذج واحد (أ)
            </a>
            <a href="?models_count=2&include_answer_key={{ $includeAnswerKey ? 1 : 0 }}" class="btn-toolbar btn-mode {{ $modelsCount == 2 ? 'active' : '' }}">
                نموذجان (أ / ب) لمنع الغش
            </a>
            <a href="?models_count=3&include_answer_key={{ $includeAnswerKey ? 1 : 0 }}" class="btn-toolbar btn-mode {{ $modelsCount == 3 ? 'active' : '' }}">
                3 نماذج (أ / ب / ج)
            </a>

            <a href="?models_count={{ $modelsCount }}&include_answer_key={{ $includeAnswerKey ? 0 : 1 }}" class="btn-toolbar btn-mode {{ $includeAnswerKey ? 'active' : '' }}">
                {{ $includeAnswerKey ? 'نموذج الإجابة مفعّل' : 'بدون نموذج إجابة' }}
            </a>

            <button onclick="window.print()" class="btn-toolbar btn-print">
                <span>طباعة النماذج الآن</span>
            </button>
        </div>
    </div>

    {{-- طباعة كل نموذج --}}
    @foreach($models as $model)
        <div class="paper-container">
            {{-- ترويسة الامتحان --}}
            <div class="exam-header">
                <div class="header-side text-right">
                    <div>{{ $centerName }}</div>
                    <div style="color: #64748b;">{{ $teacherSubject }}</div>
                    <div>{{ $teacherName }}</div>
                </div>

                <div class="header-center">
                    <h1>{{ $exam->title }}</h1>
                    <div class="sub-info">
                        <span>المرحلة: <strong>{{ $exam->educationalStage?->name }}</strong></span>
                        <span>المادة: <strong>{{ $exam->subject?->name }}</strong></span>
                        <span>الزمن: <strong>{{ $exam->duration_minutes ?? 45 }} دقيقة</strong></span>
                        <span>الدرجة الكلية: <strong>{{ $exam->total_marks }} درجة</strong></span>
                    </div>
                    <div>
                        <span class="model-badge">نموذج ( {{ $model['model_code'] }} )</span>
                    </div>
                </div>

                <div class="header-side text-left" style="text-align: left;">
                    <div>تاريخ: {{ $exam->date?->format('Y-m-d') ?? date('Y-m-d') }}</div>
                    <div style="font-size: 10.5px; color: #64748b;">عدد الأسئلة: {{ $model['questions_count'] }}</div>
                    <div style="font-size: 10px; color: #94a3b8;">{{ $headerNotes }}</div>
                </div>
            </div>

            {{-- خانة بيانات الطالب للاختبار الورقي --}}
            <div class="student-box">
                <div>اسم الطالب: <span class="dots"></span></div>
                <div>المجموعة: <span class="dots" style="width: 120px;"></span></div>
                <div>رقم الجلوس / الكود: <span class="dots" style="width: 80px;"></span></div>
                <div>الدرجة: [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; / {{ $exam->total_marks }} ]</div>
            </div>

            {{-- الأسئلة --}}
            <div class="questions-container">
                @forelse($model['questions'] as $q)
                    <div class="question-card">
                        <div class="question-head">
                            <div style="display: flex; align-items: flex-start; flex-grow: 1;">
                                <span class="question-number">{{ $q['number'] }}</span>
                                <h3 class="question-title">{{ $q['question_text'] }}</h3>
                            </div>
                            <span class="question-marks">{{ $q['marks'] }} درجات</span>
                        </div>

                        @if($q['question_image'])
                            <div class="question-img-wrap">
                                <img src="{{ asset('storage/' . $q['question_image']) }}" alt="شكل توضيحي">
                            </div>
                        @endif

                        @if(!empty($q['options']) && count($q['options']) > 0)
                            <div class="options-grid" style="{{ count($q['options']) == 2 ? 'grid-template-columns: repeat(2, 1fr);' : '' }}">
                                @foreach($q['options'] as $opt)
                                    <div class="option-item">
                                        <span class="option-checkbox"></span>
                                        <span class="option-badge">({{ $opt['key'] }})</span>
                                        <span>{{ $opt['text'] }}</span>
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
                مع تمنياتنا لجميع أبنائنا الطلاب بدوام التفوق والنجاح — {{ $teacherName }}
            </div>
        </div>
    @endforeach

    {{-- صفحة مفتاح الإجابة للمعلم (Answer Key) --}}
    @if($includeAnswerKey)
        <div class="paper-container" style="background: #ffffff; border: 2px solid #0f172a;">
            <div style="text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 15px;">
                <span style="font-size: 20px; font-weight: 900; color: #0f172a;">مفتاح الإجابة النموذجي وتوزيع الدرجات للمعلم والمساعدين</span>
                <div style="font-size: 13px; font-weight: 700; color: #475569; margin-top: 4px;">
                    امتحان: {{ $exam->title }} — مادة: {{ $exam->subject?->name }} ({{ $exam->educationalStage?->name }})
                </div>
            </div>

            @foreach($models as $model)
                <div style="margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f1f5f9; padding: 8px 14px; border-radius: 8px; font-weight: 900; margin-bottom: 8px;">
                        <span style="font-size: 15px; color: #0f172a;">مفتاح إجابة: نموذج ( {{ $model['model_code'] }} )</span>
                        <span style="color: #64748b; font-size: 12px;">إجمالي الأسئلة: {{ $model['questions_count'] }} | الدرجة: {{ $model['total_marks'] }}</span>
                    </div>

                    <table class="answer-key-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">رقم السؤال</th>
                                <th style="width: 100px;">رمز الإجابة</th>
                                <th>نص الإجابة النموذجية</th>
                                <th style="width: 80px;">الدرجة</th>
                                <th>الدرس / الموضوع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($model['answer_key'] as $ak)
                                <tr>
                                    <td style="font-weight: 900;">#{{ $ak['number'] }}</td>
                                    <td style="font-weight: 900; color: #10b981; font-size: 14px;">({{ $ak['correct_keys'] }})</td>
                                    <td style="text-align: right; font-weight: 700;">{{ $ak['correct_text'] }}</td>
                                    <td style="font-weight: 800;">{{ $ak['marks'] }}</td>
                                    <td style="color: #64748b; font-size: 11px;">{{ $ak['topic'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            <div class="exam-footer">
                سري خاص بالمعلم والمساعدين فقط — {{ $teacherName }}
            </div>
        </div>
    @endif

</body>
</html>
