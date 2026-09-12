<x-filament-panels::page>
    <style>
        .sp-container {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
            width: 100%;
            font-family: inherit;
        }

        /* ─── 1. شريط الفلاتر والتحكم الذكي ─── */
        .sp-filter-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(12px);
        }
        .dark .sp-filter-card {
            background: rgba(17, 24, 39, 0.85);
            border-color: rgba(55, 65, 81, 0.6);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .sp-filter-grid {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        @media (min-width: 768px) {
            .sp-filter-grid {
                flex-direction: row;
                align-items: flex-end;
                justify-content: space-between;
            }
        }

        .sp-input-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            flex: 1;
            max-width: 500px;
        }

        .sp-label {
            font-size: 0.8rem;
            font-weight: 800;
            color: #374151;
        }
        .dark .sp-label {
            color: #d1d5db;
        }

        .sp-select {
            width: 100%;
            border-radius: 0.85rem;
            padding: 0.65rem 1rem;
            font-size: 0.85rem;
            font-weight: 700;
            background-color: #f9fafb;
            color: #111827;
            border: 1px solid #d1d5db;
            outline: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .dark .sp-select {
            background-color: #1f2937;
            color: #f9fafb;
            border-color: #374151;
        }
        .sp-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }

        .sp-actions-bar {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .sp-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.6rem 1rem;
            border-radius: 0.85rem;
            font-size: 0.78rem;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .sp-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .sp-btn-pdf {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
        }
        .sp-btn-cert {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
        }
        .sp-btn-card {
            background: #ffffff;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        .dark .sp-btn-card {
            background: #1f2937;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        /* ─── 2. رادار الإنذار المبكر للطلاب المتعثرين ─── */
        .sp-radar-card {
            background: linear-gradient(135deg, rgba(255, 241, 242, 0.85) 0%, rgba(255, 255, 255, 0.95) 50%, rgba(255, 241, 242, 0.5) 100%);
            border: 1.5px solid rgba(244, 63, 94, 0.4);
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 8px 25px -4px rgba(244, 63, 94, 0.15);
            position: relative;
            overflow: hidden;
        }
        .dark .sp-radar-card {
            background: linear-gradient(135deg, rgba(136, 19, 55, 0.3) 0%, rgba(17, 24, 39, 0.95) 50%, rgba(136, 19, 55, 0.15) 100%);
            border-color: rgba(225, 29, 72, 0.5);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.4);
        }

        .sp-radar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 0.85rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(244, 63, 94, 0.2);
            flex-wrap: wrap;
        }

        .sp-radar-title {
            font-size: 0.95rem;
            font-weight: 900;
            color: #be123c;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .sp-radar-title {
            color: #fda4af;
        }

        .sp-radar-sub {
            font-size: 0.75rem;
            color: #9f1239;
            font-weight: 700;
            margin-top: 0.2rem;
        }
        .dark .sp-radar-sub {
            color: #fecdd3;
        }

        .sp-radar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
        }

        .sp-risk-item {
            background: #ffffff;
            border-radius: 1rem;
            padding: 1rem;
            border: 1px solid #fecdd3;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.6rem;
            transition: all 0.2s ease;
        }
        .dark .sp-risk-item {
            background: #1f2937;
            border-color: #4c1d24;
        }
        .sp-risk-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .sp-risk-high {
            border-right: 4px solid #e11d48;
        }
        .sp-risk-medium {
            border-right: 4px solid #f59e0b;
        }

        .sp-risk-pill {
            font-size: 0.68rem;
            font-weight: 800;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            white-space: nowrap;
        }
        .sp-pill-high {
            background: rgba(225, 29, 72, 0.15);
            color: #be123c;
            border: 1px solid rgba(225, 29, 72, 0.3);
        }
        .dark .sp-pill-high {
            color: #fca5a5;
        }
        .sp-pill-med {
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .dark .sp-pill-med {
            color: #fcd34d;
        }

        /* ─── 3. كرت هوية الطالب والتقييم المركب ─── */
        .sp-profile-banner {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #334155;
            border-radius: 1.5rem;
            padding: 1.5rem;
            color: #ffffff;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            position: relative;
            overflow: hidden;
        }
        @media (min-width: 768px) {
            .sp-profile-banner {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 1.75rem 2rem;
            }
        }

        .sp-profile-info {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .sp-profile-avatar {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 1.25rem;
            background: rgba(245, 158, 11, 0.15);
            border: 2px solid #f59e0b;
            color: #fbbf24;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 900;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
        }

        .sp-profile-name {
            font-size: 1.35rem;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .sp-profile-meta {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .sp-score-box {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(71, 85, 105, 0.6);
            border-radius: 1.25rem;
            padding: 0.85rem 1.25rem;
            box-shadow: inset 0 2px 6px rgba(0,0,0,0.3);
        }

        .sp-score-item {
            text-align: center;
        }
        .sp-score-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 0.2rem;
        }
        .sp-score-val {
            font-size: 1.6rem;
            font-weight: 900;
            font-family: monospace;
            color: #10b981;
            line-height: 1;
        }
        .sp-score-badge {
            font-size: 0.95rem;
            font-weight: 900;
            color: #fbbf24;
        }

        /* ─── 4. بطاقات المؤشرات الأربعة (KPIs) ─── */
        .sp-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .sp-kpi-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1.25rem;
            padding: 1.25rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .dark .sp-kpi-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
        }
        .sp-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .sp-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .sp-kpi-title {
            font-size: 0.8rem;
            font-weight: 800;
            color: #4b5563;
        }
        .dark .sp-kpi-title {
            color: #9ca3af;
        }

        .sp-kpi-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sp-kpi-value {
            font-size: 1.75rem;
            font-weight: 900;
            font-family: monospace;
            margin: 0.25rem 0;
            line-height: 1.1;
        }

        .sp-kpi-sub {
            font-size: 0.72rem;
            font-weight: 700;
            color: #6b7280;
        }
        .dark .sp-kpi-sub {
            color: #9ca3af;
        }

        /* ألوان الكروت الأربعة */
        .sp-kpi-blue .sp-kpi-icon { background: rgba(59, 130, 246, 0.15); color: #2563eb; }
        .sp-kpi-blue .sp-kpi-value { color: #2563eb; }
        .dark .sp-kpi-blue .sp-kpi-value { color: #60a5fa; }

        .sp-kpi-emerald .sp-kpi-icon { background: rgba(16, 185, 129, 0.15); color: #059669; }
        .sp-kpi-emerald .sp-kpi-value { color: #059669; }
        .dark .sp-kpi-emerald .sp-kpi-value { color: #34d399; }

        .sp-kpi-purple .sp-kpi-icon { background: rgba(168, 85, 247, 0.15); color: #9333ea; }
        .sp-kpi-purple .sp-kpi-value { color: #9333ea; }
        .dark .sp-kpi-purple .sp-kpi-value { color: #c084fc; }

        .sp-kpi-amber .sp-kpi-icon { background: rgba(245, 158, 11, 0.15); color: #d97706; }
        .sp-kpi-amber .sp-kpi-value { color: #d97706; }
        .dark .sp-kpi-amber .sp-kpi-value { color: #fbbf24; }

        /* ─── 5. جدول سجل نتائج الامتحانات ─── */
        .sp-table-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .sp-table-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .sp-table-header {
            padding: 1rem 1.5rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .dark .sp-table-header {
            background: #1f2937;
            border-bottom-color: #374151;
        }

        .sp-table-title {
            font-size: 0.95rem;
            font-weight: 900;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .sp-table-title {
            color: #ffffff;
        }

        .sp-table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .sp-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.82rem;
        }

        .sp-table th {
            padding: 0.85rem 1rem;
            background: #f3f4f6;
            color: #4b5563;
            font-weight: 800;
            font-size: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }
        .dark .sp-table th {
            background: #182234;
            color: #9ca3af;
            border-bottom-color: #374151;
        }

        .sp-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            font-weight: 600;
            white-space: nowrap;
        }
        .dark .sp-table td {
            border-bottom-color: #1f2937;
            color: #d1d5db;
        }

        .sp-table tr:hover td {
            background: rgba(249, 250, 251, 0.8);
        }
        .dark .sp-table tr:hover td {
            background: rgba(31, 41, 55, 0.5);
        }

        .sp-grade-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.75rem;
            border-radius: 0.6rem;
            font-size: 0.75rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .sp-grade-excellent {
            background: rgba(16, 185, 129, 0.15);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .dark .sp-grade-excellent { color: #34d399; }

        .sp-grade-vgood {
            background: rgba(59, 130, 246, 0.15);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        .dark .sp-grade-vgood { color: #60a5fa; }

        .sp-grade-good {
            background: rgba(245, 158, 11, 0.15);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .dark .sp-grade-good { color: #fbbf24; }

        .sp-grade-fail {
            background: rgba(239, 68, 68, 0.15);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .dark .sp-grade-fail { color: #f87171; }
    </style>

    <div class="sp-container">
        {{-- ─── 1. شريط اختيار الطالب وأزرار الإجراء ─── --}}
        <div class="sp-filter-card">
            <div class="sp-filter-grid">
                <div class="sp-input-group">
                    <label class="sp-label">🔍 اختر الطالب لعرض السجل والتحليل الشامل:</label>
                    <select wire:model.live="selectedStudentId" class="sp-select">
                        @foreach($this->studentsList as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} (كود: {{ $st->qr_code }})</option>
                        @endforeach
                    </select>
                </div>

                @if($this->analytics)
                    <div class="sp-actions-bar">
                        <a href="{{ route('student.monthly-report.pdf', ['record' => $this->analytics['student']->id]) }}" 
                            target="_blank" 
                            class="sp-action-btn sp-btn-pdf">
                            <x-heroicon-o-document-chart-bar style="width: 1.1rem; height: 1.1rem;" />
                            <span>كارت التقرير الشهري (PDF)</span>
                        </a>

                        <a href="{{ route('student.certificate.print', ['record' => $this->analytics['student']->id]) }}" 
                            target="_blank" 
                            class="sp-action-btn sp-btn-cert">
                            <x-heroicon-o-academic-cap style="width: 1.1rem; height: 1.1rem;" />
                            <span>شهادة التقدير</span>
                        </a>

                        <a href="{{ route('student.card.print', ['record' => $this->analytics['student']->id]) }}" 
                            target="_blank" 
                            class="sp-action-btn sp-btn-card">
                            <x-heroicon-o-identification style="width: 1.1rem; height: 1.1rem;" />
                            <span>طباعة الكارنيه</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── 2. رادار الإنذار المبكر للطلاب المتعثرين (Early Warning Radar) ─── --}}
        @if(!empty($this->atRiskStudents) && count($this->atRiskStudents) > 0)
            <div class="sp-radar-card">
                <div class="sp-radar-header">
                    <div>
                        <div class="sp-radar-title">
                            <x-heroicon-s-exclamation-triangle style="width: 1.3rem; height: 1.3rem;" />
                            <span>رادار الإنذار المبكر للطلاب المتعثرين (Early Warning)</span>
                        </div>
                        <div class="sp-radar-sub">
                            تم رصد {{ count($this->atRiskStudents) }} طلاب بحاجة إلى تدخل فوري ومتابعة لتعويض الغياب أو هبوط الدرجات.
                        </div>
                    </div>
                    <span class="sp-risk-pill sp-pill-high" style="font-size: 0.75rem; padding: 0.35rem 0.85rem;">
                        {{ count($this->atRiskStudents) }} إنذارات نشطة
                    </span>
                </div>

                <div class="sp-radar-grid">
                    @foreach($this->atRiskStudents as $item)
                        @php
                            $isHigh = $item['severity'] === 'high';
                            $riskClass = $isHigh ? 'sp-risk-high' : 'sp-risk-medium';
                            $pillClass = $isHigh ? 'sp-pill-high' : 'sp-pill-med';
                        @endphp
                        <div class="sp-risk-item {{ $riskClass }}">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem;">
                                <div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: #111827;" class="dark:text-white">
                                        {{ $item['student']->name }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: #6b7280; font-family: monospace;">
                                        كود: {{ $item['student']->qr_code }} • {{ $item['student']->educationalStage?->name ?? '—' }}
                                    </div>
                                </div>
                                <span class="sp-risk-pill {{ $pillClass }}">
                                    {{ $isHigh ? 'إنذار مرتفع' : 'تنبيه متابعة' }}
                                </span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.25rem; margin: 0.25rem 0;">
                                @foreach($item['reasons'] as $reason)
                                    <div style="font-size: 0.75rem; font-weight: 700; color: {{ $isHigh ? '#be123c' : '#b45309' }}; display: flex; align-items: center; gap: 0.3rem;">
                                        <span>•</span>
                                        <span>{{ $reason }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding-top: 0.5rem; border-top: 1px solid rgba(0,0,0,0.06);">
                                <button wire:click="$set('selectedStudentId', {{ $item['student']->id }})" 
                                    style="font-size: 0.75rem; font-weight: 800; color: #2563eb; background: none; border: none; cursor: pointer; text-decoration: underline; padding: 0;">
                                    عرض السجل الكامل
                                </button>
                                <a href="{{ route('student.monthly-report.pdf', $item['student']->id) }}" 
                                    target="_blank" 
                                    style="font-size: 0.72rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 0.5rem; background: rgba(16, 185, 129, 0.12); color: #059669; text-decoration: none;">
                                    التقرير الشهري
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ─── 3. الملف التعريفي والمؤشرات للطالب المختار ─── --}}
        @if($this->analytics)
            @php
                $student = $this->analytics['student'];
                $att = $this->analytics['attendance'];
                $exm = $this->analytics['exams'];
                $hw = $this->analytics['homeworks'];
                $ovr = $this->analytics['overall'];

                $nameParts = explode(' ', trim($student->name));
                $initials = mb_substr($nameParts[0] ?? '', 0, 1) . ' ' . mb_substr($nameParts[1] ?? '', 0, 1);
            @endphp

            {{-- بانر هوية الطالب والتقييم المركب --}}
            <div class="sp-profile-banner">
                <div class="sp-profile-info">
                    <div class="sp-profile-avatar">
                        {{ $initials }}
                    </div>
                    <div>
                        <div class="sp-profile-name">
                            <span>{{ $student->name }}</span>
                            <span style="font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.7rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.4); color: #fbbf24; font-family: monospace;">
                                {{ $student->qr_code }}
                            </span>
                        </div>
                        <div class="sp-profile-meta">
                            <span>{{ $student->educationalStage?->name ?? 'المرحلة العامة' }}</span>
                            <span>•</span>
                            <span>هاتف ولي الأمر: <strong style="color: #ffffff; font-family: monospace;" dir="ltr">{{ $student->parent_phone }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="sp-score-box">
                    <div class="sp-score-item">
                        <div class="sp-score-label">المستوى والتقدير</div>
                        <div class="sp-score-badge">{{ $ovr['grade']['label'] }}</div>
                    </div>
                    <div style="width: 1px; height: 2.25rem; background: rgba(71, 85, 105, 0.6);"></div>
                    <div class="sp-score-item">
                        <div class="sp-score-label">المعدل العام المركب</div>
                        <div class="sp-score-val">{{ $ovr['score'] }}%</div>
                    </div>
                </div>
            </div>

            {{-- ─── 4. بطاقات المؤشرات الأربعة (KPIs) ─── --}}
            <div class="sp-kpi-grid">
                {{-- 1. الحضور والالتزام --}}
                <div class="sp-kpi-card sp-kpi-blue">
                    <div>
                        <div class="sp-kpi-top">
                            <span class="sp-kpi-title">نسبة الحضور</span>
                            <div class="sp-kpi-icon">
                                <x-heroicon-o-calendar style="width: 1.25rem; height: 1.25rem;" />
                            </div>
                        </div>
                        <div class="sp-kpi-value">{{ $att['rate'] }}%</div>
                    </div>
                    <div class="sp-kpi-sub">
                        حضر {{ $att['present'] }} من {{ $att['total'] }} حصة (غياب: {{ $att['absent'] }})
                    </div>
                </div>

                {{-- 2. متوسط الامتحانات --}}
                <div class="sp-kpi-card sp-kpi-emerald">
                    <div>
                        <div class="sp-kpi-top">
                            <span class="sp-kpi-title">متوسط الامتحانات</span>
                            <div class="sp-kpi-icon">
                                <x-heroicon-o-academic-cap style="width: 1.25rem; height: 1.25rem;" />
                            </div>
                        </div>
                        <div class="sp-kpi-value">{{ $exm['average'] }}%</div>
                    </div>
                    <div class="sp-kpi-sub">
                        خاض {{ $exm['total'] }} اختباراً (أعلى درجة: {{ $exm['highest'] }}%)
                    </div>
                </div>

                {{-- 3. الواجبات والتكليفات --}}
                <div class="sp-kpi-card sp-kpi-purple">
                    <div>
                        <div class="sp-kpi-top">
                            <span class="sp-kpi-title">الواجبات والتكليفات</span>
                            <div class="sp-kpi-icon">
                                <x-heroicon-o-book-open style="width: 1.25rem; height: 1.25rem;" />
                            </div>
                        </div>
                        <div class="sp-kpi-value">{{ $hw['submitted'] }}</div>
                    </div>
                    <div class="sp-kpi-sub">
                        متوسط التقييم: {{ $hw['average'] ? $hw['average'].' / 10' : 'مكتمل' }}
                    </div>
                </div>

                {{-- 4. الرتبة والشارة --}}
                <div class="sp-kpi-card sp-kpi-amber">
                    <div>
                        <div class="sp-kpi-top">
                            <span class="sp-kpi-title">الرتبة الأكاديمية</span>
                            <div class="sp-kpi-icon">
                                <x-heroicon-o-trophy style="width: 1.25rem; height: 1.25rem;" />
                            </div>
                        </div>
                        <div class="sp-kpi-value" style="font-size: 1.35rem;">{{ $ovr['grade']['badge'] }}</div>
                    </div>
                    <div class="sp-kpi-sub" style="color: #d97706;" class="dark:text-amber-400">
                        {{ $ovr['grade']['label'] }}
                    </div>
                </div>
            </div>

            {{-- ─── 5. جدول السجل الزمني لنتائج الامتحانات ─── --}}
            <div class="sp-table-card">
                <div class="sp-table-header">
                    <div class="sp-table-title">
                        <x-heroicon-o-chart-bar-square style="width: 1.3rem; height: 1.3rem; color: #10b981;" />
                        <span>السجل الزمني لنتائج امتحانات الطالب</span>
                    </div>
                    <div class="sp-grade-pill sp-grade-excellent" style="font-size: 0.72rem;">
                        {{ count($exm['history']) }} اختبارات مرصودة
                    </div>
                </div>

                <div class="sp-table-responsive">
                    <table class="sp-table">
                        <thead>
                            <tr>
                                <th>عنوان الامتحان</th>
                                <th>التاريخ</th>
                                <th style="text-align: center;">الدرجة المحصلة</th>
                                <th style="text-align: center;">الدرجة العظمى</th>
                                <th style="text-align: center;">النسبة المئوية</th>
                                <th style="text-align: center;">التقييم والتقدير</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exm['history'] as $item)
                                @php
                                    $pct = $item['percentage'];
                                    if ($pct >= 90) {
                                        $pillClass = 'sp-grade-excellent';
                                        $label = 'ممتاز 🌟';
                                    } elseif ($pct >= 75) {
                                        $pillClass = 'sp-grade-vgood';
                                        $label = 'جيد جداً 👍';
                                    } elseif ($pct >= 50) {
                                        $pillClass = 'sp-grade-good';
                                        $label = 'مقبول ⚠️';
                                    } else {
                                        $pillClass = 'sp-grade-fail';
                                        $label = 'راسب ❌';
                                    }
                                @endphp
                                <tr>
                                    <td style="font-weight: 800; font-size: 0.88rem;">
                                        {{ $item['title'] }}
                                    </td>
                                    <td style="font-family: monospace; font-size: 0.78rem; color: #6b7280;">
                                        {{ $item['date'] }}
                                    </td>
                                    <td style="text-align: center; font-weight: 900; font-family: monospace; font-size: 0.95rem; color: #059669;">
                                        {{ $item['score'] }}
                                    </td>
                                    <td style="text-align: center; font-family: monospace; font-size: 0.8rem; color: #6b7280;">
                                        {{ $item['max'] }}
                                    </td>
                                    <td style="text-align: center; font-weight: 900; font-family: monospace; font-size: 0.9rem;">
                                        <span style="color: {{ $pct >= 85 ? '#059669' : ($pct >= 65 ? '#d97706' : '#dc2626') }};">
                                            {{ $pct }}%
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="sp-grade-pill {{ $pillClass }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2.5rem; color: #9ca3af;">
                                        <x-heroicon-o-academic-cap style="width: 2.5rem; height: 2.5rem; margin: 0 auto 0.5rem; opacity: 0.5;" />
                                        <p style="font-weight: 700;">لا توجد امتحانات مسجلة لهذا الطالب حتى الآن.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
