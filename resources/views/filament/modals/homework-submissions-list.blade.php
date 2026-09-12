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

<div class="modal-content-wrapper text-right" 
     x-data="{
        openSubId: null,
        scores: {
            @foreach($subsColl as $st)
                {{ $st['id'] }}: '{{ $st['score'] !== null ? $st['score'] : '' }}',
            @endforeach
        },
        feedbacks: {
            @foreach($subsColl as $st)
                {{ $st['id'] }}: '{{ addslashes($st['feedback'] ?? '') }}',
            @endforeach
        },
        displayScores: {
            @foreach($subsColl as $st)
                {{ $st['id'] }}: {{ $st['score'] !== null ? $st['score'] : 'null' }},
            @endforeach
        },
        displayPercentages: {
            @foreach($subsColl as $st)
                {{ $st['id'] }}: {{ $st['percentage'] !== null ? $st['percentage'] : 'null' }},
            @endforeach
        },
        displayFeedbacks: {
            @foreach($subsColl as $st)
                {{ $st['id'] }}: '{{ addslashes($st['feedback'] ?? '') }}',
            @endforeach
        },
        loadingSubId: null,
        alertMessage: '',
        togglePanel(id) {
            this.openSubId = (this.openSubId === id) ? null : id;
        },
        async saveGrade(subId, maxMarks, name, parentPhone) {
            const rawScore = this.scores[subId];
            const feedback = this.feedbacks[subId] || '';

            if (rawScore === undefined || rawScore === '' || rawScore === null) {
                alert('يرجى كتابة درجة الطالب أولاً');
                return;
            }

            const numScore = parseFloat(rawScore);
            if (isNaN(numScore) || numScore < 0 || numScore > maxMarks) {
                alert('الدرجة يجب أن تكون رقماً بين 0 و ' + maxMarks);
                return;
            }

            this.loadingSubId = subId;
            try {
                const response = await fetch('/admin/homework/submissions/' + subId + '/grade', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        score: numScore,
                        feedback: feedback
                    })
                });

                const res = await response.json();
                if (!response.ok || !res.success) {
                    throw new Error(res.message || 'حدث خطأ أثناء حفظ الدرجة');
                }

                // تحديث القيم تفاعلياً
                this.displayScores[subId] = res.score;
                this.displayPercentages[subId] = res.percentage;
                this.displayFeedbacks[subId] = res.feedback || '';
                this.openSubId = null;

                // تحديث رابط الواتساب
                const waEl = document.getElementById('wa-link-' + subId);
                if (waEl && parentPhone) {
                    const scoreText = res.score + ' من ' + res.total_marks + ' (' + res.percentage + '%)';
                    const centerName = '{{ addslashes($centerName) }}';
                    const hwTitle = '{{ addslashes($hwTitle) }}';
                    const subjectName = '{{ addslashes($subjectName) }}';
                    let msg = 'السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: ' + name + ' 📝\n'
                            + 'نحيطكم علماً بأنه تم استلام وتصحيح واجب (' + hwTitle + ') في مادة (' + subjectName + '):\n\n'
                            + '▪️ نتيجة الواجب: ' + scoreText + '\n';
                    if (res.feedback) {
                        msg += '▪️ ملاحظات المدرس: ' + res.feedback + '\n';
                    }
                    msg += '\n— ' + centerName;
                    waEl.href = 'https://wa.me/' + parentPhone + '?text=' + encodeURIComponent(msg);
                }

                this.alertMessage = '✅ تم رصد درجة الطالب/ة (' + name + ') بنجاح: ' + res.score + ' من ' + res.total_marks;
                setTimeout(() => { this.alertMessage = ''; }, 4500);
            } catch (err) {
                alert('خطأ: ' + err.message);
            } finally {
                this.loadingSubId = null;
            }
        }
     }">
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
            border: 1.5px solid #e2e8f0;
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
            font-weight: 900;
            border-bottom: 1.5px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .em-table th {
            background: #1e293b;
            color: #cbd5e1;
            border-bottom-color: #334155;
        }
        .em-table td {
            padding: 12px 14px;
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
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 900;
            background: #2563eb;
            color: #ffffff !important;
            border: none;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3);
            transition: all 0.15s ease;
        }
        .em-btn-grade:hover {
            background: #1d4ed8;
            transform: scale(1.04);
        }

        .em-grade-panel {
            background: #f8fafc;
            border: 1.5px solid #93c5fd;
            border-radius: 12px;
            padding: 12px 14px;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }
        .dark .em-grade-panel {
            background: #1e293b;
            border-color: #3b82f6;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }

        .em-input-score {
            width: 85px;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            font-weight: 900;
            font-size: 14px;
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
            padding: 6px 12px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            font-size: 12px;
            font-weight: 700;
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
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 900;
            background: #059669;
            color: #ffffff !important;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(5, 150, 105, 0.3);
            transition: all 0.15s ease;
        }
        .em-btn-save-grade:hover {
            background: #047857;
            transform: scale(1.02);
        }

        .em-btn-cancel-grade {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            background: #64748b;
            color: #ffffff !important;
            border: none;
            cursor: pointer;
        }
        .em-btn-cancel-grade:hover {
            background: #475569;
        }

        .em-wa-btn-emerald {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 900;
            background: #059669;
            color: #ffffff !important;
            text-decoration: none;
            border: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(5, 150, 105, 0.25);
        }
        .em-wa-btn-emerald:hover {
            background: #047857;
            transform: scale(1.04);
            color: #ffffff !important;
        }
    </style>

    <!-- Header Stats -->
    <div class="em-stat-grid-5">
        <div class="em-stat-card success">
            <span class="label">👥 قاموا بالتسليم</span>
            <span class="value">{{ $subsColl->count() }}</span>
        </div>
        <div class="em-stat-card info">
            <span class="label">📈 نسبة التسليم</span>
            <span class="value">{{ $stats['submission_rate'] ?? 0 }}%</span>
        </div>
        <div class="em-stat-card purple">
            <span class="label">✅ تم التصحيح</span>
            <span class="value">{{ $stats['graded_count'] ?? 0 }}</span>
        </div>
        <div class="em-stat-card warning">
            <span class="label">🎯 متوسط الدرجة</span>
            <span class="value">{{ $stats['average_score'] ?? 0 }}</span>
        </div>
        <div class="em-stat-card success">
            <span class="label">🌟 أعلى درجة</span>
            <span class="value">{{ $stats['highest_score'] ?? 0 }}</span>
        </div>
    </div>

    <!-- Alert Message Area -->
    <div x-show="alertMessage" 
         x-transition
         style="display: none; padding: 12px 16px; border-radius: 10px; margin-bottom: 14px; font-weight: 900; font-size: 13px; background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46;" 
         x-text="alertMessage"></div>

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
                        <tr>
                            <td style="text-align: center; font-weight: bold; opacity: 0.6;">{{ $idx + 1 }}</td>
                            <td style="font-weight: 900; font-size: 13px;">
                                {{ $name }}
                            </td>
                            <td>
                                <span class="em-code-badge">{{ $code }}</span>
                            </td>
                            <td style="font-family: monospace; font-size: 11px;">
                                <div style="font-weight: 800;">{{ $submittedAt }}</div>
                                @if($isLate)
                                    <span style="font-size: 10px; color: #e11d48; font-weight: 900;">(تسليم متأخر ⚠️)</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <template x-if="displayScores[{{ $subId }}] !== null">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-family: monospace; font-weight: 900; font-size: 13px; color: #059669;"
                                                  x-text="displayScores[{{ $subId }}] + ' / {{ $totalMarks }}'"></span>
                                            <span style="padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 900; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;"
                                                  x-text="displayPercentages[{{ $subId }}] + '%'"></span>
                                            <button type="button" class="em-btn-grade" @click="togglePanel({{ $subId }})" style="background: #e0f2fe; color: #0369a1 !important;">
                                                ✏️ تعديل
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="displayScores[{{ $subId }}] === null">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 900; background: #fffbeb; color: #92400e; border: 1px solid #fde68a;">
                                                قيد التصحيح ⏳
                                            </span>
                                            <button type="button" class="em-btn-grade" @click="togglePanel({{ $subId }})">
                                                ✍️ رصد الدرجة
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <!-- Inline Grading Panel (Alpine Controlled) -->
                                <div x-show="openSubId === {{ $subId }}" 
                                     x-transition
                                     style="display: none;" 
                                     class="em-grade-panel">
                                    <div style="font-weight: 900; font-size: 11px; margin-bottom: 8px; color: #0284c7;">
                                        ✍️ رصد درجة الطالب: <span style="font-weight: 900; color: #0f172a;" class="dark:text-white">{{ $name }}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <div style="display: flex; align-items: center; gap: 4px;">
                                            <input type="number" 
                                                   x-model="scores[{{ $subId }}]" 
                                                   class="em-input-score" 
                                                   min="0" max="{{ $totalMarks }}" step="0.5" 
                                                   placeholder="الدرجة" />
                                            <span style="font-weight: 900; font-size: 11px; opacity: 0.8;">/ {{ $totalMarks }}</span>
                                        </div>
                                        <input type="text" 
                                               x-model="feedbacks[{{ $subId }}]" 
                                               class="em-input-feedback" 
                                               placeholder="ملاحظات وتوجيهات المدرس للطالب (اختياري)..." />
                                        <button type="button" 
                                                class="em-btn-save-grade" 
                                                :disabled="loadingSubId === {{ $subId }}"
                                                @click="saveGrade({{ $subId }}, {{ $totalMarks }}, '{{ addslashes($name) }}', '{{ $cleanedPhone }}')">
                                            <span x-show="loadingSubId !== {{ $subId }}">✅ حفظ واعتماد</span>
                                            <span x-show="loadingSubId === {{ $subId }}">⏳ جاري الحفظ...</span>
                                        </button>
                                        <button type="button" class="em-btn-cancel-grade" @click="togglePanel({{ $subId }})">
                                            ✖ إلغاء
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 11px;">
                                @if($attachment)
                                    <div style="margin-bottom: 5px;">
                                        <a href="{{ asset('storage/' . $attachment) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; color: #2563eb; font-weight: 900; background: rgba(37, 99, 235, 0.1); padding: 3px 8px; border-radius: 6px; text-decoration: none; border: 1px solid rgba(37, 99, 235, 0.25);">
                                            📎 معاينة ملف الحل ⬇️
                                        </a>
                                    </div>
                                @endif
                                <div style="color: #475569; font-weight: 700;" class="dark:text-slate-300">
                                    <span x-show="displayFeedbacks[{{ $subId }}]" x-text="'💬 ' + displayFeedbacks[{{ $subId }}]"></span>
                                    <span x-show="!displayFeedbacks[{{ $subId }}] && !'{{ $attachment }}'" style="opacity: 0.4;">—</span>
                                </div>
                            </td>
                            <td style="font-family: monospace; direction: ltr; font-weight: 800; text-align: right;">
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
