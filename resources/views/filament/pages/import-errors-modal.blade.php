<div class="space-y-4 text-right">
    @if(!empty($import->error_message))
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
            <strong>خطأ عام في العملية:</strong>
            <p class="mt-1">{{ $import->error_message }}</p>
        </div>
    @endif

    @if(!empty($import->error_log))
        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-xs text-right text-gray-700 dark:text-gray-200">
                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold">
                    <tr>
                        <th class="p-3">رقم الصف</th>
                        <th class="p-3">اسم الطالب في الملف</th>
                        <th class="p-3">أسباب الرفض / الأخطاء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($import->error_log as $err)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="p-3 font-bold text-primary-600 dark:text-primary-400">
                                الصف {{ $err['row'] ?? '-' }}
                            </td>
                            <td class="p-3 font-semibold">
                                {{ $err['name'] ?? 'غير محدد' }}
                            </td>
                            <td class="p-3 text-red-600 dark:text-red-400">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($err['errors'] ?? [] as $msg)
                                        <li>{{ $msg }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif(empty($import->error_message))
        <p class="text-center text-sm text-gray-500 py-4">
            لا توجد أخطاء مسجلة لهذه العملية 🎉
        </p>
    @endif
</div>
