<x-filament-panels::page>
    <style>
        .lb-container {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
            width: 100%;
            font-family: inherit;
        }

        /* ─── الفلاتر والتحكم ─── */
        .lb-filter-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(12px);
        }
        .dark .lb-filter-card {
            background: rgba(17, 24, 39, 0.85);
            border-color: rgba(55, 65, 81, 0.6);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .lb-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            align-items: flex-end;
        }

        .lb-input-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .lb-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #374151;
        }
        .dark .lb-label {
            color: #d1d5db;
        }

        .lb-select {
            width: 100%;
            border-radius: 0.85rem;
            padding: 0.65rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            background-color: #f9fafb;
            color: #111827;
            border: 1px solid #d1d5db;
            outline: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .dark .lb-select {
            background-color: #1f2937;
            color: #f9fafb;
            border-color: #374151;
        }
        .lb-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }

        .lb-info-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1rem;
            border-radius: 0.85rem;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: #d97706;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .dark .lb-info-pill {
            background: rgba(245, 158, 11, 0.15);
            border-color: rgba(245, 158, 11, 0.3);
            color: #fbbf24;
        }

        /* ─── منصة الأبطال (Podium) ─── */
        .lb-podium-header {
            text-align: center;
            margin-bottom: 0.75rem;
        }
        .lb-podium-title {
            font-size: 1.35rem;
            font-weight: 900;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .dark .lb-podium-title {
            color: #ffffff;
        }
        .lb-podium-sub {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.25rem;
            font-weight: 600;
        }
        .dark .lb-podium-sub {
            color: #9ca3af;
        }

        .lb-podium-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            align-items: flex-end;
            padding-top: 1rem;
        }
        @media (min-width: 1024px) {
            .lb-podium-grid {
                grid-template-columns: 1fr 1.08fr 1fr;
            }
        }

        .lb-card {
            border-radius: 1.5rem;
            padding: 1.5rem;
            position: relative;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .lb-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.15);
        }

        /* بطاقة المركز الأول (Gold) */
        .lb-card-first {
            background: linear-gradient(180deg, rgba(254, 243, 199, 0.6) 0%, rgba(255, 255, 255, 0.95) 40%, rgba(254, 243, 199, 0.3) 100%);
            border: 2px solid #f59e0b;
            box-shadow: 0 12px 30px rgba(245, 158, 11, 0.25);
        }
        .dark .lb-card-first {
            background: linear-gradient(180deg, rgba(245, 158, 11, 0.15) 0%, rgba(17, 24, 39, 0.95) 40%, rgba(245, 158, 11, 0.08) 100%);
            border-color: #f59e0b;
            box-shadow: 0 15px 35px rgba(245, 158, 11, 0.2);
        }
        @media (min-width: 1024px) {
            .lb-card-first {
                transform: translateY(-12px);
            }
            .lb-card-first:hover {
                transform: translateY(-16px);
            }
        }

        /* بطاقة المركز الثاني (Silver) */
        .lb-card-second {
            background: linear-gradient(180deg, rgba(241, 245, 249, 0.7) 0%, rgba(255, 255, 255, 0.95) 40%, rgba(241, 245, 249, 0.3) 100%);
            border: 1.5px solid #94a3b8;
            box-shadow: 0 8px 25px rgba(148, 163, 184, 0.2);
        }
        .dark .lb-card-second {
            background: linear-gradient(180deg, rgba(148, 163, 184, 0.12) 0%, rgba(17, 24, 39, 0.95) 40%, rgba(148, 163, 184, 0.05) 100%);
            border-color: #64748b;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        /* بطاقة المركز الثالث (Bronze) */
        .lb-card-third {
            background: linear-gradient(180deg, rgba(255, 237, 213, 0.6) 0%, rgba(255, 255, 255, 0.95) 40%, rgba(255, 237, 213, 0.25) 100%);
            border: 1.5px solid #d97706;
            box-shadow: 0 8px 25px rgba(217, 119, 6, 0.18);
        }
        .dark .lb-card-third {
            background: linear-gradient(180deg, rgba(217, 119, 6, 0.12) 0%, rgba(17, 24, 39, 0.95) 40%, rgba(217, 119, 6, 0.05) 100%);
            border-color: #b45309;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        /* الشارة العلوية */
        .lb-badge-pill {
            position: absolute;
            top: -0.85rem;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.3rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 800;
            white-space: nowrap;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .lb-badge-gold {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #1c1917;
        }
        .lb-badge-silver {
            background: linear-gradient(135deg, #cbd5e1, #94a3b8);
            color: #0f172a;
        }
        .lb-badge-bronze {
            background: linear-gradient(135deg, #fdba74, #ea580c);
            color: #ffffff;
        }

        /* الأفاتار */
        .lb-avatar {
            width: 4.5rem;
            height: 4.5rem;
            margin: 0.75rem auto 0.5rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            font-weight: 900;
            position: relative;
        }
        .lb-avatar-gold {
            background: rgba(245, 158, 11, 0.2);
            color: #d97706;
            border: 3px solid #f59e0b;
        }
        .dark .lb-avatar-gold {
            color: #fbbf24;
        }
        .lb-avatar-silver {
            background: rgba(148, 163, 184, 0.2);
            color: #475569;
            border: 3px solid #94a3b8;
        }
        .dark .lb-avatar-silver {
            color: #cbd5e1;
        }
        .lb-avatar-bronze {
            background: rgba(217, 119, 6, 0.2);
            color: #c2410c;
            border: 3px solid #ea580c;
        }
        .dark .lb-avatar-bronze {
            color: #fdba74;
        }

        .lb-avatar-icon {
            position: absolute;
            bottom: -0.25rem;
            right: -0.25rem;
            font-size: 1.25rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        .lb-student-name {
            font-size: 1.05rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.25rem;
            line-height: 1.4;
        }
        .dark .lb-student-name {
            color: #f9fafb;
        }

        .lb-student-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        .dark .lb-student-meta {
            color: #9ca3af;
        }

        /* صندوق المؤشرات */
        .lb-metrics-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 1rem;
            background: rgba(243, 244, 246, 0.7);
            border: 1px solid rgba(229, 231, 235, 0.8);
            margin-bottom: 1rem;
        }
        .dark .lb-metrics-box {
            background: rgba(31, 41, 55, 0.7);
            border-color: rgba(55, 65, 81, 0.8);
        }

        .lb-metric-item {
            display: flex;
            flex-direction: column;
            text-align: right;
        }
        .lb-metric-title {
            font-size: 0.7rem;
            font-weight: 700;
            color: #6b7280;
        }
        .dark .lb-metric-title {
            color: #9ca3af;
        }
        .lb-metric-val {
            font-size: 1.05rem;
            font-weight: 900;
            font-family: monospace;
            margin: 0.15rem 0;
        }
        .lb-val-exam {
            color: #059669;
        }
        .dark .lb-val-exam {
            color: #34d399;
        }
        .lb-val-att {
            color: #2563eb;
        }
        .dark .lb-val-att {
            color: #60a5fa;
        }

        .lb-bar-bg {
            width: 100%;
            height: 5px;
            background: #e5e7eb;
            border-radius: 9999px;
            overflow: hidden;
        }
        .dark .lb-bar-bg {
            background: #374151;
        }
        .lb-bar-fill-exam {
            background: #10b981;
            height: 100%;
            border-radius: 9999px;
        }
        .lb-bar-fill-att {
            background: #3b82f6;
            height: 100%;
            border-radius: 9999px;
        }

        /* مجاميع النقاط والأزرار */
        .lb-total-score-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.78rem;
            font-weight: 700;
            color: #4b5563;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(229, 231, 235, 0.8);
            margin-bottom: 0.75rem;
        }
        .dark .lb-total-score-row {
            color: #d1d5db;
            border-bottom-color: rgba(55, 65, 81, 0.8);
        }

        .lb-score-tag {
            font-size: 1.05rem;
            font-weight: 900;
            font-family: monospace;
            color: #d97706;
        }
        .dark .lb-score-tag {
            color: #fbbf24;
        }

        .lb-btn-cert {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            width: 100%;
            padding: 0.65rem 1rem;
            border-radius: 0.85rem;
            font-size: 0.78rem;
            font-weight: 800;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
        }
        .lb-btn-cert:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
        }

        /* ─── جدول الأوائل ─── */
        .lb-table-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .lb-table-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
        }

        .lb-table-header {
            padding: 1rem 1.5rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .dark .lb-table-header {
            background: #1f2937;
            border-bottom-color: #374151;
        }

        .lb-table-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .lb-table-title {
            color: #ffffff;
        }

        .lb-table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .lb-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.82rem;
        }

        .lb-table th {
            padding: 0.85rem 1rem;
            background: #f3f4f6;
            color: #4b5563;
            font-weight: 800;
            font-size: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }
        .dark .lb-table th {
            background: #182234;
            color: #9ca3af;
            border-bottom-color: #374151;
        }

        .lb-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            font-weight: 600;
            white-space: nowrap;
        }
        .dark .lb-table td {
            border-bottom-color: #1f2937;
            color: #d1d5db;
        }

        .lb-table tr:hover td {
            background: rgba(249, 250, 251, 0.8);
        }
        .dark .lb-table tr:hover td {
            background: rgba(31, 41, 55, 0.5);
        }

        .lb-rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.85rem;
            height: 1.85rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 900;
        }
        .lb-rank-1 {
            background: #f59e0b;
            color: #1c1917;
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
        }
        .lb-rank-2 {
            background: #94a3b8;
            color: #ffffff;
        }
        .lb-rank-3 {
            background: #ea580c;
            color: #ffffff;
        }
        .lb-rank-other {
            background: #e5e7eb;
            color: #4b5563;
        }
        .dark .lb-rank-other {
            background: #374151;
            color: #d1d5db;
        }

        .lb-tag-pill {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 0.5rem;
            background: #f3f4f6;
            font-size: 0.72rem;
            font-weight: 700;
            color: #4b5563;
        }
        .dark .lb-tag-pill {
            background: #1f2937;
            color: #9ca3af;
        }

        .lb-btn-small {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.35rem 0.75rem;
            border-radius: 0.6rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .lb-btn-cert-small {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .lb-btn-cert-small:hover {
            background: rgba(245, 158, 11, 0.22);
        }
        .dark .lb-btn-cert-small {
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.4);
        }

        .lb-btn-rep-small {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .lb-btn-rep-small:hover {
            background: rgba(16, 185, 129, 0.22);
        }
        .dark .lb-btn-rep-small {
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.4);
        }
    </style>

    <div class="lb-container">
        {{-- ─── 1. شريط الفلاتر الذكية ─── --}}
        <div class="lb-filter-card">
            <div class="lb-filter-grid">
                <div class="lb-input-group">
                    <label class="lb-label">تصفية حسب المرحلة الدراسية</label>
                    <select wire:model.live="selectedStage" class="lb-select">
                        <option value="">جميع المراحل الدراسية</option>
                        @foreach($stages as $stg)
                            <option value="{{ $stg->id }}">{{ $stg->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lb-input-group">
                    <label class="lb-label">تصفية حسب المجموعة الدراسية</label>
                    <select wire:model.live="selectedGroup" class="lb-select">
                        <option value="">جميع المجموعات الدراسية</option>
                        @foreach($groups as $grp)
                            <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lb-info-pill">
                    <x-heroicon-o-sparkles style="width: 1.1rem; height: 1.1rem; flex-shrink: 0;" />
                    <span>معيار الترتيب: الامتحانات 75% + الحضور 25%</span>
                </div>
            </div>
        </div>

        {{-- ─── 2. منصة التتويج للثلاثة الأوائل (Podium of Champions) ─── --}}
        @php
            $top3 = $topStudents->take(3);
            
            // ترتيب المنصة في الواجهة العربية (RTL):
            // المركز الثاني (الفضي) على اليمين، المركز الأول (الذهبي) في المنتصف وأعلى، المركز الثالث (البرونزي) على اليسار
            $first = $top3->firstWhere('rank', 1);
            $second = $top3->firstWhere('rank', 2);
            $third = $top3->firstWhere('rank', 3);

            $podiumList = collect();
            if ($second) $podiumList->push($second);
            if ($first) $podiumList->push($first);
            if ($third) $podiumList->push($third);
        @endphp

        @if($top3->isNotEmpty())
            <div>
                <div class="lb-podium-header">
                    <h3 class="lb-podium-title">
                        <x-heroicon-s-trophy style="width: 1.5rem; height: 1.5rem; color: #f59e0b;" />
                        <span>منصة أوائل المنظومة</span>
                    </h3>
                    <p class="lb-podium-sub">تكريم الطلاب الأكثر تفوقاً والتزاماً بالدرجات والحضور</p>
                </div>

                <div class="lb-podium-grid">
                    @foreach($podiumList as $item)
                        @php
                            $rank = $item['rank'];
                            $isFirst = $rank === 1;
                            $isSecond = $rank === 2;
                            $isThird = $rank === 3;

                            if ($isFirst) {
                                $cardClass = 'lb-card-first';
                                $badgeClass = 'lb-badge-gold';
                                $avatarClass = 'lb-avatar-gold';
                                $rankText = 'المركز الأول (الذهبي)';
                                $medal = '🥇';
                            } elseif ($isSecond) {
                                $cardClass = 'lb-card-second';
                                $badgeClass = 'lb-badge-silver';
                                $avatarClass = 'lb-avatar-silver';
                                $rankText = 'المركز الثاني (الفضي)';
                                $medal = '🥈';
                            } else {
                                $cardClass = 'lb-card-third';
                                $badgeClass = 'lb-badge-bronze';
                                $avatarClass = 'lb-avatar-bronze';
                                $rankText = 'المركز الثالث (البرونزي)';
                                $medal = '🥉';
                            }

                            $nameParts = explode(' ', trim($item['student_name']));
                            $initials = mb_substr($nameParts[0] ?? '', 0, 1) . ' ' . mb_substr($nameParts[1] ?? '', 0, 1);
                        @endphp

                        <div class="lb-card {{ $cardClass }}">
                            {{-- شارة الترتيب العلوية --}}
                            <div class="lb-badge-pill {{ $badgeClass }}">
                                <span>{{ $medal }}</span>
                                <span>{{ $rankText }}</span>
                            </div>

                            {{-- الصورة الرمزية --}}
                            <div class="lb-avatar {{ $avatarClass }}">
                                <span>{{ $initials }}</span>
                                <span class="lb-avatar-icon">{{ $medal }}</span>
                            </div>

                            {{-- معلومات الطالب --}}
                            <div class="lb-student-name">
                                {{ $item['student_name'] }}
                            </div>
                            <div class="lb-student-meta">
                                <span>{{ $item['stage_name'] }}</span>
                                <span>•</span>
                                <span style="color: #d97706;">{{ $item['group_name'] }}</span>
                            </div>

                            {{-- مؤشرات الأداء --}}
                            <div class="lb-metrics-box">
                                <div class="lb-metric-item">
                                    <span class="lb-metric-title">متوسط الامتحانات</span>
                                    <span class="lb-metric-val lb-val-exam">{{ $item['exam_average'] }}%</span>
                                    <div class="lb-bar-bg">
                                        <div class="lb-bar-fill-exam" style="width: {{ min(100, $item['exam_average']) }}%"></div>
                                    </div>
                                </div>
                                <div class="lb-metric-item">
                                    <span class="lb-metric-title">نسبة الحضور</span>
                                    <span class="lb-metric-val lb-val-att">{{ $item['attendance_rate'] }}%</span>
                                    <div class="lb-bar-bg">
                                        <div class="lb-bar-fill-att" style="width: {{ min(100, $item['attendance_rate']) }}%"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- المجموع والزر --}}
                            <div>
                                <div class="lb-total-score-row">
                                    <span>مجموع النقاط المحسوبة:</span>
                                    <span class="lb-score-tag">{{ $item['total_points'] }}</span>
                                </div>

                                <a href="{{ route('student.certificate.print', ['record' => $item['student_id'], 'badge' => $item['badge']]) }}" 
                                    target="_blank" 
                                    class="lb-btn-cert">
                                    <x-heroicon-o-academic-cap style="width: 1.1rem; height: 1.1rem;" />
                                    <span>طباعة شهادة التقدير</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ─── 3. جدول ترتيب الأوائل والمتفوقين ─── --}}
        <div class="lb-table-card">
            <div class="lb-table-header">
                <div class="lb-table-title">
                    <x-heroicon-o-trophy style="width: 1.3rem; height: 1.3rem; color: #f59e0b;" />
                    <span>جدول الترتيب العام لأوائل الطلاب</span>
                </div>
                <div class="lb-info-pill" style="font-size: 0.72rem; padding: 0.35rem 0.75rem;">
                    إجمالي الطلاب المعروضين: {{ $topStudents->count() }}
                </div>
            </div>

            <div class="lb-table-responsive">
                <table class="lb-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 60px;">الترتيب</th>
                            <th>اسم الطالب</th>
                            <th>المرحلة الدراسية</th>
                            <th>المجموعة</th>
                            <th style="text-align: center;">متوسط الامتحانات</th>
                            <th style="text-align: center;">نسبة الحضور</th>
                            <th style="text-align: center;">مجموع النقاط</th>
                            <th style="text-align: left;">التكريم والشهادات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topStudents as $st)
                            @php
                                $rank = $st['rank'];
                                $rankClass = match($rank) {
                                    1 => 'lb-rank-1',
                                    2 => 'lb-rank-2',
                                    3 => 'lb-rank-3',
                                    default => 'lb-rank-other',
                                };
                            @endphp
                            <tr>
                                <td style="text-align: center;">
                                    <span class="lb-rank-badge {{ $rankClass }}">
                                        {{ $rank }}
                                    </span>
                                </td>
                                <td style="font-weight: 800; font-size: 0.88rem;">
                                    {{ $st['student_name'] }}
                                </td>
                                <td>
                                    {{ $st['stage_name'] }}
                                </td>
                                <td>
                                    <span class="lb-tag-pill">
                                        {{ $st['group_name'] }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-family: monospace; font-weight: 800; font-size: 0.88rem; color: #059669;">
                                    {{ $st['exam_average'] }}%
                                </td>
                                <td style="text-align: center; font-family: monospace; font-weight: 800; font-size: 0.88rem; color: #2563eb;">
                                    {{ $st['attendance_rate'] }}%
                                </td>
                                <td style="text-align: center;">
                                    <span class="lb-tag-pill" style="color: #d97706; font-family: monospace; font-weight: 800; font-size: 0.85rem; background: rgba(245, 158, 11, 0.1);">
                                        {{ $st['total_points'] }}
                                    </span>
                                </td>
                                <td style="text-align: left;">
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
                                        <a href="{{ route('student.certificate.print', ['record' => $st['student_id'], 'badge' => $st['badge']]) }}" 
                                            target="_blank" 
                                            class="lb-btn-small lb-btn-cert-small">
                                            <x-heroicon-o-academic-cap style="width: 0.95rem; height: 0.95rem;" />
                                            <span>شهادة التقدير</span>
                                        </a>
                                        <a href="{{ route('student.monthly-report.pdf', ['record' => $st['student_id']]) }}" 
                                            target="_blank" 
                                            class="lb-btn-small lb-btn-rep-small">
                                            <x-heroicon-o-document-chart-bar style="width: 0.95rem; height: 0.95rem;" />
                                            <span>التقرير الشهري</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2.5rem; color: #9ca3af;">
                                    <x-heroicon-o-user-group style="width: 2.5rem; height: 2.5rem; margin: 0 auto 0.5rem; opacity: 0.5;" />
                                    <p style="font-weight: 700;">لا توجد بيانات كافية لعرض لوحة الشرف وفق الفلاتر المحددة حالياً.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>