<x-filament-widgets::widget>
    <style>
        .dw-table-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            transition: all 0.2s ease;
        }
        .dark .dw-table-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.35);
        }

        .dw-table-header {
            padding: 1rem 1.25rem;
            background: #f8fafc;
            border-bottom: 1.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dark .dw-table-header {
            background: #1f2937;
            border-bottom-color: #374151;
        }

        .dw-table-h3 {
            font-size: 0.95rem;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .dark .dw-table-h3 {
            color: #f8fafc;
        }

        .dw-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.85rem;
        }

        .dw-table th {
            padding: 0.9rem 1.1rem;
            background: #f1f5f9;
            color: #334155;
            font-weight: 900;
            font-size: 0.78rem;
            border-bottom: 1.5px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .dw-table th {
            background: #182234;
            color: #cbd5e1;
            border-bottom-color: #374151;
        }

        .dw-table td {
            padding: 0.95rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            font-weight: 800;
            white-space: nowrap;
        }
        .dark .dw-table td {
            border-bottom-color: #1f2937;
            color: #f1f5f9;
        }

        .dw-table tr:hover td {
            background: rgba(241, 245, 249, 0.75);
        }
        .dark .dw-table tr:hover td {
            background: rgba(31, 41, 55, 0.6);
        }

        /* كروت الإشعارات */
        .notif-item {
            padding: 0.9rem 1.1rem;
            border-radius: 1rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            transition: all 0.2s ease;
        }
        .notif-item.is-read {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .dark .notif-item.is-read {
            background: rgba(31, 41, 55, 0.6);
            border-color: #374151;
        }
        .notif-item.is-unread {
            background: rgba(245, 158, 11, 0.08);
            border: 1.5px solid rgba(245, 158, 11, 0.35);
        }
        .dark .notif-item.is-unread {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.4);
        }

        .notif-icon-box {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.75rem;
            background: rgba(245, 158, 11, 0.15);
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .dark .notif-icon-box {
            background: rgba(245, 158, 11, 0.25);
            color: #fbbf24;
        }

        .notif-title {
            font-weight: 900;
            font-size: 0.9rem;
            margin: 0 0 0.25rem 0;
            color: #0f172a;
            line-height: 1.3;
        }
        .dark .notif-title {
            color: #ffffff;
        }

        .notif-body {
            font-size: 0.8rem;
            color: #334155;
            margin: 0;
            font-weight: 700;
            line-height: 1.4;
        }
        .dark .notif-body {
            color: #cbd5e1;
        }

        .notif-time {
            font-size: 0.72rem;
            font-weight: 800;
            color: #64748b;
            white-space: nowrap;
        }
        .dark .notif-time {
            color: #94a3b8;
        }

        /* كروت القائمة الجانبية (الطلبات والطلاب) */
        .side-item-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1rem;
            border-radius: 1rem;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        .dark .side-item-card {
            background: rgba(31, 41, 55, 0.7);
            border-color: #374151;
        }
        .side-item-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }
        .dark .side-item-card:hover {
            border-color: #4b5563;
        }

        .side-item-card.danger {
            background: rgba(239, 68, 68, 0.05);
            border: 1.5px solid rgba(239, 68, 68, 0.25);
        }
        .dark .side-item-card.danger {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.35);
        }

        .side-item-title {
            font-weight: 900;
            font-size: 0.88rem;
            margin: 0;
            color: #0f172a;
        }
        .dark .side-item-title {
            color: #ffffff;
        }

        .side-item-subtitle {
            font-size: 0.75rem;
            color: #475569;
            margin: 0.2rem 0 0 0;
            font-weight: 700;
        }
        .dark .side-item-subtitle {
            color: #94a3b8;
        }

        /* أزرار العمليات المضيئة والواضحة */
        .btn-action-blue {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.4rem 0.85rem;
            border-radius: 0.65rem;
            background: #2563eb;
            color: #ffffff !important;
            font-size: 0.75rem;
            font-weight: 900;
            text-decoration: none !important;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
            transition: all 0.15s ease;
        }
        .btn-action-blue:hover {
            background: #1d4ed8;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4);
            transform: translateY(-1px);
        }

        .btn-action-green {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.85rem;
            border-radius: 0.65rem;
            background: #059669;
            color: #ffffff !important;
            font-size: 0.75rem;
            font-weight: 900;
            text-decoration: none !important;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.3);
            transition: all 0.15s ease;
        }
        .btn-action-green:hover {
            background: #047857;
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.4);
            transform: translateY(-1px);
        }
    </style>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- العمود الأيمن: جدول حصص اليوم + الإشعارات --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- جدول حصص اليوم --}}
            <div class="dw-table-card">
                <div class="dw-table-header">
                    <h3 class="dw-table-h3">
                        <span style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.65rem; background: rgba(245, 158, 11, 0.15); color: #d97706;">
                            <x-heroicon-o-calendar style="width: 1.25rem; height: 1.25rem;" />
                        </span>
                        <span>جدول حصص اليوم — {{ now()->translatedFormat('l d F Y') }}</span>
                    </h3>
                    <a href="{{ url('/admin/group-sessions') }}" style="font-size: 0.78rem; font-weight: 900; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <span>عرض الجدول الكامل</span>
                        <span>←</span>
                    </a>
                </div>

                <div style="width: 100%; overflow-x: auto;">
                    <table class="dw-table">
                        <thead>
                            <tr>
                                <th>الوقت</th>
                                <th>المجموعة</th>
                                <th>المادة الدراسية</th>
                                <th style="text-align: center;">عدد الطلاب</th>
                                <th>القاعة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todaySchedules as $schedule)
                                <tr>
                                    <td>
                                        <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.65rem; border-radius: 0.5rem; background: rgba(245, 158, 11, 0.12); color: #d97706; font-weight: 900; font-family: monospace; font-size: 0.85rem;" class="dark:text-amber-400 dark:bg-amber-400/15">
                                            ⏰ {{ date('h:i A', strtotime($schedule->time)) }}
                                        </span>
                                    </td>
                                    <td style="font-weight: 900; font-size: 0.9rem; color: #0f172a;" class="dark:text-white">
                                        {{ $schedule->group->name ?? 'غير محدد' }}
                                    </td>
                                    <td>
                                        <span style="display: inline-block; padding: 0.25rem 0.65rem; border-radius: 0.5rem; background: rgba(37, 99, 235, 0.12); color: #1d4ed8; font-weight: 900; font-size: 0.75rem; border: 1px solid rgba(37, 99, 235, 0.25);" class="dark:text-blue-300 dark:bg-blue-900/30">
                                            {{ $schedule->group->subject->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 900; font-family: monospace; color: #059669; background: rgba(16, 185, 129, 0.1); padding: 0.2rem 0.55rem; border-radius: 0.5rem;" class="dark:text-emerald-400">
                                            👥 {{ $schedule->group->students->count() ?? 0 }} طالب
                                        </span>
                                    </td>
                                    <td style="color: #475569; font-weight: 800;" class="dark:text-slate-300">
                                        🚪 {{ $schedule->room ?? 'قاعة 1' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2.5rem; color: #64748b; font-weight: 800;">
                                        📅 لا توجد حصص مجدولة اليوم.. نتمنى لكم يوماً سعيداً!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- قسم مركز التنبيهات والإشعارات اللحظية --}}
            <div class="dw-table-card">
                <div class="dw-table-header">
                    <h3 class="dw-table-h3">
                        <span style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.65rem; background: rgba(245, 158, 11, 0.15); color: #d97706;">
                            <x-heroicon-o-bell style="width: 1.25rem; height: 1.25rem;" />
                        </span>
                        <span>مركز التنبيهات والإشعارات اللحظية</span>
                    </h3>
                    <span style="font-size: 0.75rem; font-weight: 900; padding: 0.25rem 0.75rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);" class="dark:text-amber-400 dark:border-amber-400/30">
                        ⚡ تحديث لحظي
                    </span>
                </div>

                <div style="padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    @forelse($recentNotifications as $notif)
                        <div class="notif-item {{ $notif['read_at'] ? 'is-read' : 'is-unread' }}">
                            <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                                <div class="notif-icon-box">
                                    🔔
                                </div>
                                <div>
                                    <h4 class="notif-title">
                                        {!! $notif['title'] !!}
                                    </h4>
                                    <p class="notif-body">
                                        {!! $notif['body'] !!}
                                    </p>
                                </div>
                            </div>
                            <span class="notif-time">
                                {{ $notif['created_at'] }}
                            </span>
                        </div>
                    @empty
                        <div style="text-align: center; color: #64748b; padding: 2rem; font-size: 0.85rem; font-weight: 800;">
                            ✨ لا توجد إشعارات جديدة حالياً.. جميع الأنشطة هادئة ومنتظمة!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- العمود الأيسر: الحصص والطلبات المعلقة --}}
        <div class="space-y-6">
            {{-- طلبات التقديم أونلاين الجديدة --}}
            <div class="dw-table-card">
                <div class="dw-table-header">
                    <h3 class="dw-table-h3">
                        <span style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.65rem; background: rgba(37, 99, 235, 0.15); color: #2563eb;">
                            <x-heroicon-o-document-check style="width: 1.25rem; height: 1.25rem;" />
                        </span>
                        <span>طلبات التقديم أونلاين</span>
                    </h3>
                    <a href="{{ url('/admin/student-applications') }}" style="font-size: 0.78rem; font-weight: 900; color: #2563eb; text-decoration: none;">عرض الكل ←</a>
                </div>

                <div style="padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    @forelse($pendingApplications as $app)
                        <div class="side-item-card">
                            <div>
                                <h4 class="side-item-title">{{ $app->name }}</h4>
                                <p class="side-item-subtitle">
                                    <span style="color: #2563eb; font-weight: 900;" class="dark:text-blue-400">{{ $app->educationalStage?->name ?? '-' }}</span>
                                    <span> • </span>
                                    <span>{{ $app->parent_phone }}</span>
                                </p>
                            </div>
                            <a href="{{ url('/admin/student-applications/' . $app->id . '/edit') }}" class="btn-action-blue">
                                مراجعة
                            </a>
                        </div>
                    @empty
                        <p style="text-align: center; color: #64748b; padding: 1.5rem 0; font-size: 0.82rem; font-weight: 800; margin: 0;">لا توجد طلبات تقديم معلقة حالياً ✅</p>
                    @endforelse
                </div>
            </div>

            {{-- الطلاب المتأخرون في الدفع --}}
            <div class="dw-table-card">
                <div class="dw-table-header">
                    <h3 class="dw-table-h3">
                        <span style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.65rem; background: rgba(239, 68, 68, 0.15); color: #dc2626;">
                            <x-heroicon-o-exclamation-triangle style="width: 1.25rem; height: 1.25rem;" />
                        </span>
                        <span>متأخرات الاشتراكات</span>
                    </h3>
                    <a href="{{ \App\Filament\Pages\LatePaymentsReport::getUrl() }}" style="font-size: 0.78rem; font-weight: 900; color: #dc2626; text-decoration: none;">عرض الكل ←</a>
                </div>

                <div style="padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    @forelse($lateStudents as $student)
                        <div class="side-item-card danger">
                            <div>
                                <h4 class="side-item-title">{{ $student->name }}</h4>
                                <p class="side-item-subtitle">
                                    <span style="color: #dc2626; font-weight: 900;" class="dark:text-rose-400">{{ $student->educationalStage->name ?? '-' }}</span>
                                    <span> • </span>
                                    <span>{{ $student->parent_phone }}</span>
                                </p>
                            </div>
                            <a href="https://wa.me/2{{ $student->parent_phone }}?text={{ urlencode('تذكير بموعد سداد الاشتراك الشهري للطالب: ' . $student->name) }}" 
                               target="_blank" 
                               class="btn-action-green">
                                <span>💬</span>
                                <span>تذكير واتساب</span>
                            </a>
                        </div>
                    @empty
                        <p style="text-align: center; color: #059669; padding: 1.5rem 0; font-size: 0.82rem; font-weight: 800; margin: 0;">جميع الطلاب مسددين الاشتراكات بالكامل 👍</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-filament-widgets::widget>
