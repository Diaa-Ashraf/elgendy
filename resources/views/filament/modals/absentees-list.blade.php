@php
    $centerName = app(\App\Services\SettingService::class)->get('center_name', 'المنظومة التعليمية');
    $sessionDate = \Carbon\Carbon::parse($session->date)->format('Y-m-d');
    $groupName = $session->group?->name ?? 'المجموعة';
    $absenteesList = $absentees ?? [];
    $totalGroupStudents = $session->group?->students()->wherePivot('status', 'active')->count() ?? $session->group?->students()->count() ?? 0;
    if ($totalGroupStudents === 0) {
        $totalGroupStudents = count($absenteesList);
    }
    $absentCount = count($absenteesList);
    $presentCount = max(0, $totalGroupStudents - $absentCount);
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
        <div class="em-stat-card danger">
            <span class="label">⚠️ الطلاب الغائبين</span>
            <span class="value">{{ $absentCount }}</span>
        </div>
        <div class="em-stat-card info">
            <span class="label">👥 إجمالي طلاب المجموعة</span>
            <span class="value">{{ $totalGroupStudents }}</span>
        </div>
        <div class="em-stat-card success">
            <span class="label">✅ الطلاب الحاضرين</span>
            <span class="value">{{ $presentCount }}</span>
        </div>
        <div class="em-stat-card warning">
            <span class="label">📊 نسبة الحضور</span>
            <span class="value">{{ $attendanceRate }}%</span>
        </div>
    </div>

    <!-- Absentees Table -->
    @if(count($absenteesList) > 0)
        <div class="em-table-container">
            <table class="em-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th>اسم الطالب</th>
                        <th>الكود</th>
                        <th>هاتف ولي الأمر</th>
                        <th>حالة الواتساب</th>
                        <th style="text-align: center;">محادثة واتساب مباشرة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($absenteesList as $index => $st)
                        @php
                            $msg = "السلام عليكم ورحمة الله، المكرم ولي أمر الطالب/ة: {$st['name']} ⚠️\n"
                                . "نحيطكم علماً بعدم حضور ابنكم لحصة اليوم ({$groupName}) بتاريخ: {$sessionDate}.\n"
                                . "يرجى التواصل معنا للإفادة بالعذر أو التنسيق للحصة القادمة.\n\n"
                                . "— {$centerName}";
                            $waUrl = !empty($st['parent_phone']) ? \App\Services\WhatsAppNotificationService::getWhatsAppUrl($st['parent_phone'], $msg) : null;
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold; opacity: 0.6;">{{ $index + 1 }}</td>
                            <td style="font-weight: 800; font-size: 13px;">
                                {{ $st['name'] }}
                            </td>
                            <td>
                                <span class="em-code-badge">{{ $st['code'] }}</span>
                            </td>
                            <td style="font-family: monospace; direction: ltr; font-weight: 700; text-align: right;">
                                {{ $st['parent_phone'] ?: '—' }}
                            </td>
                            <td>
                                @if(!empty($st['whatsapp_sent_at']))
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                                        ✅ تم ({{ $st['whatsapp_sent_at'] }})
                                    </span>
                                @else
                                    <span style="opacity: 0.5; font-size: 11px;">لم يُرسل بعد</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($waUrl)
                                    <a href="{{ $waUrl }}" target="_blank" class="em-wa-btn">
                                        <span>💬</span>
                                        <span>فتح واتساب</span>
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
            <h4 style="font-weight: 900; font-size: 15px; margin-bottom: 4px; color: #059669;">ممتاز! لا يوجد أي طلاب غائبين في هذه الحصة</h4>
            <p style="font-size: 12px; opacity: 0.75; margin: 0;">جميع طلاب المجموعة حضروا بالكامل وتم تسجيلهم.</p>
        </div>
    @endif
</div>
