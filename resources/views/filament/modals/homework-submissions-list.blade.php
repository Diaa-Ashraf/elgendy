@props([
    'submissions' => [],
    'homework' => null,
    'stats' => [],
])

@php
    $centerName = app(\App\Services\SettingService::class)->get('center_name', 'المنظومة التعليمية');
    $hwTitle = $homework?->title ?? 'الواجب';
    $subjectName = $homework?->subject?->name ?? 'المادة';
    $totalMarks = (float) ($homework?->total_marks ?? 10);
    $subsColl = collect($submissions);
@endphp

<div class="modal-content-wrapper text-right" id="hw-submissions-modal-{{ $homework?->id }}">
    <style>
        .em-stat-grid-5 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .em-stat-card {
            border-radius: 14px;
            padding: 10px 12px;
            text-align: center;
            border: 1px solid;
            transition: transform 0.2s ease;
        }
        .em-stat-card:hover {
            transform: translateY(-2px);
        }
        .em-stat-card.success {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .em-stat-card.info {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1e40af;
        }
        .em-stat-card.purple {
            background: #faf5ff;
            border-color: #e9d5ff;
            color: #6b21a8;
        }
        .em-stat-card.warning {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }
        .em-stat-card.danger {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #9f1239;
        }
        .em-stat-card .label {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 4px;
            display: block;
            opacity: 0.9;
        }
        .em-stat-card .value {
            font-size: 20px;
            font-weight: 900;
            line-height: 1.2;
        }

        /* Dark Mode for Stat Cards */
        .dark .em-stat-card.success {
            background: rgba(6, 78, 59, 0.25);
            border-color: rgba(16, 185, 129, 0.4);
            color: #a7f3d0;
        }
        .dark .em-stat-card.info {
            background: rgba(30, 58, 138, 0.25);
            border-color: rgba(59, 130, 246, 0.4);
            color: #bfdbfe;
        }
        .dark .em-stat-card.purple {
            background: rgba(107, 33, 168, 0.25);
            border-color: rgba(168, 85, 247, 0.4);
            color: #e9d5ff;
        }
        .dark .em-stat-card.warning {
            background: rgba(120, 53, 15, 0.25);
            border-color: rgba(245, 158, 11, 0.4);
            color: #fde68a;
        }
        .dark .em-stat-card.danger {
            background: rgba(136, 19, 55, 0.25);
            border-color: rgba(225, 29, 72, 0.4);
            color: #fecdd3;
        }

        /* Modern Table */
        .em-table-container {
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .dark .em-table-container {
            border-color: #334155;
            background: #0f172a;
        }
        .em-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: right;
        }
        .em-table th {
            padding: 12px 14px;
            background: #f8fafc;
            color: #475569;
            font-weight: 800;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .em-table th {
            background: #1e293b;
            color: #cbd5e1;
            border-bottom-color: #334155;
        }
        .em-table td {
            padding: 11px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
        }
        .dark .em-table td {
            border-bottom-color: #1e293b;
            color: #e2e8f0;
        }
        .em-table tbody tr {
            transition: background 0.15s ease;
        }
        .em-table tbody tr:hover {
            background: #f8fafc;
        }
        .dark .em-table tbody tr {
            background: #0f172a;
        }
        .dark .em-table tbody tr:hover {
            background: #1e293b;
        }

        .em-code-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            font-family: monospace;
            font-weight: 700;
            font-size: 11px;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .dark .em-code-badge {
            background: rgba(148, 163, 184, 0.15);
            color: #cbd5e1;
            border-color: rgba(148, 163, 184, 0.3);
        }

        .em-btn-grade {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            background: #3b82f6;
            color: #ffffff !important;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .em-btn-grade:hover {
            background: #2563eb;
            transform: scale(1.04);
        }

        .em-grade-panel {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            padding: 12px 14px;
            margin-top: 8px;
            display: none;
            animation: fadeIn 0.2s ease-in-out;
        }
        .dark .em-grade-panel {
            background: #1e293b;
            border-color: #475569;
        }

        .em-input-score {
            width: 80px;
            padding: 5px 8px;
            border-radius: 6px;
            border: 1.5px solid #cbd5e1;
            font-weight: 900;
            font-size: 13px;
            text-align: center;
            color: #0f172a;
        }
        .dark .em-input-score {
            background: #0f172a;
            border-color: #475569;
            color: #ffffff;
        }

        .em-input-feedback {
            flex: 1;
            min-width: 180px;
            padding: 5px 10px;
            border-radius: 6px;
            border: 1.5px solid #cbd5e1;
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
        }
        .dark .em-input-feedback {
            background: #0f172a;
            border-color: #475569;
            color: #ffffff;
        }

        .em-btn-save-grade {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 900;
            background: #059669;
            color: #ffffff !important;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .em-btn-save-grade:hover {
            background: #047857;
        }

        .em-btn-cancel-grade {
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            background: #64748b;
            color: #ffffff !important;
            border: none;
            cursor: pointer;
        }

        .em-wa-btn-emerald {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 800;
            background: #059669;
            color: #ffffff !important;
            text-decoration: none;
            border: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(5, 150, 105, 0.2);
        }
        .em-wa-btn-emerald:hover {
            background: #047857;
            transform: scale(1.04);
            color: #ffffff !important;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <!-- Header Stats -->
    <div class="em-stat-grid-5">
        <div class="em-stat-card success">
            <span class="label">👥 قاموا بالتسليم</span>
            <span class="value" id="stat-submitted-count">{{ $subsColl->count() }}</span>
        </div>
        <div class="em-stat-card info">
            <span class="label">📈 نسبة التسليم</span>
            <span class="value">{{ $stats['submission_rate'] ?? 0 }}%</span>
        </div>
        <div class="em-stat-card purple">
            <span class="label">✅ تم التصحيح</span>
            <span class="value" id="stat-graded-count">{{ $stats['graded_count'] ?? 0 }}</span>
        </div>
        <div class="em-stat-card warning">
            <span class="label">🎯 متوسط الدرجة</span>
            <span class="value" id="stat-avg-score">{{ $stats['average_score'] ?? 0 }}</span>
        </div>
        <div class="em-stat-card success">
            <span class="label">🌟 أعلى درجة</span>
            <span class="value">{{ $stats['highest_score'] ?? 0 }}</span>
        </div>
    </div>

    <!-- Alert Message Area -->
    <div id="hw-alert-box" style="display: none; padding: 10px 14px; border-radius: 10px; margin-bottom: 12px; font-weight: 800; font-size: 12px;"></div>

    <!-- Submissions Table -->
    @if($subsColl->count() > 0)
        <div class="em-table-container">
            <table class="em-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th>الطالب</th>
                        <th>الكود</th>
                        <th>وقت التسليم</th>
                        <th>الدرجة والتقييم</th>
                        <th>ملف الحل والملاحظات</th>
                        <th>هاتف ولي الأمر</th>
                        <th style="text-align: center;">إرسال التقييم</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subsColl as $idx => $st)
                        @php
                            $subId = $st['id'];
                            $parentPhone = $st['parent_phone'] ?? $st['phone'] ?? null;
                            $score = $st['score'];
                            $percentage = $st['percentage'];
                            $name = $st['name'] ?? 'طالب';
                            $code = $st['code'] ?? '—';
                            $submittedAt = $st['submitted_at'] ?? '—';
                            $isLate = $st['is_late'] ?? false;
                            $feedback = $st['feedback'] ?? null;
                            $attachment = $st['attachment'] ?? null;

                            $cleanedPhone = $parentPhone ? preg_replace('/[^0-9]/', '', $parentPhone) : null;
                            if ($cleanedPhone && !str_starts_with($cleanedPhone, '20') && strlen($cleanedPhone) === 11 && str_starts_with($cleanedPhone, '01')) {
                                $cleanedPhone = '20' . substr($cleanedPhone, 1);
                            }

                            $scoreText = $score !== null ? "{$score} من {$totalMarks} ({$percentage}%)" : 'قيد المراجعة والتصحيح';
                            $msg = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$name} 📝\n"
                                . "نحيطكم علماً بأنه تم استلام وتصحيح واجب ({$hwTitle}) في مادة ({$subjectName}):\n\n"
                                . "▪️ نتيجة الواجب: {$scoreText}\n"
                                . ($feedback ? "▪️ ملاحظات المدرس: {$feedback}\n" : "")
                                . "\n— {$centerName}";
                            $waUrl = $cleanedPhone ? "https://wa.me/{$cleanedPhone}?text=" . urlencode($msg) : null;
                        @endphp
                        <tr id="sub-row-{{ $subId }}">
                            <td style="text-align: center; font-weight: bold; opacity: 0.6;">{{ $idx + 1 }}</td>
                            <td style="font-weight: 800; font-size: 13px;">
                                {{ $name }}
                            </td>
                            <td>
                                <span class="em-code-badge">{{ $code }}</span>
                            </td>
                            <td style="font-family: monospace; font-size: 11px;">
                                <div>{{ $submittedAt }}</div>
                                @if($isLate)
                                    <span style="font-size: 10px; color: #e11d48; font-weight: bold;">(تسليم متأخر ⚠️)</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;" id="score-badge-box-{{ $subId }}">
                                    @if($score !== null)
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-family: monospace; font-weight: 900; font-size: 13px; color: #059669;">
                                                {{ $score }} / {{ $totalMarks }}
                                            </span>
                                            <span style="padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 800; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                                                {{ $percentage }}%
                                            </span>
                                        </div>
                                        <button type="button" class="em-btn-grade" onclick="toggleGradePanel({{ $subId }})" style="background: #e0f2fe; color: #0369a1 !important;">
                                            ✏️ تعديل
                                        </button>
                                    @else
                                        <span style="padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 800; background: #fffbeb; color: #92400e; border: 1px solid #fde68a;">
                                            قيد التصحيح ⏳
                                        </span>
                                        <button type="button" class="em-btn-grade" onclick="toggleGradePanel({{ $subId }})">
                                            ✍️ رصد الدرجة
                                        </button>
                                    @endif
                                </div>

                                <!-- Inline Grading Panel -->
                                <div id="grade-panel-{{ $subId }}" class="em-grade-panel">
                                    <div style="font-weight: 800; font-size: 11px; margin-bottom: 6px; color: #0284c7;">
                                        ✍️ رصد درجة الطالب: <span style="font-weight: 900;">{{ $name }}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <div style="display: flex; align-items: center; gap: 4px;">
                                            <input type="number" id="input-score-{{ $subId }}" class="em-input-score" 
                                                   min="0" max="{{ $totalMarks }}" step="0.5" 
                                                   value="{{ $score !== null ? $score : '' }}" 
                                                   placeholder="الدرجة" />
                                            <span style="font-weight: 800; font-size: 11px; opacity: 0.7;">/ {{ $totalMarks }}</span>
                                        </div>
                                        <input type="text" id="input-feedback-{{ $subId }}" class="em-input-feedback" 
                                               value="{{ $feedback ?? '' }}" 
                                               placeholder="ملاحظات وتوجيهات المدرس (اختياري)..." />
                                        <button type="button" class="em-btn-save-grade" id="btn-save-{{ $subId }}" onclick="submitQuickGrade({{ $subId }}, '{{ addslashes($name) }}', {{ $totalMarks }}, '{{ $cleanedPhone }}')">
                                            ✅ حفظ واعتماد
                                        </button>
                                        <button type="button" class="em-btn-cancel-grade" onclick="toggleGradePanel({{ $subId }})">
                                            ✖
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 11px;">
                                @if($attachment)
                                    <div style="margin-bottom: 4px;">
                                        <a href="{{ asset('storage/' . $attachment) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; color: #2563eb; font-weight: 900; background: rgba(37, 99, 235, 0.1); padding: 3px 8px; border-radius: 6px; text-decoration: none; border: 1px solid rgba(37, 99, 235, 0.25);">
                                            📎 معاينة ملف الحل ⬇️
                                        </a>
                                    </div>
                                @endif
                                <div id="feedback-text-{{ $subId }}" style="color: #475569; font-weight: 700;">
                                    @if($feedback)
                                        💬 {{ \Illuminate\Support\Str::limit($feedback, 40) }}
                                    @elseif(!$attachment)
                                        <span style="opacity: 0.4;">—</span>
                                    @endif
                                </div>
                            </td>
                            <td style="font-family: monospace; direction: ltr; font-weight: 700; text-align: right;">
                                {{ $parentPhone ?: '—' }}
                            </td>
                            <td style="text-align: center;">
                                @if($waUrl)
                                    <a id="wa-link-{{ $subId }}" href="{{ $waUrl }}" target="_blank" class="em-wa-btn-emerald">
                                        <span>💬</span>
                                        <span>إرسال التقييم</span>
                                    </a>
                                @else
                                    <span style="opacity: 0.5; font-size: 11px;">لا يوجد رقم</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="padding: 32px 16px; text-align: center; border-radius: 14px; border: 1px dashed #cbd5e1; background: rgba(241, 245, 249, 0.5);">
            <div style="font-size: 32px; margin-bottom: 8px;">📥</div>
            <h4 style="font-weight: 900; font-size: 15px; margin-bottom: 4px;">لم يقم أي طالب بتسليم هذا الواجب بعد</h4>
            <p style="font-size: 12px; opacity: 0.7; margin: 0;">يمكنك تذكير الطلاب وأولياء الأمور عبر زر "الطلاب الذين لم يسلموا".</p>
        </div>
    @endif
</div>

<script>
    function toggleGradePanel(subId) {
        const panel = document.getElementById('grade-panel-' + subId);
        if (!panel) return;
        if (panel.style.display === 'block') {
            panel.style.display = 'none';
        } else {
            panel.style.display = 'block';
            const input = document.getElementById('input-score-' + subId);
            if (input) input.focus();
        }
    }

    function submitQuickGrade(subId, studentName, totalMarks, parentPhone) {
        const scoreInput = document.getElementById('input-score-' + subId);
        const feedbackInput = document.getElementById('input-feedback-' + subId);
        const saveBtn = document.getElementById('btn-save-' + subId);
        const alertBox = document.getElementById('hw-alert-box');

        const scoreVal = scoreInput ? scoreInput.value.trim() : '';
        const feedbackVal = feedbackInput ? feedbackInput.value.trim() : '';

        if (scoreVal === '') {
            alert('يرجى إدخال درجة الطالب أولاً');
            if (scoreInput) scoreInput.focus();
            return;
        }

        const numScore = parseFloat(scoreVal);
        if (isNaN(numScore) || numScore < 0 || numScore > totalMarks) {
            alert(`الدرجة يجب أن تكون رقماً بين 0 و ${totalMarks}`);
            if (scoreInput) scoreInput.focus();
            return;
        }

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '⏳ جاري الحفظ...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                       || '{{ csrf_token() }}';

        fetch(`/admin/homework/submissions/${subId}/grade`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                score: numScore,
                feedback: feedbackVal
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'فشل حفظ الدرجة'); });
            }
            return response.json();
        })
        .then(res => {
            if (res.success) {
                // 1. تحديث شارة الدرجة
                const badgeBox = document.getElementById('score-badge-box-' + subId);
                if (badgeBox) {
                    badgeBox.innerHTML = `
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-family: monospace; font-weight: 900; font-size: 13px; color: #059669;">
                                ${res.score} / ${res.total_marks}
                            </span>
                            <span style="padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 800; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                                ${res.percentage}%
                            </span>
                        </div>
                        <button type="button" class="em-btn-grade" onclick="toggleGradePanel(${subId})" style="background: #e0f2fe; color: #0369a1 !important;">
                            ✏️ تعديل
                        </button>
                    `;
                }

                // 2. تحديث نص الملاحظات
                const feedbackBox = document.getElementById('feedback-text-' + subId);
                if (feedbackBox) {
                    feedbackBox.innerHTML = res.feedback ? `💬 ${res.feedback}` : '';
                }

                // 3. تحديث رابط الواتساب
                const waLink = document.getElementById('wa-link-' + subId);
                if (waLink && parentPhone) {
                    const scoreText = `${res.score} من ${res.total_marks} (${res.percentage}%)`;
                    const centerName = '{{ addslashes($centerName) }}';
                    const hwTitle = '{{ addslashes($hwTitle) }}';
                    const subjectName = '{{ addslashes($subjectName) }}';
                    
                    let msg = `السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: ${studentName} 📝\n`
                            + `نحيطكم علماً بأنه تم استلام وتصحيح واجب (${hwTitle}) في مادة (${subjectName}):\n\n`
                            + `▪️ نتيجة الواجب: ${scoreText}\n`;
                    if (res.feedback) {
                        msg += `▪️ ملاحظات المدرس: ${res.feedback}\n`;
                    }
                    msg += `\n— ${centerName}`;
                    
                    waLink.href = `https://wa.me/${parentPhone}?text=` + encodeURIComponent(msg);
                }

                // 4. إغلاق لوحة الرصد
                const panel = document.getElementById('grade-panel-' + subId);
                if (panel) panel.style.display = 'none';

                // 5. إظهار رسالة النجاح
                if (alertBox) {
                    alertBox.style.display = 'block';
                    alertBox.style.background = '#ecfdf5';
                    alertBox.style.border = '1px solid #a7f3d0';
                    alertBox.style.color = '#065f46';
                    alertBox.innerHTML = `✅ تم رصد درجة الطالب/ة (${studentName}) بنجاح: ${res.score} من ${res.total_marks}`;
                    setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
                }
            }
        })
        .catch(err => {
            alert('خطأ: ' + err.message);
        })
        .finally(() => {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '✅ حفظ واعتماد';
            }
        });
    }
</script>
