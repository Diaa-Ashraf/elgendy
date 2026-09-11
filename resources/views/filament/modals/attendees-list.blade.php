@props([
    'attendees' => [],
    'session' => null,
])

@php
    $centerName = app(\App\Services\SettingService::class)->get('center_name', 'المنظومة التعليمية');
    $sessionTitle = $session?->topic ?? $session?->title ?? 'الحصة الدراسية';
    $attendeesColl = collect($attendees);
    $totalGroupStudents = $session?->group?->students()->wherePivot('status', 'active')->count() ?? $session?->group?->students()->count() ?? 0;
    if ($totalGroupStudents === 0) {
        $totalGroupStudents = $attendeesColl->count();
    }
    $presentCount = $attendeesColl->count();
    $absentCount = max(0, $totalGroupStudents - $presentCount);
    $attendanceRate = $totalGroupStudents > 0 ? round(($presentCount / $totalGroupStudents) * 100) : 0;
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
            font-size: 22px;
            font-weight: 900;
            line-height: 1.2;
        }

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
        .em-wa-btn {
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
        .em-wa-btn:hover {
            background: #047857;
            transform: scale(1.04);
            color: #ffffff !important;
        }
    </style>

    <!-- Header Stats -->
    <div class="em-stat-grid">
        <div class="em-stat-card success">
            <span class="label">👥 إجمالي الحاضرين</span>
            <span class="value">{{ $presentCount }}</span>
        </div>
        <div class="em-stat-card info">
            <span class="label">👥 إجمالي طلاب المجموعة</span>
            <span class="value">{{ $totalGroupStudents }}</span>
        </div>
        <div class="em-stat-card danger">
            <span class="label">⚠️ الطلاب الغائبين</span>
            <span class="value">{{ $absentCount }}</span>
        </div>
        <div class="em-stat-card warning">
            <span class="label">📊 نسبة الحضور</span>
            <span class="value">{{ $attendanceRate }}%</span>
        </div>
    </div>

    <!-- Attendees Table -->
    @if($attendeesColl->count() > 0)
        <div class="em-table-container">
            <table class="em-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th>الطالب</th>
                        <th>الكود</th>
                        <th>وقت الحضور</th>
                        <th>الحالة</th>
                        <th>هاتف ولي الأمر</th>
                        <th style="text-align: center;">تواصل مباشر</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendeesColl as $idx => $att)
                        @php
                            $isArr = is_array($att);
                            $name = $isArr ? ($att['name'] ?? 'طالب') : ($att->student?->name ?? 'طالب');
                            $code = $isArr ? ($att['code'] ?? '-') : ($att->student?->code ?? $att->student?->qr_code ?? '-');
                            $time = $isArr ? ($att['time'] ?? '-') : ($att->check_in_time ?? $att->checked_in_at ? \Carbon\Carbon::parse($att->checked_in_at)->format('h:i A') : '-');
                            $status = $isArr ? ($att['status'] ?? 'present') : ($att->status ?? 'present');
                            $parentPhone = $isArr ? ($att['parent_phone'] ?? $att['phone'] ?? null) : ($att->student?->parent_phone ?? $att->student?->phone ?? null);

                            $cleanedPhone = $parentPhone ? preg_replace('/[^0-9]/', '', $parentPhone) : null;
                            if ($cleanedPhone && !str_starts_with($cleanedPhone, '20') && strlen($cleanedPhone) === 11 && str_starts_with($cleanedPhone, '01')) {
                                $cleanedPhone = '20' . substr($cleanedPhone, 1);
                            }
                            $msgText = urlencode("السلام عليكم ورحمة الله، ولي أمر الطالب/ة: {$name} ✅\nنود إعلامكم بتسجيل حضور الطالب للحصة ({$sessionTitle}) بنجاح.\n— {$centerName}");
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold; opacity: 0.6;">{{ $idx + 1 }}</td>
                            <td style="font-weight: 800; font-size: 13px;">
                                {{ $name }}
                            </td>
                            <td>
                                <span class="em-code-badge">{{ $code }}</span>
                            </td>
                            <td style="font-family: monospace; font-size: 11px;">
                                {{ $time }}
                            </td>
                            <td>
                                @if($status === 'present')
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                                        حاضر ✅
                                    </span>
                                @elseif($status === 'late')
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; background: #fffbeb; color: #92400e; border: 1px solid #fde68a;">
                                        متأخر ⏰
                                    </span>
                                @else
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">
                                        {{ $status }}
                                    </span>
                                @endif
                            </td>
                            <td style="font-family: monospace; direction: ltr; font-weight: 700; text-align: right;">
                                {{ $parentPhone ?? '-' }}
                            </td>
                            <td style="text-align: center;">
                                @if($cleanedPhone)
                                    <a href="https://wa.me/{{ $cleanedPhone }}?text={{ $msgText }}" target="_blank" class="em-wa-btn">
                                        <span>💬</span>
                                        <span>واتساب</span>
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
            <div style="font-size: 32px; margin-bottom: 8px;">👥</div>
            <h4 style="font-weight: 900; font-size: 15px; margin-bottom: 4px;">لم يسجل أي طالب حضوره في هذه الحصة حتى الآن</h4>
            <p style="font-size: 12px; opacity: 0.7; margin: 0;">يمكنك تسجيل الحضور عبر ماسح الـ QR أو القائمة السريعة.</p>
        </div>
    @endif
</div>
