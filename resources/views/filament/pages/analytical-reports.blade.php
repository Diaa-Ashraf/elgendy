<x-filament-panels::page>
    @php
        $data = $this->getAnalyticsData();
    @endphp

    <style>
        .rp-container {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
            width: 100%;
            font-family: inherit;
        }

        /* ─── 1. شريط الأدوات والفلاتر ─── */
        .rp-filter-card {
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
        .dark .rp-filter-card {
            background: rgba(17, 24, 39, 0.9);
            border-color: rgba(55, 65, 81, 0.8);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.35);
        }

        .rp-btn-print {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            border-radius: 0.85rem;
            font-size: 0.82rem;
            font-weight: 900;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
        }
        .rp-btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
        }

        .rp-filters-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .rp-select {
            border-radius: 0.85rem;
            padding: 0.6rem 1rem;
            font-size: 0.82rem;
            font-weight: 800;
            background-color: #f8fafc;
            color: #0f172a;
            border: 1.5px solid #d1d5db;
            outline: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dark .rp-select {
            background-color: #1e293b;
            color: #f8fafc;
            border-color: #475569;
        }
        .rp-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }

        .rp-date-box {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background-color: #f8fafc;
            border: 1.5px solid #d1d5db;
            border-radius: 0.85rem;
            padding: 0.45rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 800;
            color: #334155;
        }
        .dark .rp-date-box {
            background-color: #1e293b;
            border-color: #475569;
            color: #e2e8f0;
        }

        .rp-date-input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.8rem;
            font-weight: 800;
            font-family: inherit;
            color: #0f172a;
            cursor: pointer;
        }
        .dark .rp-date-input {
            color: #f8fafc;
            color-scheme: dark;
        }

        /* ─── 2. كروت الإحصائيات الـ 6 ─── */
        .rp-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 1.15rem;
        }
        @media (min-width: 1280px) {
            .rp-kpi-grid {
                grid-template-columns: repeat(6, 1fr);
            }
        }

        .rp-kpi-card {
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
            text-align: center;
        }
        .dark .rp-kpi-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
        }
        .rp-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .rp-kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .rp-kpi-title {
            font-size: 0.78rem;
            font-weight: 800;
            color: #475569;
        }
        .dark .rp-kpi-title {
            color: #cbd5e1;
        }

        .rp-kpi-icon {
            width: 2.1rem;
            height: 2.1rem;
            border-radius: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rp-kpi-val {
            font-size: 1.55rem;
            font-weight: 900;
            font-family: monospace;
            margin: 0.2rem 0;
            line-height: 1.1;
        }

        .rp-kpi-sub {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.2rem 0.55rem;
            border-radius: 0.5rem;
            display: inline-block;
            margin-top: 0.35rem;
        }

        /* ألوان الكروت الـ 6 */
        .rp-card-rev .rp-kpi-icon { background: rgba(16, 185, 129, 0.15); color: #059669; }
        .rp-card-rev .rp-kpi-val { color: #059669; }
        .dark .rp-card-rev .rp-kpi-val { color: #34d399; }
        .rp-card-rev .rp-kpi-sub { background: rgba(16, 185, 129, 0.12); color: #047857; }
        .dark .rp-card-rev .rp-kpi-sub { color: #6ee7b7; background: rgba(16, 185, 129, 0.2); }

        .rp-card-exp .rp-kpi-icon { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
        .rp-card-exp .rp-kpi-val { color: #dc2626; }
        .dark .rp-card-exp .rp-kpi-val { color: #f87171; }
        .rp-card-exp .rp-kpi-sub { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }
        .dark .rp-card-exp .rp-kpi-sub { color: #fca5a5; background: rgba(239, 68, 68, 0.2); }

        .rp-card-profit .rp-kpi-icon { background: rgba(99, 102, 241, 0.15); color: #4f46e5; }
        .rp-card-profit .rp-kpi-val { color: #4f46e5; }
        .dark .rp-card-profit .rp-kpi-val { color: #818cf8; }
        .rp-card-profit .rp-kpi-sub { background: rgba(99, 102, 241, 0.12); color: #4338ca; }
        .dark .rp-card-profit .rp-kpi-sub { color: #c7d2fe; background: rgba(99, 102, 241, 0.2); }

        .rp-card-students .rp-kpi-icon { background: rgba(37, 99, 235, 0.15); color: #2563eb; }
        .rp-card-students .rp-kpi-val { color: #2563eb; }
        .dark .rp-card-students .rp-kpi-val { color: #60a5fa; }
        .rp-card-students .rp-kpi-sub { background: rgba(37, 99, 235, 0.12); color: #1d4ed8; }
        .dark .rp-card-students .rp-kpi-sub { color: #93c5fd; background: rgba(37, 99, 235, 0.2); }

        .rp-card-att-count .rp-kpi-icon { background: rgba(20, 184, 166, 0.15); color: #0d9488; }
        .rp-card-att-count .rp-kpi-val { color: #0d9488; }
        .dark .rp-card-att-count .rp-kpi-val { color: #2dd4bf; }
        .rp-card-att-count .rp-kpi-sub { background: rgba(20, 184, 166, 0.12); color: #0f766e; }
        .dark .rp-card-att-count .rp-kpi-sub { color: #5eead4; background: rgba(20, 184, 166, 0.2); }

        .rp-card-att-rate .rp-kpi-icon { background: rgba(245, 158, 11, 0.15); color: #d97706; }
        .rp-card-att-rate .rp-kpi-val { color: #d97706; }
        .dark .rp-card-att-rate .rp-kpi-val { color: #fbbf24; }
        .rp-card-att-rate .rp-kpi-sub { background: rgba(245, 158, 11, 0.12); color: #b45309; }
        .dark .rp-card-att-rate .rp-kpi-sub { color: #fde68a; background: rgba(245, 158, 11, 0.2); }

        /* ─── 3. كرت الجداول الرئيسية ─── */
        .rp-two-col-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 1024px) {
            .rp-two-col-grid {
                grid-template-columns: 2fr 1fr;
            }
        }

        .rp-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .rp-table-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .rp-table-header {
            padding: 1.1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .dark .rp-table-header {
            background: #1f2937;
            border-bottom-color: #374151;
        }

        .rp-table-title {
            font-size: 0.98rem;
            font-weight: 900;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .dark .rp-table-title {
            color: #f8fafc;
        }

        .rp-table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .rp-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.84rem;
        }

        .rp-table th {
            padding: 0.9rem 1.1rem;
            background: #f1f5f9;
            color: #334155;
            font-weight: 800;
            font-size: 0.76rem;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .rp-table th {
            background: #182234;
            color: #cbd5e1;
            border-bottom-color: #374151;
        }

        .rp-table td {
            padding: 0.9rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            white-space: nowrap;
        }
        .dark .rp-table td {
            border-bottom-color: #1f2937;
            color: #e2e8f0;
        }

        .rp-table tr:hover td {
            background: rgba(241, 245, 249, 0.7);
        }
        .dark .rp-table tr:hover td {
            background: rgba(31, 41, 55, 0.6);
        }

        .rp-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.8rem;
            border-radius: 0.65rem;
            font-size: 0.74rem;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .rp-link-blue {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
            border: 1px solid rgba(37, 99, 235, 0.3);
        }
        .rp-link-blue:hover { background: rgba(37, 99, 235, 0.22); }
        .dark .rp-link-blue { color: #93c5fd; border-color: rgba(37, 99, 235, 0.4); }

        .rp-link-rose {
            background: rgba(225, 29, 72, 0.12);
            color: #be123c;
            border: 1px solid rgba(225, 29, 72, 0.3);
        }
        .rp-link-rose:hover { background: rgba(225, 29, 72, 0.22); }
        .dark .rp-link-rose { color: #fda4af; border-color: rgba(225, 29, 72, 0.4); }

        .rp-link-teal {
            background: rgba(20, 184, 166, 0.12);
            color: #0f766e;
            border: 1px solid rgba(20, 184, 166, 0.3);
        }
        .rp-link-teal:hover { background: rgba(20, 184, 166, 0.22); }
        .dark .rp-link-teal { color: #5eead4; border-color: rgba(20, 184, 166, 0.4); }

        .rp-link-purple {
            background: rgba(168, 85, 247, 0.12);
            color: #7e22ce;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        .rp-link-purple:hover { background: rgba(168, 85, 247, 0.22); }
        .dark .rp-link-purple { color: #e9d5ff; border-color: rgba(168, 85, 247, 0.4); }

        .rp-link-amber {
            background: rgba(245, 158, 11, 0.12);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .rp-link-amber:hover { background: rgba(245, 158, 11, 0.22); }
        .dark .rp-link-amber { color: #fde68a; border-color: rgba(245, 158, 11, 0.4); }

        /* ─── 4. لوحة المؤشرات الحيوية ─── */
        .rp-vital-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.25rem;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .dark .rp-vital-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .rp-vital-header {
            padding-bottom: 0.9rem;
            margin-bottom: 1rem;
            border-bottom: 1.5px solid #e2e8f0;
        }
        .dark .rp-vital-header {
            border-bottom-color: #374151;
        }

        .rp-vital-h3 {
            font-weight: 900;
            font-size: 0.98rem;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .rp-vital-h3 {
            color: #f8fafc;
        }

        .rp-vital-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .rp-vital-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.95rem 1.15rem;
            border-radius: 1rem;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        .dark .rp-vital-item {
            background: #1e293b;
            border-color: #334155;
        }
        .rp-vital-item:hover {
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .rp-vital-lbl {
            font-size: 0.82rem;
            font-weight: 800;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .rp-vital-lbl {
            color: #e2e8f0;
        }

        .rp-vital-val {
            font-size: 1.15rem;
            font-weight: 900;
            font-family: monospace;
        }
    </style>

    <div class="rp-container">
        {{-- ─── 1. شريط الأدوات والفلاتر الذكية ─── --}}
        <div class="rp-filter-card">
            <div>
                <button type="button" onclick="window.print()" class="rp-btn-print">
                    <x-heroicon-o-printer style="width: 1.2rem; height: 1.2rem;" />
                    <span>تصدير وطباعة التقرير الشامل</span>
                </button>
            </div>

            <div class="rp-filters-group">
                <select wire:model.live="period_type" class="rp-select">
                    <option value="this_month">📅 هذا الشهر الحالي</option>
                    <option value="last_month">🗓️ الشهر السابق</option>
                    <option value="this_year">📆 هذه السنة كاملة</option>
                    <option value="custom">⚙️ فترة مخصصة</option>
                </select>

                <div class="rp-date-box">
                    <span>من:</span>
                    <input type="date" wire:model.live="from_date" class="rp-date-input">
                </div>

                <div class="rp-date-box">
                    <span>إلى:</span>
                    <input type="date" wire:model.live="to_date" class="rp-date-input">
                </div>
            </div>
        </div>

        {{-- ─── 2. كروت الإحصائيات الـ 6 ─── --}}
        <div class="rp-kpi-grid">
            {{-- إجمالي الإيرادات --}}
            <div class="rp-kpi-card rp-card-rev">
                <div>
                    <div class="rp-kpi-header">
                        <span class="rp-kpi-title">إجمالي الإيرادات</span>
                        <div class="rp-kpi-icon">
                            <x-heroicon-o-banknotes style="width: 1.25rem; height: 1.25rem;" />
                        </div>
                    </div>
                    <div class="rp-kpi-val">{{ number_format($data['revenue'], 2) }} <span style="font-size: 0.7rem;">ج.م</span></div>
                </div>
                <div>
                    <span class="rp-kpi-sub">محصلة من الرسوم</span>
                </div>
            </div>

            {{-- إجمالي المصروفات --}}
            <div class="rp-kpi-card rp-card-exp">
                <div>
                    <div class="rp-kpi-header">
                        <span class="rp-kpi-title">إجمالي المصروفات</span>
                        <div class="rp-kpi-icon">
                            <x-heroicon-o-arrow-trending-down style="width: 1.25rem; height: 1.25rem;" />
                        </div>
                    </div>
                    <div class="rp-kpi-val">{{ number_format($data['expenses'], 2) }} <span style="font-size: 0.7rem;">ج.م</span></div>
                </div>
                <div>
                    <span class="rp-kpi-sub">نفقات + رواتب</span>
                </div>
            </div>

            {{-- صافي الأرباح --}}
            <div class="rp-kpi-card rp-card-profit">
                <div>
                    <div class="rp-kpi-header">
                        <span class="rp-kpi-title">صافي الأرباح</span>
                        <div class="rp-kpi-icon">
                            <x-heroicon-o-chart-bar style="width: 1.25rem; height: 1.25rem;" />
                        </div>
                    </div>
                    <div class="rp-kpi-val">{{ number_format($data['net_profit'], 2) }} <span style="font-size: 0.7rem;">ج.م</span></div>
                </div>
                <div>
                    <span class="rp-kpi-sub">الإيراد - المصروفات</span>
                </div>
            </div>

            {{-- إجمالي الطلاب --}}
            <div class="rp-kpi-card rp-card-students">
                <div>
                    <div class="rp-kpi-header">
                        <span class="rp-kpi-title">إجمالي الطلاب</span>
                        <div class="rp-kpi-icon">
                            <x-heroicon-o-users style="width: 1.25rem; height: 1.25rem;" />
                        </div>
                    </div>
                    <div class="rp-kpi-val">{{ number_format($data['total_students']) }}</div>
                </div>
                <div>
                    <span class="rp-kpi-sub">المقيدين بالسنتر</span>
                </div>
            </div>

            {{-- سجلات الحضور --}}
            <div class="rp-kpi-card rp-card-att-count">
                <div>
                    <div class="rp-kpi-header">
                        <span class="rp-kpi-title">سجلات الحضور</span>
                        <div class="rp-kpi-icon">
                            <x-heroicon-o-check-circle style="width: 1.25rem; height: 1.25rem;" />
                        </div>
                    </div>
                    <div class="rp-kpi-val">{{ number_format($data['today_present']) }}</div>
                </div>
                <div>
                    <span class="rp-kpi-sub">حضور الحصص بالفترة</span>
                </div>
            </div>

            {{-- معدل الالتزام --}}
            <div class="rp-kpi-card rp-card-att-rate">
                <div>
                    <div class="rp-kpi-header">
                        <span class="rp-kpi-title">معدل الالتزام</span>
                        <div class="rp-kpi-icon">
                            <x-heroicon-o-chart-pie style="width: 1.25rem; height: 1.25rem;" />
                        </div>
                    </div>
                    <div class="rp-kpi-val">{{ $data['attendance_rate'] }}%</div>
                </div>
                <div>
                    <span class="rp-kpi-sub">معدل حضور الطلاب</span>
                </div>
            </div>
        </div>

        {{-- ─── 3. التقارير المباشرة + لوحة المؤشرات الحيوية ─── --}}
        <div class="rp-two-col-grid">
            {{-- جدول روابط السجلات والتقارير المباشرة --}}
            <div class="rp-table-card">
                <div class="rp-table-header">
                    <div class="rp-table-title">
                        <x-heroicon-o-clipboard-document-list style="width: 1.35rem; height: 1.35rem; color: #2563eb;" />
                        <span>مراكز وتقارير المنظومة المباشرة للتصدير والمتابعة</span>
                    </div>
                </div>

                <div class="rp-table-responsive">
                    <table class="rp-table">
                        <thead>
                            <tr>
                                <th>نوع التقرير والسجل</th>
                                <th>الوصف والبيان</th>
                                <th style="text-align: left;">الانتقال المباشر</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-weight: 900;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background: #2563eb; margin-left: 6px;"></span>
                                    سجل مدفوعات واشتراكات الطلاب
                                </td>
                                <td style="color: #64748b; font-size: 0.8rem;" class="dark:text-slate-300">
                                    إيصالات وتحصيلات الرسوم الدراسية وحسابات الطلاب
                                </td>
                                <td style="text-align: left;">
                                    <a href="{{ url('/admin/student-payments') }}" class="rp-link-btn rp-link-blue">
                                        <x-heroicon-o-eye style="width: 1rem; height: 1rem;" />
                                        <span>عرض السجل</span>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: 900;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background: #e11d48; margin-left: 6px;"></span>
                                    سجل المصروفات التشغيلية
                                </td>
                                <td style="color: #64748b; font-size: 0.8rem;" class="dark:text-slate-300">
                                    تفاصيل جميع النفقات والمصروفات ومستلزمات السنتر
                                </td>
                                <td style="text-align: left;">
                                    <a href="{{ url('/admin/expenses') }}" class="rp-link-btn rp-link-rose">
                                        <x-heroicon-o-eye style="width: 1rem; height: 1rem;" />
                                        <span>عرض المصروفات</span>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: 900;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background: #0d9488; margin-left: 6px;"></span>
                                    سجل الرواتب والأجور
                                </td>
                                <td style="color: #64748b; font-size: 0.8rem;" class="dark:text-slate-300">
                                    سجل الرواتب والمستحقات المصروفة للمساعدين والموظفين
                                </td>
                                <td style="text-align: left;">
                                    <a href="{{ url('/admin/salaries') }}" class="rp-link-btn rp-link-teal">
                                        <x-heroicon-o-eye style="width: 1rem; height: 1rem;" />
                                        <span>عرض الرواتب</span>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: 900;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background: #9333ea; margin-left: 6px;"></span>
                                    سجل الحصص والجلسات والغياب
                                </td>
                                <td style="color: #64748b; font-size: 0.8rem;" class="dark:text-slate-300">
                                    سجلات حضور وغياب الطلاب والملاحظات الأكاديمية
                                </td>
                                <td style="text-align: left;">
                                    <a href="{{ url('/admin/group-sessions') }}" class="rp-link-btn rp-link-purple">
                                        <x-heroicon-o-eye style="width: 1rem; height: 1rem;" />
                                        <span>عرض الجلسات</span>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: 900;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background: #d97706; margin-left: 6px;"></span>
                                    سجل الامتحانات والتقييمات
                                </td>
                                <td style="color: #64748b; font-size: 0.8rem;" class="dark:text-slate-300">
                                    درجات ونتائج الامتحانات الورقية والإلكترونية المرصودة
                                </td>
                                <td style="text-align: left;">
                                    <a href="{{ url('/admin/exams') }}" class="rp-link-btn rp-link-amber">
                                        <x-heroicon-o-eye style="width: 1rem; height: 1rem;" />
                                        <span>عرض الامتحانات</span>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- لوحة مؤشرات الأداء الحيوية --}}
            <div class="rp-vital-card">
                <div class="rp-vital-header">
                    <h3 class="rp-vital-h3">
                        <x-heroicon-o-bolt style="width: 1.35rem; height: 1.35rem; color: #f59e0b;" />
                        <span>مؤشرات الأداء الحيوية للفترة</span>
                    </h3>
                </div>

                <div class="rp-vital-list">
                    {{-- متوسط دخل الطالب --}}
                    <div class="rp-vital-item">
                        <div class="rp-vital-lbl">
                            <x-heroicon-o-currency-dollar style="width: 1.2rem; height: 1.2rem; color: #2563eb;" />
                            <span>متوسط دخل الطالب بالفترة</span>
                        </div>
                        <div class="rp-vital-val" style="color: #2563eb;" class="dark:text-blue-400">
                            {{ number_format($data['avg_fee'], 2) }} ج.م
                        </div>
                    </div>

                    {{-- نسبة سداد الاشتراكات --}}
                    <div class="rp-vital-item">
                        <div class="rp-vital-lbl">
                            <x-heroicon-o-check-badge style="width: 1.2rem; height: 1.2rem; color: #059669;" />
                            <span>نسبة سداد الاشتراكات</span>
                        </div>
                        <div class="rp-vital-val" style="color: #059669;" class="dark:text-emerald-400">
                            {{ $data['payment_rate'] }}%
                        </div>
                    </div>

                    {{-- الطلاب غير المسددين --}}
                    <div class="rp-vital-item">
                        <div class="rp-vital-lbl">
                            <x-heroicon-o-exclamation-circle style="width: 1.2rem; height: 1.2rem; color: #dc2626;" />
                            <span>الطلاب غير المسددين</span>
                        </div>
                        <div class="rp-vital-val" style="color: #dc2626;" class="dark:text-rose-400">
                            {{ number_format($data['late_students']) }} طالب
                        </div>
                    </div>

                    {{-- إجمالي الحركات المالية --}}
                    <div class="rp-vital-item">
                        <div class="rp-vital-lbl">
                            <x-heroicon-o-arrows-right-left style="width: 1.2rem; height: 1.2rem; color: #9333ea;" />
                            <span>إجمالي المعاملات بالفترة</span>
                        </div>
                        <div class="rp-vital-val" style="color: #9333ea;" class="dark:text-purple-400">
                            {{ number_format($data['total_transactions']) }} حركة
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>