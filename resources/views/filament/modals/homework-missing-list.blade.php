@props([
    'missingStudents' => [],
    'homework' => null,
    'stats' => [],
])

@php
    $centerName = app(\App\Services\SettingService::class)->get('center_name', 'المنظومة التعليمية');
    $hwTitle = $homework?->title ?? 'الواجب';
    $subjectName = $homework?->subject?->name ?? 'المادة';
    $dueDate = $homework?->due_date ? \Carbon\Carbon::parse($homework->due_date)->format('Y-m-d h:i A') : 'غير محدد';
    $isOverdue = $homework?->isOverdue() ?? false;
    $missingColl = collect($missingStudents);
@endphp

<div class="modal-content-wrapper text-right">
    <style>
        .em-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .em-stat-card {
            border-radius: 14px;
            padding: 12px 14px;
            text-align: center;
            border: 1px solid;
            transition: transform 0.2s ease;
        }
        .em-stat-card:hover {
            transform: translateY(-2px);
        }
        .em-stat-card.danger {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #9f1239;
        }
        .em-stat-card.info {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1e40af;
        }
        .em-stat-card.success {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .em-stat-card.warning {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }
        .em-stat-card .label {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 4px;
            display: block;
            opacity: 0.9;
        }
        .em-stat-card .value {
            font-size: 22px;
            font-weight: 900;
            line-height: 1.2;
        }

        /* Dark Mode for Stat Cards */
        .dark .em-stat-card.danger {
            background: rgba(136, 19, 55, 0.25);
            border-color: rgba(225, 29, 72, 0.4);
            color: #fecdd3;
        }
        .dark .em-stat-card.info {
            background: rgba(30, 58, 138, 0.25);
            border-color: rgba(59, 130, 246, 0.4);
            color: #bfdbfe;
        }
        .dark .em-stat-card.success {
            background: rgba(6, 78, 59, 0.25);
            border-color: rgba(16, 185, 129, 0.4);
            color: #a7f3d0;
        }
        .dark .em-stat-card.warning {
            background: rgba(120, 53, 15, 0.25);
            border-color: rgba(245, 158, 11, 0.4);
            color: #fde68a;
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
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .dark .em-code-badge {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.3);
        }
        .em-wa-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 800;
            background: #e11d48;
            color: #ffffff !important;
            text-decoration: none;
            border: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(225, 29, 72, 0.2);
        }
        .em-wa-btn:hover {
            background: #be123c;
            transform: scale(1.04);
            color: #ffffff !important;
        }
    </style>

    <!-- Header Stats -->
    <div class="em-stat-grid">
        <div class="em-stat-card danger">
            <span class="label">⚠️ لم يسلموا الواجب</span>
            <span class="value">{{ $missingColl->count() }}</span>
        </div>
        <div class="em-stat-card info">
            <span class="label">👥 إجمالي المستهدفين</span>
            <span class="value">
                {{ $stats['total_target_students'] ?? ($missingColl->count() + ($stats['submitted_count'] ?? 0)) }}
            </span>
        </div>
        <div class="em-stat-card success">
            <span class="label">✅ الذين قاموا بالتسليم</span>
            <span class="value">
                {{ $stats['submitted_count'] ?? 0 }}
            </span>
        </div>
        <div class="em-stat-card warning">
            <span class="label">📊 نسبة التسليم</span>
            <span class="value">
                {{ $stats['submission_rate'] ?? 0 }}%
            </span>
        </div>
    </div>

    <!-- Missing Students Table -->
    @if($missingColl->count() > 0)
        <div class="em-table-container">
            <table class="em-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th>اسم الطالب</th>
                        <th>الكود</th>
                        <th>المرحلة / المجموعة</th>
                        <th>هاتف ولي الأمر</th>
                        <th style="text-align: center;">تواصل مباشر وتنبيه</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($missingColl as $index => $st)
                        @php
                            $parentPhone = is_array($st) ? ($st['parent_phone'] ?? $st['phone'] ?? null) : ($st->parent_phone ?? $st->phone ?? null);
                            $name = is_array($st) ? $st['name'] : $st->name;
                            $code = is_array($st) ? ($st['code'] ?? '') : ($st->qr_code ?: ('STD-' . $st->id));
                            $stageName = is_array($st) ? ($st['stage_name'] ?? '') : ($st->educationalStage?->name ?? '');
                            $groupName = is_array($st) ? ($st['group_name'] ?? '') : ($st->group?->name ?? '');

                            $cleanedPhone = $parentPhone ? preg_replace('/[^0-9]/', '', $parentPhone) : null;
                            if ($cleanedPhone && !str_starts_with($cleanedPhone, '20') && strlen($cleanedPhone) === 11 && str_starts_with($cleanedPhone, '01')) {
                                $cleanedPhone = '20' . substr($cleanedPhone, 1);
                            }

                            $msg = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$name} ⚠️\n"
                                . "نود تذكيركم بأن الطالب لم يقم بعد بتسليم واجب ({$hwTitle}) في مادة ({$subjectName}).\n"
                                . "⏰ موعد التسليم: {$dueDate}\n"
                                . "يرجى حث الطالب على حل الواجب وتسليمه عبر بوابة الطالب.\n\n"
                                . "— {$centerName}";
                            $waUrl = $cleanedPhone ? "https://wa.me/{$cleanedPhone}?text=" . urlencode($msg) : null;
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold; opacity: 0.6;">{{ $index + 1 }}</td>
                            <td style="font-weight: 800; font-size: 13px;">
                                {{ $name }}
                            </td>
                            <td>
                                <span class="em-code-badge">{{ $code }}</span>
                            </td>
                            <td style="opacity: 0.85;">
                                {{ $stageName }} @if($groupName && $groupName !== 'جميع المجموعات') - ({{ $groupName }}) @endif
                            </td>
                            <td style="font-family: monospace; direction: ltr; font-weight: 700; text-align: right;">
                                {{ $parentPhone ?: '—' }}
                            </td>
                            <td style="text-align: center;">
                                @if($waUrl)
                                    <a href="{{ $waUrl }}" target="_blank" class="em-wa-btn">
                                        <span>💬</span>
                                        <span>تنبيه بالواتساب</span>
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
        <div style="padding: 32px 16px; text-align: center; border-radius: 14px; border: 1px dashed #10b981; background: rgba(16, 185, 129, 0.08);">
            <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
            <h4 style="font-weight: 900; font-size: 15px; margin-bottom: 4px; color: #059669;">ممتاز! جميع الطلاب قاموا بتسليم الواجب</h4>
            <p style="font-size: 12px; opacity: 0.75; margin: 0;">لا يوجد أي طلاب مقصرين في هذا الواجب والتكليف.</p>
        </div>
    @endif
</div>
