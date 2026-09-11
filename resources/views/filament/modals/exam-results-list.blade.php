@props([
    'attendees' => [],
    'exam' => null,
    'stats' => [],
])

@php
    $centerName = app(\App\Services\SettingService::class)->get('center_name', 'المنظومة التعليمية');
    $examTitle = $exam?->title ?? 'الامتحان';
    $subjectName = $exam?->subject?->name ?? 'المادة';
    $totalMarks = $exam?->total_marks ?? 100;
    $examDate = $exam?->date ? \Carbon\Carbon::parse($exam->date)->format('Y-m-d') : now()->toDateString();
    $attendeesColl = collect($attendees);
@endphp

<div class="modal-content-wrapper text-right">
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
        .rank-gold {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 900;
        }
        .dark .rank-gold {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.4);
        }
        .rank-silver {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 900;
        }
        .dark .rank-silver {
            background: rgba(148, 163, 184, 0.2);
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, 0.4);
        }
        .rank-bronze {
            background: #ffedd5;
            color: #9a3412;
            border: 1px solid #fed7aa;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 900;
        }
        .dark .rank-bronze {
            background: rgba(234, 88, 12, 0.2);
            color: #fdba74;
            border-color: rgba(234, 88, 12, 0.4);
        }
    </style>

    <!-- Header Stats -->
    <div class="em-stat-grid-5">
        <div class="em-stat-card success">
            <span class="label">👥 الممتحنين</span>
            <span class="value">{{ $attendeesColl->count() }}</span>
        </div>
        <div class="em-stat-card info">
            <span class="label">📈 نسبة النجاح</span>
            <span class="value">{{ $stats['pass_rate'] ?? 0 }}%</span>
        </div>
        <div class="em-stat-card purple">
            <span class="label">🎯 متوسط الدرجات</span>
            <span class="value">{{ $stats['average_mark'] ?? 0 }}</span>
        </div>
        <div class="em-stat-card warning">
            <span class="label">🌟 أعلى درجة</span>
            <span class="value">{{ $stats['highest_mark'] ?? 0 }}</span>
        </div>
        <div class="em-stat-card danger">
            <span class="label">⚠️ الراسبين</span>
            <span class="value">{{ $stats['fail_count'] ?? 0 }}</span>
        </div>
    </div>

    <!-- Attendees Table -->
    @if($attendeesColl->count() > 0)
        <div class="em-table-container">
            <table class="em-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">الترتيب</th>
                        <th>الطالب</th>
                        <th>الكود</th>
                        <th>الدرجة المحصلة</th>
                        <th>النسبة والتقدير</th>
                        <th>هاتف ولي الأمر</th>
                        <th style="text-align: center;">إرسال النتيجة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendeesColl as $idx => $st)
                        @php
                            $rank = $st['rank'] ?? ($idx + 1);
                            $parentPhone = $st['parent_phone'] ?? $st['phone'] ?? null;
                            $marks = $st['marks_obtained'] ?? 0;
                            $percentage = $st['percentage'] ?? 0;
                            $passed = $st['passed'] ?? true;
                            $gradeText = $st['grade_text'] ?? '—';
                            $name = $st['name'] ?? 'طالب';
                            $code = $st['code'] ?? '—';

                            $cleanedPhone = $parentPhone ? preg_replace('/[^0-9]/', '', $parentPhone) : null;
                            if ($cleanedPhone && !str_starts_with($cleanedPhone, '20') && strlen($cleanedPhone) === 11 && str_starts_with($cleanedPhone, '01')) {
                                $cleanedPhone = '20' . substr($cleanedPhone, 1);
                            }

                            $statusIcon = $passed ? '🎉' : '⚠️';
                            $msg = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$name} 📊\n"
                                . "نرسل لكم بطاقة نتيجة امتحان ({$examTitle}) في مادة ({$subjectName}):\n\n"
                                . "▪️ الدرجة: {$marks} من {$totalMarks}\n"
                                . "▪️ النسبة المئوية: {$percentage}%\n"
                                . "▪️ التقدير والتقييم: {$gradeText}\n"
                                . "▪️ الترتيب على الدفعة: المركز #{$rank} {$statusIcon}\n\n"
                                . "— {$centerName}";
                            $waUrl = $cleanedPhone ? "https://wa.me/{$cleanedPhone}?text=" . urlencode($msg) : null;
                        @endphp
                        <tr>
                            <td style="text-align: center;">
                                @if($rank === 1)
                                    <span class="rank-gold">🥇 الأول</span>
                                @elseif($rank === 2)
                                    <span class="rank-silver">🥈 الثاني</span>
                                @elseif($rank === 3)
                                    <span class="rank-bronze">🥉 الثالث</span>
                                @else
                                    <span style="font-family: monospace; font-weight: bold; opacity: 0.6;">#{{ $rank }}</span>
                                @endif
                            </td>
                            <td style="font-weight: 800; font-size: 13px;">
                                {{ $name }}
                            </td>
                            <td>
                                <span class="em-code-badge">{{ $code }}</span>
                            </td>
                            <td style="font-family: monospace; font-weight: 900; font-size: 13px;">
                                <span style="color: {{ $passed ? '#059669' : '#e11d48' }};">
                                    {{ $marks }}
                                </span>
                                <span style="opacity: 0.5; font-size: 11px;">/ {{ $totalMarks }}</span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-family: monospace; font-weight: 800; font-size: 11px; color: {{ $passed ? '#059669' : '#e11d48' }};">
                                        {{ $percentage }}%
                                    </span>
                                    <span style="padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 800; background: {{ $passed ? '#ecfdf5' : '#fff1f2' }}; color: {{ $passed ? '#065f46' : '#9f1239' }}; border: 1px solid {{ $passed ? '#a7f3d0' : '#fecdd3' }};">
                                        {{ $gradeText }}
                                    </span>
                                </div>
                            </td>
                            <td style="font-family: monospace; direction: ltr; font-weight: 700; text-align: right;">
                                {{ $parentPhone ?: '—' }}
                            </td>
                            <td style="text-align: center;">
                                @if($waUrl)
                                    <a href="{{ $waUrl }}" target="_blank" class="em-wa-btn-emerald">
                                        <span>💬</span>
                                        <span>إرسال النتيجة</span>
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
            <div style="font-size: 32px; margin-bottom: 8px;">📝</div>
            <h4 style="font-weight: 900; font-size: 15px; margin-bottom: 4px;">لم يتم رصد نتائج لهذا الامتحان بعد</h4>
            <p style="font-size: 12px; opacity: 0.7; margin: 0;">يمكنك رصد الدرجات عبر زر "رصد يدوي" في جدول الامتحانات.</p>
        </div>
    @endif
</div>
