<x-filament-widgets::widget>
    <style>
        .dw-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        .dark .dw-table-card {
            background: #111827;
            border-color: #374151;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3);
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
            gap: 0.5rem;
        }
        .dark .dw-table-h3 {
            color: #f8fafc;
        }

        .dw-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.82rem;
        }

        .dw-table th {
            padding: 0.85rem 1rem;
            background: #f1f5f9;
            color: #334155;
            font-weight: 800;
            font-size: 0.76rem;
            border-bottom: 1.5px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .dw-table th {
            background: #182234;
            color: #cbd5e1;
            border-bottom-color: #374151;
        }

        .dw-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            white-space: nowrap;
        }
        .dark .dw-table td {
            border-bottom-color: #1f2937;
            color: #e2e8f0;
        }

        .dw-table tr:hover td {
            background: rgba(241, 245, 249, 0.7);
        }
        .dark .dw-table tr:hover td {
            background: rgba(31, 41, 55, 0.6);
        }
    </style>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- العمود الأيمن: جدول حصص اليوم + الإشعارات --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- جدول حصص اليوم --}}
            <div class="dw-table-card">
                <div class="dw-table-header">
                    <h3 class="dw-table-h3">
                        <x-heroicon-o-calendar style="width: 1.35rem; height: 1.35rem; color: #f59e0b;" />
                        <span>جدول حصص اليوم — {{ now()->translatedFormat('l d F Y') }}</span>
                    </h3>
                    <a href="{{ url('/admin/group-sessions') }}" style="font-size: 0.75rem; font-weight: 800; color: #2563eb; text-decoration: none;">
                        عرض الجدول الكامل ←
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
                                    <td style="font-weight: 900; font-family: monospace; color: #d97706;" class="dark:text-amber-400">
                                        {{ date('h:i A', strtotime($schedule->time)) }}
                                    </td>
                                    <td style="font-weight: 900;">
                                        {{ $schedule->group->name ?? 'غير محدد' }}
                                    </td>
                                    <td>
                                        <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 0.5rem; background: rgba(37, 99, 235, 0.1); color: #2563eb; font-weight: 800; font-size: 0.72rem;">
                                            {{ $schedule->group->subject->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td style="text-align: center; font-weight: 900; font-family: monospace;">
                                        {{ $schedule->group->students->count() ?? 0 }} طالب
                                    </td>
                                    <td style="color: #64748b;">
                                        {{ $schedule->room ?? 'قاعة 1' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                        لا توجد حصص مجدولة اليوم
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
                        <x-heroicon-o-bell style="width: 1.35rem; height: 1.35rem; color: #f59e0b;" />
                        <span>مركز التنبيهات والإشعارات اللحظية</span>
                    </h3>
                    <span style="font-size: 0.72rem; font-weight: 800; padding: 0.2rem 0.65rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.15); color: #d97706;">
                        تحديث تلقائي
                    </span>
                </div>

                <div style="padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    @forelse($recentNotifications as $notif)
                        <div style="padding: 0.9rem 1.1rem; border-radius: 1rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; transition: all 0.2s;"
                             class="{{ $notif['read_at'] ? 'bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700' : 'bg-amber-500/10 border border-amber-500/30' }}">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.65rem; background: rgba(245, 158, 11, 0.15); color: #d97706; display: flex; align-items: center; justify-content: center; font-weight: 900; flex-shrink: 0;">
                                    🔔
                                </div>
                                <div>
                                    <h4 style="font-weight: 900; font-size: 0.88rem; margin: 0 0 0.2rem 0; color: #0f172a;" class="dark:text-white">
                                        {!! $notif['title'] !!}
                                    </h4>
                                    <p style="font-size: 0.75rem; color: #475569; margin: 0; font-weight: 600;" class="dark:text-slate-300">
                                        {!! $notif['body'] !!}
                                    </p>
                                </div>
                            </div>
                            <span style="font-size: 0.68rem; font-weight: 800; color: #94a3b8; white-space: nowrap;">
                                {{ $notif['created_at'] }}
                            </span>
                        </div>
                    @empty
                        <div style="text-align: center; color: #94a3b8; padding: 1.5rem; font-size: 0.8rem; font-weight: 700;">
                            لا توجد إشعارات جديدة حالياً.. جميع الأنشطة هادئة ومنتظمة!
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
                        <x-heroicon-o-document-check style="width: 1.35rem; height: 1.35rem; color: #2563eb;" />
                        <span>طلبات التقديم أونلاين</span>
                    </h3>
                    <a href="{{ url('/admin/student-applications') }}" style="font-size: 0.75rem; font-weight: 800; color: #2563eb; text-decoration: none;">عرض الكل</a>
                </div>

                <div style="padding: 0.85rem; display: flex; flex-direction: column; gap: 0.65rem;">
                    @forelse($pendingApplications as $app)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0.9rem; border-radius: 0.85rem; background: #f8fafc; border: 1px solid #e2e8f0;" class="dark:bg-gray-800/80 dark:border-gray-700">
                            <div>
                                <h4 style="font-weight: 900; font-size: 0.85rem; margin: 0; color: #0f172a;" class="dark:text-white">{{ $app->name }}</h4>
                                <p style="font-size: 0.72rem; color: #64748b; margin: 0.15rem 0 0 0; font-weight: 700;">{{ $app->educationalStage?->name ?? '-' }} | {{ $app->parent_phone }}</p>
                            </div>
                            <a href="{{ url('/admin/student-applications/' . $app->id . '/edit') }}"
                               style="display: inline-block; padding: 0.3rem 0.75rem; border-radius: 0.6rem; background: #2563eb; color: #ffffff; font-size: 0.72rem; font-weight: 900; text-decoration: none;">
                                مراجعة
                            </a>
                        </div>
                    @empty
                        <p style="text-align: center; color: #94a3b8; padding: 1rem 0; font-size: 0.75rem; font-weight: 700; margin: 0;">لا توجد طلبات تقديم معلقة حالياً</p>
                    @endforelse
                </div>
            </div>

            {{-- الطلاب المتأخرون في الدفع --}}
            <div class="dw-table-card">
                <div class="dw-table-header">
                    <h3 class="dw-table-h3">
                        <x-heroicon-o-exclamation-triangle style="width: 1.35rem; height: 1.35rem; color: #dc2626;" />
                        <span>الطلاب المتأخرون في الدفع</span>
                    </h3>
                    <a href="{{ url('/admin/students') }}" style="font-size: 0.75rem; font-weight: 800; color: #dc2626; text-decoration: none;">عرض الكل</a>
                </div>

                <div style="padding: 0.85rem; display: flex; flex-direction: column; gap: 0.65rem;">
                    @forelse($lateStudents as $student)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0.9rem; border-radius: 0.85rem; background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2);" class="dark:bg-gray-800/80">
                            <div>
                                <h4 style="font-weight: 900; font-size: 0.85rem; margin: 0; color: #0f172a;" class="dark:text-white">{{ $student->name }}</h4>
                                <p style="font-size: 0.72rem; color: #64748b; margin: 0.15rem 0 0 0; font-weight: 700;">{{ $student->educationalStage->name ?? '-' }} | {{ $student->parent_phone }}</p>
                            </div>
                            <a href="https://wa.me/2{{ $student->parent_phone }}?text={{ urlencode('تذكير بموعد سداد الاشتراك الشهري للطالب: ' . $student->name) }}" 
                               target="_blank" 
                               style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.3rem 0.75rem; border-radius: 0.6rem; background: #059669; color: #ffffff; font-size: 0.72rem; font-weight: 900; text-decoration: none;">
                                تذكير
                            </a>
                        </div>
                    @empty
                        <p style="text-align: center; color: #059669; padding: 1rem 0; font-size: 0.75rem; font-weight: 700; margin: 0;">جميع الطلاب مسددين الاشتراكات 👍</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-filament-widgets::widget>
