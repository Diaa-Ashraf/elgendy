<x-filament-panels::page>
    <style>
        .lp-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
            font-family: inherit;
        }

        /* ─── بطاقات الإحصائيات العلوية ─── */
        .lp-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .lp-stat-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 15px -2px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .dark .lp-stat-card {
            background: #111827;
            border-color: rgba(55, 65, 81, 0.6);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .lp-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -4px rgba(0, 0, 0, 0.08);
        }

        .lp-stat-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .lp-stat-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
        }
        .dark .lp-stat-label {
            color: #9ca3af;
        }
        .lp-stat-val {
            font-size: 1.45rem;
            font-weight: 900;
            font-family: 'Alexandria', monospace, sans-serif;
            line-height: 1.2;
        }
        .lp-stat-sub {
            font-size: 0.72rem;
            font-weight: 600;
            color: #94a3b8;
        }

        .lp-stat-icon-box {
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* ألوان الكروت الإحصائية */
        .lp-stat-danger .lp-stat-val { color: #dc2626; }
        .dark .lp-stat-danger .lp-stat-val { color: #f87171; }
        .lp-stat-danger .lp-stat-icon-box {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .lp-stat-warning .lp-stat-val { color: #d97706; }
        .dark .lp-stat-warning .lp-stat-val { color: #fbbf24; }
        .lp-stat-warning .lp-stat-icon-box {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .lp-stat-success .lp-stat-val { color: #059669; }
        .dark .lp-stat-success .lp-stat-val { color: #34d399; }
        .lp-stat-success .lp-stat-icon-box {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .lp-stat-info-card .lp-stat-val { color: #2563eb; }
        .dark .lp-stat-info-card .lp-stat-val { color: #60a5fa; }
        .lp-stat-info-card .lp-stat-icon-box {
            background: rgba(37, 99, 235, 0.12);
            color: #2563eb;
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        /* ─── صندوق الفلاتر والتحكم ─── */
        .lp-filter-card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
            backdrop-filter: blur(12px);
        }
        .dark .lp-filter-card {
            background: rgba(17, 24, 39, 0.9);
            border-color: rgba(55, 65, 81, 0.7);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .lp-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: flex-end;
        }

        .lp-input-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .lp-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #374151;
        }
        .dark .lp-label {
            color: #d1d5db;
        }

        .lp-control {
            width: 100%;
            border-radius: 0.85rem;
            padding: 0.6rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 600;
            background-color: #f8fafc;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            outline: none;
            transition: all 0.2s ease;
        }
        .dark .lp-control {
            background-color: #1e293b;
            color: #f8fafc;
            border-color: #334155;
        }
        .lp-control:focus {
            border-color: #f43f5e;
            box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.18);
        }

        /* ─── شريط العمليات الجماعية ─── */
        .lp-actions-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 0.75rem 1.25rem;
            border-radius: 1rem;
            background: rgba(241, 245, 249, 0.7);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .dark .lp-actions-bar {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(51, 65, 85, 0.8);
        }

        .lp-btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.6rem 1.1rem;
            border-radius: 0.85rem;
            font-size: 0.78rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }
        .lp-btn-whatsapp-bulk {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }
        .lp-btn-whatsapp-bulk:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
        }

        .lp-btn-print {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }
        .dark .lp-btn-print {
            background: #1e293b;
            color: #f1f5f9;
            border-color: #475569;
        }
        .lp-btn-print:hover {
            background: #f8fafc;
        }

        /* ─── جدول وبطاقات المتأخرين ─── */
        .lp-table-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .lp-table-card {
            background: #111827;
            border-color: rgba(55, 65, 81, 0.6);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .lp-table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .lp-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.82rem;
        }

        .lp-table th {
            padding: 0.95rem 1.1rem;
            background: #f8fafc;
            color: #475569;
            font-weight: 800;
            font-size: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .lp-table th {
            background: #182234;
            color: #94a3b8;
            border-bottom-color: #334155;
        }

        .lp-table td {
            padding: 1rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            font-weight: 600;
            vertical-align: middle;
        }
        .dark .lp-table td {
            border-bottom-color: #1e293b;
            color: #e2e8f0;
        }

        .lp-table tr:hover td {
            background: rgba(248, 250, 252, 0.9);
        }
        .dark .lp-table tr:hover td {
            background: rgba(30, 41, 59, 0.4);
        }

        .lp-student-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .lp-student-avatar {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.75rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 900;
            flex-shrink: 0;
        }
        .dark .lp-student-avatar {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }

        .lp-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 0.6rem;
            font-size: 0.72rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .lp-badge-stage {
            background: rgba(13, 59, 76, 0.08);
            border: 1px solid rgba(13, 59, 76, 0.18);
            color: #0D3B4C;
        }
        .dark .lp-badge-stage {
            background: rgba(56, 189, 248, 0.1);
            border-color: rgba(56, 189, 248, 0.25);
            color: #7dd3fc;
        }

        .lp-badge-group {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: #d97706;
        }
        .dark .lp-badge-group {
            background: rgba(245, 158, 11, 0.15);
            border-color: rgba(245, 158, 11, 0.3);
            color: #fbbf24;
        }

        .lp-badge-unpaid {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }
        .dark .lp-badge-unpaid {
            background: rgba(239, 68, 68, 0.18);
            border-color: rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }

        .lp-badge-partial {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: #d97706;
        }
        .dark .lp-badge-partial {
            background: rgba(245, 158, 11, 0.18);
            border-color: rgba(245, 158, 11, 0.35);
            color: #fde047;
        }

        .lp-due-val {
            font-size: 1.05rem;
            font-weight: 900;
            font-family: monospace;
            color: #dc2626;
        }
        .dark .lp-due-val {
            color: #f87171;
        }

        .lp-actions-group {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.4rem;
        }

        .lp-btn-wa-direct {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.8rem;
            border-radius: 0.65rem;
            font-size: 0.72rem;
            font-weight: 800;
            background: #10b981;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
        }
        .lp-btn-wa-direct:hover {
            background: #059669;
            transform: scale(1.02);
        }

        .lp-btn-pay-direct {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.8rem;
            border-radius: 0.65rem;
            font-size: 0.72rem;
            font-weight: 800;
            background: rgba(37, 99, 235, 0.1);
            border: 1px solid rgba(37, 99, 235, 0.25);
            color: #2563eb;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .lp-btn-pay-direct:hover {
            background: rgba(37, 99, 235, 0.2);
            color: #1d4ed8;
        }
        .dark .lp-btn-pay-direct {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.35);
            color: #93c5fd;
        }

        .lp-btn-portal-notify {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            border-radius: 0.65rem;
            font-size: 0.72rem;
            font-weight: 800;
            background: rgba(244, 63, 94, 0.1);
            border: 1px solid rgba(244, 63, 94, 0.25);
            color: #e11d48;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .lp-btn-portal-notify:hover {
            background: rgba(244, 63, 94, 0.2);
        }
        .dark .lp-btn-portal-notify {
            background: rgba(244, 63, 94, 0.15);
            border-color: rgba(244, 63, 94, 0.35);
            color: #fda4af;
        }

        .lp-empty-state {
            padding: 4rem 1.5rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        .lp-empty-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
    </style>

    <div class="lp-container">

        {{-- ─── 1. بطاقات المؤشرات المالية لمتأخرات الشهر ─── --}}
        <div class="lp-stats-grid">
            <div class="lp-stat-card lp-stat-danger">
                <div class="lp-stat-info">
                    <span class="lp-stat-label">إجمالي الطلاب المتأخرين</span>
                    <span class="lp-stat-val">{{ number_format($stats['total_students']) }} <span style="font-size: 0.85rem; font-weight: 700;">طالب</span></span>
                    <span class="lp-stat-sub">من إجمالي المستحقين لشهر {{ $stats['month_label'] }}</span>
                </div>
                <div class="lp-stat-icon-box">
                    <x-heroicon-o-user-minus style="width: 1.65rem; height: 1.65rem;" />
                </div>
            </div>

            <div class="lp-stat-card lp-stat-warning">
                <div class="lp-stat-info">
                    <span class="lp-stat-label">المبالغ المتأخرة المستحقة</span>
                    <span class="lp-stat-val">{{ number_format($stats['total_due'], 2) }} <span style="font-size: 0.85rem; font-weight: 700;">ج.م</span></span>
                    <span class="lp-stat-sub">رسوم اشتراكات لم تُسدد بعد</span>
                </div>
                <div class="lp-stat-icon-box">
                    <x-heroicon-o-banknotes style="width: 1.65rem; height: 1.65rem;" />
                </div>
            </div>

            <div class="lp-stat-card lp-stat-success">
                <div class="lp-stat-info">
                    <span class="lp-stat-label">المبالغ المسددة للشهر</span>
                    <span class="lp-stat-val">{{ number_format($stats['total_collected'], 2) }} <span style="font-size: 0.85rem; font-weight: 700;">ج.م</span></span>
                    <span class="lp-stat-sub">تم تحصيلها واعتمادها بالخزينة</span>
                </div>
                <div class="lp-stat-icon-box">
                    <x-heroicon-o-check-circle style="width: 1.65rem; height: 1.65rem;" />
                </div>
            </div>

            <div class="lp-stat-card lp-stat-info-card">
                <div class="lp-stat-info">
                    <span class="lp-stat-label">نسبة التحصيل والسداد</span>
                    <span class="lp-stat-val">{{ $stats['collection_rate'] }}%</span>
                    <span class="lp-stat-sub">معدل التزام سداد الاشتراكات</span>
                </div>
                <div class="lp-stat-icon-box">
                    <x-heroicon-o-chart-pie style="width: 1.65rem; height: 1.65rem;" />
                </div>
            </div>
        </div>

        {{-- ─── 2. صندوق الفلاتر الذكية ─── --}}
        <div class="lp-filter-card">
            <div class="lp-filter-grid">
                {{-- الشهر المستهدف --}}
                <div class="lp-input-group">
                    <label class="lp-label">شهر الاشتراك</label>
                    <select wire:model.live="selectedMonth" class="lp-control">
                        @foreach($months as $mKey => $mLabel)
                            <option value="{{ $mKey }}">{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- المرحلة الدراسية --}}
                <div class="lp-input-group">
                    <label class="lp-label">المرحلة الدراسية</label>
                    <select wire:model.live="selectedStage" class="lp-control">
                        <option value="">جميع المراحل الدراسية</option>
                        @foreach($stages as $stg)
                            <option value="{{ $stg->id }}">{{ $stg->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- المجموعة الدراسية --}}
                <div class="lp-input-group">
                    <label class="lp-label">المجموعة الدراسية</label>
                    <select wire:model.live="selectedGroup" class="lp-control">
                        <option value="">جميع المجموعات الدراسية</option>
                        @foreach($groups as $grp)
                            <option value="{{ $grp->id }}">{{ $grp->name }} ({{ $grp->price_per_month }} ج.م)</option>
                        @endforeach
                    </select>
                </div>

                {{-- حالة السداد --}}
                <div class="lp-input-group">
                    <label class="lp-label">حالة السداد</label>
                    <select wire:model.live="statusFilter" class="lp-control">
                        <option value="all">الكل (غير مسدد + سداد جزئي)</option>
                        <option value="unpaid">لم يسدد نهائياً (0%)</option>
                        <option value="partial">سداد جزئي</option>
                    </select>
                </div>

                {{-- البحث الفوري --}}
                <div class="lp-input-group">
                    <label class="lp-label">بحث سريع (اسم / كود / هاتف)</label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="ابحث باسم الطالب أو الهاتف..." class="lp-control">
                </div>
            </div>
        </div>

        {{-- ─── 3. شريط العمليات والأدوات ─── --}}
        <div class="lp-actions-bar">
            <div style="font-size: 0.85rem; font-weight: 800; color: #334155;" class="dark:text-slate-200">
                <span>نتائج المتأخرين المعروضة: </span>
                <span class="lp-badge lp-badge-unpaid" style="font-size: 0.8rem; font-family: monospace;">{{ $records->count() }} متأخر</span>
            </div>

            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <button type="button" wire:click="sendBulkWhatsApp" wire:loading.attr="disabled" class="lp-btn-action lp-btn-whatsapp-bulk">
                    <x-heroicon-s-chat-bubble-left-right style="width: 1.1rem; height: 1.1rem;" />
                    <span>تذكير واتساب جماعي للكل</span>
                </button>

                <button type="button" onclick="window.print()" class="lp-btn-action lp-btn-print">
                    <x-heroicon-o-printer style="width: 1.1rem; height: 1.1rem;" />
                    <span>طباعة كشف المتأخرات</span>
                </button>
            </div>
        </div>

        {{-- ─── 4. جدول كشف المتأخرين التفصيلي ─── --}}
        <div class="lp-table-card">
            <div class="lp-table-responsive">
                <table class="lp-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">#</th>
                            <th>بيانات الطالب</th>
                            <th>المرحلة الدراسية</th>
                            <th>المجموعة والمادة</th>
                            <th style="text-align: center;">اشتراك الشهر</th>
                            <th style="text-align: center;">المسدد</th>
                            <th style="text-align: center;">المبلغ المتأخر</th>
                            <th style="text-align: center;">الحالة</th>
                            <th>هاتف ولي الأمر</th>
                            <th style="text-align: left;">إجراءات المتابعة والسداد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $index => $rec)
                            @php
                                $parentPhone = $rec['parent_phone'] ?: $rec['student_phone'];
                                $monthLabel = $stats['month_label'];
                                $waMsg = "السلام عليكم ورحمة الله 💰\n\n";
                                $waMsg .= "المكرم ولي أمر الطالب/ة: *{$rec['student_name']}*\n";
                                $waMsg .= "نود تذكيركم بلطف بموعد سداد اشتراك شهر ({$monthLabel}) لمجموعة *{$rec['group_name']}*.\n";
                                $waMsg .= "المبلغ المستحق: *{$rec['due_amount']} ج.م*\n\n";
                                $waMsg .= "شاكرين حسن تعاونكم معنا — سنتر الأستاذ محمد الغندي.";

                                $waUrl = $parentPhone ? \App\Services\WhatsAppNotificationService::getWhatsAppUrl($parentPhone, $waMsg) : '#';
                                $recordPaymentUrl = url('/admin/student-payments/create') . '?student_id=' . $rec['student_id'] . ($rec['group_id'] ? '&group_id=' . $rec['group_id'] : '');
                            @endphp
                            <tr>
                                <td style="text-align: center; font-weight: 800; color: #94a3b8; font-family: monospace;">
                                    {{ $index + 1 }}
                                </td>

                                {{-- اسم الطالب والكود --}}
                                <td>
                                    <div class="lp-student-row">
                                        <div class="lp-student-avatar">
                                            {{ mb_substr($rec['student_name'], 0, 1) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 900; font-size: 0.9rem; color: #0f172a;" class="dark:text-white">
                                                {{ $rec['student_name'] }}
                                            </div>
                                            <div style="font-size: 0.72rem; color: #64748b; font-family: monospace; font-weight: 700;">
                                                كود: {{ $rec['student_code'] }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- المرحلة الدراسية --}}
                                <td>
                                    <span class="lp-badge lp-badge-stage">
                                        {{ $rec['stage_name'] }}
                                    </span>
                                </td>

                                {{-- المجموعة والمادة --}}
                                <td>
                                    <div>
                                        <span class="lp-badge lp-badge-group">
                                            {{ $rec['group_name'] }}
                                        </span>
                                        <div style="font-size: 0.72rem; color: #64748b; margin-top: 0.2rem; font-weight: 600;">
                                            المادة: {{ $rec['subject_name'] }}
                                        </div>
                                    </div>
                                </td>

                                {{-- سعر الاشتراك الشهري --}}
                                <td style="text-align: center; font-family: monospace; font-weight: 800; font-size: 0.88rem;">
                                    {{ number_format($rec['required_amount'], 2) }} ج.م
                                </td>

                                {{-- المسدد --}}
                                <td style="text-align: center; font-family: monospace; font-weight: 800; font-size: 0.88rem; color: #059669;">
                                    {{ number_format($rec['paid_amount'], 2) }} ج.م
                                </td>

                                {{-- المبلغ المتأخر المستحق --}}
                                <td style="text-align: center;">
                                    <span class="lp-due-val">
                                        {{ number_format($rec['due_amount'], 2) }} ج.م
                                    </span>
                                </td>

                                {{-- شارة الحالة --}}
                                <td style="text-align: center;">
                                    @if($rec['status'] === 'partial')
                                        <span class="lp-badge lp-badge-partial">
                                            سداد جزئي ⚠️
                                        </span>
                                    @else
                                        <span class="lp-badge lp-badge-unpaid">
                                            غير مسدد ❌
                                        </span>
                                    @endif
                                </td>

                                {{-- رقم الهاتف --}}
                                <td>
                                    @if($parentPhone)
                                        <div style="font-family: monospace; font-weight: 700; font-size: 0.82rem; direction: ltr; text-align: right;">
                                            {{ $parentPhone }}
                                        </div>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.75rem;">غير مسجل</span>
                                    @endif
                                </td>

                                {{-- الإجراءات --}}
                                <td style="text-align: left;">
                                    <div class="lp-actions-group">
                                        {{-- زر تذكير واتساب --}}
                                        @if($parentPhone)
                                            <a href="{{ $waUrl }}" target="_blank" class="lp-btn-wa-direct" title="إرسال تذكير سداد واتساب لولي الأمر">
                                                <span>💬</span>
                                                <span>واتساب</span>
                                            </a>
                                        @endif

                                        {{-- زر تسجيل سداد سريع --}}
                                        <a href="{{ $recordPaymentUrl }}" class="lp-btn-pay-direct" title="تسجيل دفعة سداد جديدة للطالب">
                                            <span>💳</span>
                                            <span>سداد</span>
                                        </a>

                                        {{-- زر إشعار بوابة ولي الأمر --}}
                                        <button type="button" wire:click="notifyParentPortal({{ $rec['student_id'] }}, '{{ addslashes($rec['group_name']) }}', {{ $rec['due_amount'] }})" class="lp-btn-portal-notify" title="إرسال إشعار تذكير لبوابة ولي الأمر">
                                            <span>📲</span>
                                            <span>إشعار</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="lp-empty-state">
                                        <div class="lp-empty-icon">🎉</div>
                                        <h3 style="font-weight: 900; font-size: 1.1rem; color: #059669;">
                                            لا توجد أي متأخرات في الاشتراكات للخيارات المحددة!
                                        </h3>
                                        <p style="font-size: 0.8rem; color: #64748b; font-weight: 600;">
                                            جميع الطلاب قاموا بسداد المستحقات المطلوبة بالكامل لشهر {{ $stats['month_label'] }} 👍
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>
