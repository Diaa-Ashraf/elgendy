<div class="space-y-4 text-right">
    <div class="bg-rose-50 dark:bg-rose-950/40 p-3 rounded-xl border border-rose-200 dark:border-rose-900 text-xs text-rose-700 dark:text-rose-300 font-bold flex items-center justify-between">
        <span>إجمالي الغائبين في هذه الحصة:</span>
        <span class="text-base font-extrabold">{{ $absentees->count() }} طالب</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-right text-xs">
            <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-bold border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="p-2.5">اسم الطالب</th>
                    <th class="p-2.5">هاتف ولي الأمر</th>
                    <th class="p-2.5">تواصل سريع</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-semibold">
                @forelse($absentees as $att)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="p-2.5 font-bold text-gray-900 dark:text-white">
                            {{ $att->student?->name }}
                        </td>
                        <td class="p-2.5 font-mono dir-ltr text-gray-600 dark:text-gray-300">
                            {{ $att->student?->parent_phone ?? '—' }}
                        </td>
                        <td class="p-2.5">
                            @if($att->student?->parent_phone)
                                @php
                                    $phone = preg_replace('/[^0-9]/', '', $att->student->parent_phone);
                                    if (str_starts_with($phone, '01')) { $phone = '2' . $phone; }
                                    $msg = rawurlencode("تنبيه من السنتر: نحيطكم علماً بغياب الطالب/ة {$att->student->name} عن حصة اليوم ({$session->group?->name}).");
                                @endphp
                                <a href="https://wa.me/{{ $phone }}?text={{ $msg }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold transition">
                                    💬 واتساب
                                </a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-4 text-center text-gray-400">
                            🎉 لا يوجد طلاب غائبون في هذه الحصة (الجميع حضروا أو لم يسجل بعد)
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
