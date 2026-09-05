<x-filament-widgets::widget>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- العمود الأيمن: جدول حصص اليوم (يشغل 2 أجزاء في الشاشات الكبيرة) --}}
        <div class="lg:col-span-2 space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-lg text-gray-900 dark:text-white">
                            <x-heroicon-o-calendar class="w-6 h-6 text-primary-500" />
                            <span>جدول حصص اليوم - {{ now()->translatedFormat('l d F Y') }}</span>
                        </div>
                        <a href="{{ url('/admin/group-sessions') }}" class="text-xs font-semibold text-primary-600 hover:underline">
                            عرض الجدول الكامل ←
                        </a>
                    </div>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right text-gray-700 dark:text-gray-200">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-xs font-bold uppercase text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="p-3">الوقت</th>
                                <th class="p-3">المجموعة</th>
                                <th class="p-3">المادة</th>
                                <th class="p-3">عدد الطلاب</th>
                                <th class="p-3">القاعة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($todaySchedules as $schedule)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                    <td class="p-3 font-bold text-primary-600 dark:text-primary-400">
                                        {{ date('h:i A', strtotime($schedule->time)) }}
                                    </td>
                                    <td class="p-3 font-semibold">
                                        {{ $schedule->group->name ?? 'غير محدد' }}
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $schedule->group->subject->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-bold">
                                        {{ $schedule->group->students->count() ?? 0 }} طالب
                                    </td>
                                    <td class="p-3 text-gray-500">
                                        {{ $schedule->room ?? 'قاعة 1' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-gray-400">
                                        لا توجد حصص مجدولة اليوم 🎉
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- قسم مركز التنبيهات والإشعارات المباشرة بأسلوب فاخر --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-black text-base text-gray-900 dark:text-white">
                            <x-heroicon-o-bell class="w-6 h-6 text-amber-500 animate-bounce" />
                            <span>مركز التنبيهات والإشعارات اللحظية</span>
                        </div>
                        <span class="text-xs px-2.5 py-1 bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 rounded-full font-bold">
                            تحديث تلقائي
                        </span>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    @forelse($recentNotifications as $notif)
                        <div class="p-4 rounded-2xl border transition duration-200 flex items-start justify-between gap-4 {{ $notif['read_at'] ? 'bg-gray-50/70 dark:bg-gray-900/40 border-gray-200 dark:border-gray-800' : 'bg-gradient-to-r from-amber-50/90 to-orange-50/90 dark:from-amber-950/30 dark:to-orange-950/20 border-amber-300 dark:border-amber-800/80 shadow-sm' }}">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-base shadow-sm shrink-0 {{ str_contains($notif['title'], 'أونلاين') ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400' : (str_contains($notif['title'], 'تحصيل') ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400') }}">
                                    @if(str_contains($notif['title'], 'أونلاين')) 🌐 @elseif(str_contains($notif['title'], 'تحصيل')) 💰 @else 🔔 @endif
                                </div>
                                <div class="space-y-1">
                                    <h4 class="font-extrabold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                        <span>{!! $notif['title'] !!}</span>
                                        @if(!$notif['read_at'])
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                        @endif
                                    </h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 font-semibold leading-relaxed">{!! $notif['body'] !!}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-gray-400 shrink-0 dir-ltr">{{ $notif['created_at'] }}</span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-400 font-bold text-xs">
                            🎉 لا توجد إشعارات جديدة حالياً.. جميع الأنشطة هادئة ومنتظمة!
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        {{-- العمود الأيسر: الطلاب المتأخرون في الدفع وطلبات التقديم أونلاين --}}
        <div class="space-y-6">
            {{-- طلبات التقديم أونلاين الجديدة --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-base text-gray-900 dark:text-white">
                            <x-heroicon-o-document-check class="w-5 h-5 text-amber-500" />
                            <span>طلبات التقديم أونلاين الحديثة 🌐</span>
                        </div>
                        <a href="{{ url('/admin/student-applications') }}" class="text-xs text-primary-600 font-bold hover:underline">عرض الكل</a>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    @forelse($pendingApplications as $app)
                        <div class="flex items-center justify-between p-3.5 bg-amber-50/70 dark:bg-amber-950/30 rounded-2xl border border-amber-200 dark:border-amber-800/80 shadow-sm hover:border-amber-400 transition">
                            <div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">{{ $app->name }}</h4>
                                <p class="text-xs text-gray-500 font-semibold mt-0.5">{{ $app->educationalStage?->name ?? '-' }} | {{ $app->parent_phone }}</p>
                            </div>
                            <a href="{{ url('/admin/student-applications/' . $app->id . '/edit') }}"
                               class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black transition flex items-center gap-1 shadow-md shadow-amber-600/20">
                                مراجعة
                            </a>
                        </div>
                    @empty
                        <p class="text-xs text-center text-gray-400 py-4 font-bold">لا توجد طلبات تقديم معلقة حالياً 🎉</p>
                    @endforelse
                </div>
            </x-filament::section>

            {{-- الطلاب المتأخرون عن الدفع --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-base text-gray-900 dark:text-white">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-rose-500" />
                            <span>الطلاب المتأخرون في الدفع</span>
                        </div>
                        <a href="{{ url('/admin/students') }}" class="text-xs text-primary-600 font-bold hover:underline">عرض الكل</a>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    @forelse($lateStudents as $student)
                        <div class="flex items-center justify-between p-3 bg-rose-50/50 dark:bg-rose-950/20 rounded-xl border border-rose-100 dark:border-rose-900/50">
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ $student->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $student->educationalStage->name ?? '-' }} | {{ $student->parent_phone }}</p>
                            </div>
                            <a href="https://wa.me/2{{ $student->parent_phone }}?text={{ urlencode('تذكير بموعد سداد الاشتراك الشهري للطالب: ' . $student->name) }}" 
                               target="_blank"
                               class="px-3 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-bold transition flex items-center gap-1">
                                💬 تذكير
                            </a>
                        </div>
                    @empty
                        <p class="text-xs text-center text-gray-400 py-4 font-bold">جميع الطلاب مسددين الاشتراكات 👍</p>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

    </div>
</x-filament-widgets::widget>
