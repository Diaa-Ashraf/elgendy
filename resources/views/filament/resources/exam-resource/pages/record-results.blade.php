<x-filament-panels::page>

    {{-- إحصائيات سريعة عن الامتحان --}}
    @php
        $totalStudents = \App\Models\Student::where('stage_id', $this->exam->stage_id)->count();
        $recorded = \App\Models\ExamResult::where('exam_id', $this->exam->id)->count();
        $remaining = $totalStudents - $recorded;
        $avg = \App\Models\ExamResult::where('exam_id', $this->exam->id)->avg('marks_obtained');
        $passCount = \App\Models\ExamResult::where('exam_id', $this->exam->id)
            ->where('marks_obtained', '>=', $this->exam->total_marks / 2)
            ->count();
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 text-center">
            <div class="text-xs font-bold text-gray-500 mb-1">إجمالي الطلاب</div>
            <div class="text-2xl font-extrabold text-blue-600">{{ $totalStudents }}</div>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 text-center">
            <div class="text-xs font-bold text-gray-500 mb-1">تم رصدهم</div>
            <div class="text-2xl font-extrabold text-emerald-600">{{ $recorded }}</div>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 text-center">
            <div class="text-xs font-bold text-gray-500 mb-1">لم يُرصد بعد</div>
            <div class="text-2xl font-extrabold text-rose-600">{{ $remaining }}</div>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 text-center">
            <div class="text-xs font-bold text-gray-500 mb-1">متوسط الدرجات</div>
            <div class="text-2xl font-extrabold text-purple-600">{{ $avg ? number_format($avg, 1) : '—' }}</div>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 text-center">
            <div class="text-xs font-bold text-gray-500 mb-1">الناجحون</div>
            <div class="text-2xl font-extrabold text-teal-600">{{ $passCount }}</div>
        </div>
    </div>

    {{-- جدول رصد الدرجات --}}
    {{ $this->table }}

</x-filament-panels::page>
