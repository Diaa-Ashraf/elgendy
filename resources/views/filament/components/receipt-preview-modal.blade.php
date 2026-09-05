<div class="space-y-4 p-2 text-right" dir="rtl">
    <div class="grid grid-cols-2 gap-2 text-sm bg-slate-800/60 p-3 rounded-xl border border-slate-700">
        <div><span class="text-slate-400 font-bold">الطالب:</span> <span class="font-extrabold text-white">{{ $record->student?->name }}</span></div>
        <div><span class="text-slate-400 font-bold">المبلغ:</span> <span class="font-extrabold text-emerald-400">{{ number_format($record->amount, 2) }} ج.م</span></div>
        <div><span class="text-slate-400 font-bold">طريقة التحويل:</span> <span class="font-bold text-indigo-400">{{ $record->payment_method === 'instapay' ? 'انستاباي' : 'فودافون كاش' }}</span></div>
        <div><span class="text-slate-400 font-bold">رقم المحول:</span> <span class="font-bold text-white font-mono">{{ $record->sender_phone ?? '-' }}</span></div>
    </div>

    @if($record->receipt_image)
        <div class="border border-slate-700 rounded-2xl overflow-hidden bg-black flex items-center justify-center p-2">
            <img src="{{ asset('storage/' . $record->receipt_image) }}" alt="إيصال التحويل" class="max-h-[500px] w-auto rounded-xl object-contain shadow-2xl">
        </div>
        <div class="text-center">
            <a href="{{ asset('storage/' . $record->receipt_image) }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-indigo-400 hover:text-indigo-300 underline">
                فتح الصورة بالحجم الكامل في تبويب جديد ↗️
            </a>
        </div>
    @else
        <div class="p-4 text-center text-slate-400 bg-slate-900 rounded-xl">
            لا توجد صورة إيصال مرفقة مع هذا الطلب.
        </div>
    @endif
</div>
