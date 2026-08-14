<x-filament-panels::page>
    @php
        $timetable = $this->getTimetableData();
        $colors = [
            'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
            'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
            'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800',
        ];
    @endphp

    {{-- ─── 1. تصفية المرحلة + طباعة ─── --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 mb-6">
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 rounded-lg font-bold text-sm hover:bg-blue-100 transition flex items-center gap-2">
                <x-heroicon-o-printer class="w-4 h-4" />
                <span>طباعة الجدول الأسبوعي</span>
            </button>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-gray-500">تصفية بالمرحلة:</span>
            <select wire:model.live="selected_stage_id" class="text-sm font-semibold border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-200">
                <option value="">جميع المراحل الدراسية</option>
                @foreach($timetable['stages'] as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ─── 2. الجدول التفاعلي الشبكي ─── --}}
    <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
        @foreach($timetable['days'] as $dayKey => $dayLabel)
            @php
                $daySchedules = $timetable['schedules']->get($dayKey, collect());
            @endphp
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col">
                <div class="bg-gray-100 dark:bg-gray-800 p-3 text-center border-b border-gray-200 dark:border-gray-700 font-extrabold text-sm text-gray-800 dark:text-gray-100">
                    {{ $dayLabel }}
                </div>

                <div class="p-3 space-y-3 flex-1 min-h-[160px]">
                    @forelse($daySchedules as $idx => $sch)
                        @php
                            $style = $colors[$idx % count($colors)];
                            $formattedTime = date('h:i A', strtotime($sch->time));
                        @endphp
                        <div class="p-3 rounded-xl border text-xs {{ $style }} space-y-1">
                            <div class="font-extrabold text-sm">{{ $sch->group?->name }}</div>
                            <div class="font-semibold">{{ $sch->group?->subject?->name }}</div>
                            <div class="flex items-center justify-between text-[11px] pt-1 border-t border-black/10 dark:border-white/10">
                                <span>⏰ {{ $formattedTime }}</span>
                                <span>🏫 {{ $sch->room ?? 'قاعة 1' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex items-center justify-center text-gray-300 dark:text-gray-600 text-xs py-8 text-center">
                            لا توجد حصص
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
