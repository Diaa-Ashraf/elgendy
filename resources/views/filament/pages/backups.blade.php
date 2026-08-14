<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">سجل ملفات النسخ الاحتياطي المحفوظة</h3>

            @if(count($backups) === 0)
                <div class="py-8 text-center text-gray-500">
                    لا يوجد ملفات نسخ احتياطي حالية. اضغط على "إنشاء نسخة احتياطية الآن" لبدء نسخة جديدة.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">#</th>
                                <th scope="col" class="px-6 py-3">اسم الملف</th>
                                <th scope="col" class="px-6 py-3">الحجم</th>
                                <th scope="col" class="px-6 py-3">تاريخ الإنشاء</th>
                                <th scope="col" class="px-6 py-3 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $index => $b)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50">
                                    <td class="px-6 py-4 font-bold">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 font-mono dir-ltr text-right">{{ $b['filename'] }}</td>
                                    <td class="px-6 py-4">{{ $b['size'] }}</td>
                                    <td class="px-6 py-4 dir-ltr text-right">{{ $b['created_at'] }}</td>
                                    <td class="px-6 py-4 text-center space-x-2 space-x-reverse">
                                        <button wire:click="downloadBackup('{{ $b['filename'] }}')" type="button" class="px-3 py-1 bg-primary-600 hover:bg-primary-700 text-white rounded text-xs font-semibold shadow">
                                            تحميل SQL
                                        </button>
                                        <button wire:click="deleteBackup('{{ $b['filename'] }}')" wire:confirm="هل أنت تأكد من حذف هذا الملف النهائي؟" type="button" class="px-3 py-1 bg-danger-600 hover:bg-danger-700 text-white rounded text-xs font-semibold shadow">
                                            حذف
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
