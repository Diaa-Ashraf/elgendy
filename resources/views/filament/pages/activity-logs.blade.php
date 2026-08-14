<x-filament-panels::page>
    @php
        $logs = $this->getLogs();
    @endphp

    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
        <h3 class="font-bold text-base text-gray-900 dark:text-white mb-4 flex items-center justify-between">
            <span class="flex items-center gap-2">
                <x-heroicon-o-finger-print class="w-5 h-5 text-purple-500" />
                سجل تتبع التعديلات والعمليات آلياً
            </span>
            <span class="text-xs text-gray-400 font-normal">تحديث لحظي</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-gray-50 dark:bg-gray-800 font-bold text-gray-500 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">المستخدم / الفاعل</th>
                        <th class="p-3">نوع العملية</th>
                        <th class="p-3">الوصف</th>
                        <th class="p-3">تاريخ ووقت الحركة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="p-3 text-gray-400">#{{ $log->id }}</td>
                            <td class="p-3 font-bold text-gray-900 dark:text-white">
                                {{ $log->causer?->name ?? ($log->causer?->email ?? 'النظام / تلقائي') }}
                            </td>
                            <td class="p-3">
                                @if($log->event === 'created')
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full font-bold">إضافة ➕</span>
                                @elseif($log->event === 'updated')
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full font-bold">تعديل ✏️</span>
                                @elseif($log->event === 'deleted')
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 rounded-full font-bold">حذف 🗑️</span>
                                @else
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-bold">{{ $log->event ?? 'عملية' }}</span>
                                @endif
                            </td>
                            <td class="p-3 text-gray-700 dark:text-gray-300">
                                {{ $log->description }}
                            </td>
                            <td class="p-3 text-gray-500 font-mono">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d h:i:s A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-400">لا توجد سجلات نشاط مسجلة حالياً</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</x-filament-panels::page>
