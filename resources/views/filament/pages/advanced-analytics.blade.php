<x-filament-panels::page>
    @php
        $analytics = $this->getAnalyticsData();
        $metrics = $analytics['academicMetrics'];
    @endphp

    <style>
        .aa-container {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
            width: 100%;
            font-family: inherit;
        }

        /* ─── 1. شريط العنوان والتحديث اللحظي ─── */
        .aa-header-card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .dark .aa-header-card {
            background: rgba(17, 24, 39, 0.9);
            border-color: rgba(55, 65, 81, 0.8);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.35);
        }

        .aa-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .aa-header-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.85rem;
            background: rgba(245, 158, 11, 0.15);
            border: 1.5px solid rgba(245, 158, 11, 0.35);
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .dark .aa-header-icon {
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.5);
            background: rgba(245, 158, 11, 0.2);
        }

        .aa-header-h3 {
            font-size: 1.15rem;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
        }
        .dark .aa-header-h3 {
            color: #f8fafc;
        }
        .aa-header-p {
            font-size: 0.78rem;
            color: #475569;
            margin: 0.2rem 0 0 0;
            font-weight: 700;
        }
        .dark .aa-header-p {
            color: #cbd5e1;
        }

        .aa-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1.1rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 800;
            background: rgba(16, 185, 129, 0.15);
            border: 1.5px solid rgba(16, 185, 129, 0.35);
            color: #047857;
        }
        .dark .aa-header-badge {
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.5);
            background: rgba(16, 185, 129, 0.2);
        }
        @media (max-width: 768px) {
            .aa-header-card {
                flex-direction: column;
                align-items: stretch;
                text-align: right;
            }
            .aa-header-title {
                flex-direction: column;
                align-items: flex-start;
            }
            .aa-header-badge {
                width: 100%;
                justify-content: center;
            }
        }

        /* ─── 2. كروت المؤشرات الكبرى (KPIs) ─── */
        .aa-kpi-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.85rem;
        }
        @media (min-width: 640px) {
            .aa-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }
        @media (min-width: 1200px) {
            .aa-kpi-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 1.25rem;
            }
        }

        .aa-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .dark .aa-kpi-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
        }
        .aa-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .aa-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.4rem;
            gap: 0.4rem;
        }
        .aa-kpi-label {
            font-size: 0.8rem;
            font-weight: 800;
            color: #334155;
            white-space: nowrap;
        }
        .dark .aa-kpi-label {
            color: #cbd5e1;
        }

        .aa-kpi-icon-box {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .aa-kpi-val {
            font-size: clamp(1.3rem, 2vw, 1.95rem);
            font-weight: 900;
            font-family: monospace;
            margin: 0.25rem 0;
            line-height: 1.15;
            word-break: break-word;
        }

        .aa-kpi-sub {
            font-size: 0.72rem;
            font-weight: 800;
            padding: 0.2rem 0.6rem;
            border-radius: 0.5rem;
            display: inline-block;
            margin-top: 0.35rem;
        }

        /* تخصيص الألوان للكروت الأربعة */
        .aa-card-blue .aa-kpi-icon-box { background: rgba(59, 130, 246, 0.15); color: #2563eb; }
        .aa-card-blue .aa-kpi-val { color: #2563eb; }
        .dark .aa-card-blue .aa-kpi-val { color: #60a5fa; }
        .aa-card-blue .aa-kpi-sub { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
        .dark .aa-card-blue .aa-kpi-sub { color: #93c5fd; background: rgba(59, 130, 246, 0.2); }

        .aa-card-teal .aa-kpi-icon-box { background: rgba(20, 184, 166, 0.15); color: #0d9488; }
        .aa-card-teal .aa-kpi-val { color: #0d9488; }
        .dark .aa-card-teal .aa-kpi-val { color: #2dd4bf; }
        .aa-card-teal .aa-kpi-sub { background: rgba(20, 184, 166, 0.12); color: #0f766e; }
        .dark .aa-card-teal .aa-kpi-sub { color: #5eead4; background: rgba(20, 184, 166, 0.2); }

        .aa-card-amber .aa-kpi-icon-box { background: rgba(245, 158, 11, 0.15); color: #d97706; }
        .aa-card-amber .aa-kpi-val { color: #d97706; }
        .dark .aa-card-amber .aa-kpi-val { color: #fbbf24; }
        .aa-card-amber .aa-kpi-sub { background: rgba(245, 158, 11, 0.12); color: #b45309; }
        .dark .aa-card-amber .aa-kpi-sub { color: #fde68a; background: rgba(245, 158, 11, 0.2); }

        .aa-card-purple .aa-kpi-icon-box { background: rgba(168, 85, 247, 0.15); color: #9333ea; }
        .aa-card-purple .aa-kpi-val { color: #9333ea; }
        .dark .aa-card-purple .aa-kpi-val { color: #c084fc; }
        .aa-card-purple .aa-kpi-sub { background: rgba(168, 85, 247, 0.12); color: #7e22ce; }
        .dark .aa-card-purple .aa-kpi-sub { color: #e9d5ff; background: rgba(168, 85, 247, 0.2); }

        /* ─── 3. كرت الجداول الرئيسية ─── */
        .aa-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .aa-table-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .aa-table-header {
            padding: 1.1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .dark .aa-table-header {
            background: #1f2937;
            border-bottom-color: #374151;
        }

        .aa-table-title {
            font-size: 0.98rem;
            font-weight: 900;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .dark .aa-table-title {
            color: #f8fafc;
        }

        .aa-table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .aa-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.84rem;
        }

        .aa-table th {
            padding: 0.9rem 1.1rem;
            background: #f1f5f9;
            color: #334155;
            font-weight: 800;
            font-size: 0.76rem;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .aa-table th {
            background: #182234;
            color: #cbd5e1;
            border-bottom-color: #374151;
        }

        .aa-table td {
            padding: 0.9rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            white-space: nowrap;
        }
        .dark .aa-table td {
            border-bottom-color: #1f2937;
            color: #e2e8f0;
        }

        .aa-table tr:hover td {
            background: rgba(241, 245, 249, 0.7);
        }
        .dark .aa-table tr:hover td {
            background: rgba(31, 41, 55, 0.6);
        }

        /* الشارات الملونة للجدول */
        .aa-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.3rem 0.85rem;
            border-radius: 0.65rem;
            font-size: 0.76rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .aa-status-profit {
            background: rgba(16, 185, 129, 0.15);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.35);
        }
        .dark .aa-status-profit {
            color: #34d399;
            background: rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.4);
        }

        .aa-status-loss {
            background: rgba(239, 68, 68, 0.15);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.35);
        }
        .dark .aa-status-loss {
            color: #f87171;
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.4);
        }

        .aa-status-neutral {
            background: rgba(148, 163, 184, 0.15);
            color: #334155;
            border: 1px solid rgba(148, 163, 184, 0.35);
        }
        .dark .aa-status-neutral {
            color: #cbd5e1;
            background: rgba(148, 163, 184, 0.2);
            border-color: rgba(148, 163, 184, 0.4);
        }

        /* ─── 4. قسم توزيع المراحل والمجموعات ─── */
        .aa-two-col-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 1400px) {
            .aa-two-col-grid {
                grid-template-columns: 1fr 1.8fr;
            }
        }

        .aa-stage-card-wrapper {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.25rem;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .dark .aa-stage-card-wrapper {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .aa-stage-header {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding-bottom: 0.9rem;
            margin-bottom: 1rem;
            border-bottom: 1.5px solid #e2e8f0;
        }
        .dark .aa-stage-header {
            border-bottom-color: #374151;
        }

        .aa-stage-h3 {
            font-weight: 900;
            font-size: 0.98rem;
            margin: 0;
            color: #0f172a;
        }
        .dark .aa-stage-h3 {
            color: #f8fafc;
        }

        .aa-stage-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .aa-stage-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.95rem 1.15rem;
            border-radius: 1rem;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        .dark .aa-stage-item {
            background: #1e293b;
            border-color: #334155;
        }
        .aa-stage-item:hover {
            border-color: #10b981;
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.12);
        }

        .aa-stage-name {
            font-weight: 900;
            font-size: 0.92rem;
            color: #0f172a;
            display: block;
            margin-bottom: 0.25rem;
        }
        .dark .aa-stage-name {
            color: #ffffff;
        }

        .aa-stage-groups-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.72rem;
            font-weight: 800;
            color: #2563eb;
            background: rgba(37, 99, 235, 0.1);
            padding: 0.15rem 0.55rem;
            border-radius: 0.5rem;
        }
        .dark .aa-stage-groups-badge {
            color: #93c5fd;
            background: rgba(37, 99, 235, 0.2);
        }

        .aa-stage-count-box {
            text-align: left;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .aa-stage-count {
            font-size: 1.5rem;
            font-weight: 900;
            font-family: monospace;
            color: #059669;
            line-height: 1;
            margin-bottom: 0.2rem;
        }
        .dark .aa-stage-count {
            color: #34d399;
        }

        .aa-stage-count-lbl {
            font-size: 0.7rem;
            color: #475569;
            font-weight: 800;
        }
        .dark .aa-stage-count-lbl {
            color: #cbd5e1;
        }

        .aa-tag-pill {
            display: inline-block;
            padding: 0.25rem 0.7rem;
            border-radius: 0.55rem;
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
            font-size: 0.75rem;
            font-weight: 800;
        }
        .dark .aa-tag-pill {
            color: #93c5fd;
            background: rgba(37, 99, 235, 0.2);
        }
    </style>

    <div class="aa-container">
        {{-- ─── 1. شريط العنوان والتحكم الذكي ─── --}}
        <div class="aa-header-card">
            <div class="aa-header-title">
                <div class="aa-header-icon">
                    <x-heroicon-o-presentation-chart-line style="width: 1.6rem; height: 1.6rem;" />
                </div>
                <div>
                    <h3 class="aa-header-h3">لوحة التحليلات المتقدمة والمؤشرات الاستراتيجية</h3>
                    <p class="aa-header-p">رصد شامل ومباشر للمؤشرات المالية، جودة التحصيل الأكاديمي، ونمو المجموعات</p>
                </div>
            </div>

            <div class="aa-header-badge">
                <x-heroicon-o-bolt style="width: 1.15rem; height: 1.15rem;" />
                <span>تحديث لحظي ومحمي بنظام الكاش الذكي</span>
            </div>
        </div>

        {{-- ─── 2. كروت المؤشرات الكبرى (KPIs) ─── --}}
        <div class="aa-kpi-grid">
            {{-- إجمالي الطلاب --}}
            <div class="aa-kpi-card aa-card-blue">
                <div>
                    <div class="aa-kpi-top">
                        <span class="aa-kpi-label">إجمالي الطلاب المقيدين</span>
                        <div class="aa-kpi-icon-box">
                            <x-heroicon-o-user-group style="width: 1.3rem; height: 1.3rem;" />
                        </div>
                    </div>
                    <div class="aa-kpi-val">{{ number_format($metrics['total_students']) }}</div>
                </div>
                <div>
                    <span class="aa-kpi-sub">طالب مقيد بالمنظومة</span>
                </div>
            </div>

            {{-- معدل الحضور العام --}}
            <div class="aa-kpi-card aa-card-teal">
                <div>
                    <div class="aa-kpi-top">
                        <span class="aa-kpi-label">معدل الحضور والمواظبة</span>
                        <div class="aa-kpi-icon-box">
                            <x-heroicon-o-check-badge style="width: 1.3rem; height: 1.3rem;" />
                        </div>
                    </div>
                    <div class="aa-kpi-val">{{ $metrics['overall_attendance_rate'] }}%</div>
                </div>
                <div>
                    <span class="aa-kpi-sub">التزام ومواظبة الطلاب</span>
                </div>
            </div>

            {{-- متوسط درجات الطلاب --}}
            <div class="aa-kpi-card aa-card-amber">
                <div>
                    <div class="aa-kpi-top">
                        <span class="aa-kpi-label">متوسط درجات الطلاب</span>
                        <div class="aa-kpi-icon-box">
                            <x-heroicon-o-academic-cap style="width: 1.3rem; height: 1.3rem;" />
                        </div>
                    </div>
                    <div class="aa-kpi-val">{{ $metrics['avg_exam_score'] }}%</div>
                </div>
                <div>
                    <span class="aa-kpi-sub">مستوى التحصيل الأكاديمي</span>
                </div>
            </div>

            {{-- إجمالي الاختبارات --}}
            <div class="aa-kpi-card aa-card-purple">
                <div>
                    <div class="aa-kpi-top">
                        <span class="aa-kpi-label">إجمالي الامتحانات والكويزات</span>
                        <div class="aa-kpi-icon-box">
                            <x-heroicon-o-clipboard-document-list style="width: 1.3rem; height: 1.3rem;" />
                        </div>
                    </div>
                    <div class="aa-kpi-val">{{ number_format($metrics['total_exams']) }}</div>
                </div>
                <div>
                    <span class="aa-kpi-sub">امتحانات ورقية وإلكترونية</span>
                </div>
            </div>
        </div>

        {{-- ─── 3. جدول اتجاه الأرباح والإيرادات الشهرية ─── --}}
        <div class="aa-table-card">
            <div class="aa-table-header">
                <div class="aa-table-title">
                    <x-heroicon-o-chart-bar-square style="width: 1.35rem; height: 1.35rem; color: #f59e0b;" />
                    <span>تحليل حركة الأرباح والإيرادات والمصروفات الشهرية (آخر 6 أشهر)</span>
                </div>
                <div class="aa-status-pill aa-status-profit" style="font-size: 0.75rem;">
                    سجل مالي معتمد
                </div>
            </div>

            <div class="aa-table-responsive">
                <table class="aa-table">
                    <thead>
                        <tr>
                            <th>الشهر والبيان</th>
                            <th style="color: #059669;" class="dark:text-emerald-400">الإيرادات المحصلة</th>
                            <th style="color: #dc2626;" class="dark:text-rose-400">المصروفات والرواتب</th>
                            <th style="color: #2563eb;" class="dark:text-blue-400">صافي الربح الفعلي</th>
                            <th style="text-align: center;">مؤشر الأداء المالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analytics['monthlyChart'] as $m)
                            <tr>
                                <td style="font-weight: 900; font-size: 0.9rem;">
                                    {{ $m['label'] }}
                                </td>
                                <td style="font-weight: 900; font-family: monospace; font-size: 0.98rem; color: #059669;" class="dark:text-emerald-400">
                                    {{ number_format($m['revenue'], 2) }} ج.م
                                </td>
                                <td style="font-weight: 900; font-family: monospace; font-size: 0.98rem; color: #dc2626;" class="dark:text-rose-400">
                                    {{ number_format($m['expenses'], 2) }} ج.م
                                </td>
                                <td style="font-weight: 900; font-family: monospace; font-size: 0.98rem; color: {{ $m['profit'] >= 0 ? '#2563eb' : '#dc2626' }};" class="dark:text-blue-400">
                                    {{ number_format($m['profit'], 2) }} ج.م
                                </td>
                                <td style="text-align: center;">
                                    @if($m['profit'] > 0)
                                        <span class="aa-status-pill aa-status-profit">
                                            فائض أرباح ممتاز 📈
                                        </span>
                                    @elseif($m['profit'] < 0)
                                        <span class="aa-status-pill aa-status-loss">
                                            عجز تشغيلي 📉
                                        </span>
                                    @else
                                        <span class="aa-status-pill aa-status-neutral">
                                            نقطة التعادل ⚖️
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── 4. توزيع الطلاب وتحليل كفاءة وربحية المجموعات ─── --}}
        <div class="aa-two-col-grid">
            {{-- كروت توزيع المراحل الدراسية بتصميم بارز وواضح جداً --}}
            <div class="aa-stage-card-wrapper">
                <div class="aa-stage-header">
                    <x-heroicon-o-academic-cap style="width: 1.35rem; height: 1.35rem; color: #10b981;" />
                    <h3 class="aa-stage-h3">
                        توزيع الطلاب حسب المراحل الدراسية
                    </h3>
                </div>

                <div class="aa-stage-list">
                    @forelse($analytics['stageDistribution'] as $stg)
                        <div class="aa-stage-item">
                            <div>
                                <span class="aa-stage-name">
                                    {{ $stg->name }}
                                </span>
                                <span class="aa-stage-groups-badge">
                                    <x-heroicon-o-user-group style="width: 0.85rem; height: 0.85rem;" />
                                    <span>{{ $stg->groups_count }} مجموعات</span>
                                </span>
                            </div>
                            <div class="aa-stage-count-box">
                                <span class="aa-stage-count">
                                    {{ $stg->count }}
                                </span>
                                <span class="aa-stage-count-lbl">طالب مقيد</span>
                            </div>
                        </div>
                    @empty
                        <p style="text-align: center; color: #94a3b8; padding: 1.5rem 0; font-size: 0.85rem; font-weight: 700;">لا توجد مراحل دراسية مسجلة</p>
                    @endforelse
                </div>
            </div>

            {{-- تحليل كفاءة وربحية المجموعات الدراسية --}}
            <div class="aa-table-card">
                <div class="aa-table-header">
                    <div class="aa-table-title">
                        <x-heroicon-o-presentation-chart-bar style="width: 1.35rem; height: 1.35rem; color: #2563eb;" />
                        <span>تحليل الطاقة الاستيعابية والربحية للمجموعات</span>
                    </div>
                </div>

                <div class="aa-table-responsive">
                    <table class="aa-table">
                        <thead>
                            <tr>
                                <th>اسم المجموعة</th>
                                <th>المرحلة الدراسية</th>
                                <th style="text-align: center;">الطلاب المقيدين</th>
                                <th style="text-align: center;">سعر الاشتراك</th>
                                <th style="text-align: left; color: #059669;" class="dark:text-emerald-400">الدخل المتوقع شهرياً</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analytics['groupsAnalytics'] as $grp)
                                <tr>
                                    <td style="font-weight: 900; font-size: 0.88rem;">
                                        {{ $grp['name'] }}
                                    </td>
                                    <td style="color: #64748b; font-size: 0.8rem;" class="dark:text-slate-300">
                                        {{ $grp['stage'] }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="aa-tag-pill">
                                            {{ $grp['students_count'] }} طالب
                                        </span>
                                    </td>
                                    <td style="text-align: center; font-family: monospace; font-weight: 900;">
                                        {{ number_format($grp['price']) }} ج.م
                                    </td>
                                    <td style="text-align: left; font-weight: 900; font-family: monospace; font-size: 0.98rem; color: #059669;" class="dark:text-emerald-400">
                                        {{ number_format($grp['expected_revenue']) }} ج.م
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2rem; color: #94a3b8; font-weight: 700;">لا توجد مجموعات دراسية نشطة حالياً</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
