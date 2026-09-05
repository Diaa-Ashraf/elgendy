<x-filament-panels::page>
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- ─── 0. اختيار وضع تسجيل الحضور (تلقائي ذكي / يدوي) ─── --}}
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center space-x-3 space-x-reverse">
                <span class="text-xl">📋</span>
                <div>
                    <h4 class="font-bold text-sm text-gray-900 dark:text-white">طريقة ربط الجلسات</h4>
                    <p class="text-xs text-gray-500">اختر بين التعرف التلقائي برقم الطالب أو التحديد اليدوي لجميع الطلاب</p>
                </div>
            </div>
            <div class="flex bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                <button wire:click="$set('mode', 'auto')" class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ $mode === 'auto' ? 'bg-emerald-600 text-white shadow' : 'text-gray-600 dark:text-gray-400' }}">
                    تلقائي (حصة الطالب اليوم)
                </button>
                <button wire:click="$set('mode', 'manual')" class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ $mode === 'manual' ? 'bg-blue-600 text-white shadow' : 'text-gray-600 dark:text-gray-400' }}">
                    📌 يدوي (حصة واحدة محددة)
                </button>
            </div>
        </div>

        {{-- ─── 1. اختيار الجلسة الدراسية (تظهر دائمًا في اليدوي وتظهر اختيارية للمراجعة في التلقائي) ─── --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">
                @if($mode === 'auto')
                    الحصة الدراسية المحددة تلقائياً / أو اختر يدوي للتجربة 🎯
                @else
                    اختر الحصة / الجلسة الدراسية لتسجيل الحضور بها لكافة الطلاب 🎯
                @endif
            </label>
            <select wire:model.live="selected_session_id" class="w-full text-base font-semibold border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-xl p-3 text-gray-800 dark:text-gray-100">
                <option value="">-- {{ $mode === 'auto' ? 'سيتم التعرف على حصة الطالب تلقائياً عند قراءة الباركود' : 'اضغط لاختيار الجلسة الحالية' }} --</option>
                @php
                    $activeSessions = \App\Models\GroupSession::with('group.subject')
                        ->orderBy('date', 'desc')
                        ->take(50)
                        ->get();
                @endphp
                @foreach($activeSessions as $session)
                    <option value="{{ $session->id }}">
                        {{ $session->group?->name }} — {{ $session->group?->subject?->name }} — {{ \Carbon\Carbon::parse($session->date)->format('Y-m-d (h:i A)') }} ({{ $session->title ?? 'حصة عادية' }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ─── 2. كارت قراءة الـ QR والتسجيل السريع ─── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- إدخال / قراءة الـ QR --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm text-center flex flex-col justify-between">
                <div>
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center mx-auto text-3xl mb-4">
                        📷
                    </div>
                    <h3 class="font-extrabold text-lg text-gray-900 dark:text-white mb-1">مسح رمز الطالب</h3>
                    <p class="text-xs text-gray-500 mb-4">وجه باركود الطالب أو اكتب الكود المباشر</p>

                    <form wire:submit="processScan" class="space-y-3">
                        <input type="text"
                               wire:model="scanned_code"
                               placeholder="امسح الـ QR أو اكتب الكود (مثال: STD-1234 أو 1)..."
                               autofocus
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-center font-mono font-bold text-lg focus:outline-none focus:ring-2 focus:ring-blue-500">

                        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm transition shadow-lg shadow-emerald-200 dark:shadow-none">
                            تسجيل حضور الطالب
                        </button>
                    </form>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-[11px] text-gray-400">
                    💡 يمكنك استخدام قارئ الـ Barcode اليدوي أو كاميرا الجوال مباشرة.
                </div>
            </div>

            {{-- قائمة الحاضرين في الجلسة المختارة --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-3 flex items-center justify-between">
                    <span>قائمة الطلاب الحاضرين بالجلسة الحالية</span>
                    <span class="text-xs px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 rounded-full font-bold">
                        @if($selected_session_id)
                            {{ \App\Models\Attendance::where('group_session_id', $selected_session_id)->where('status', 'present')->count() }} حاضر
                        @else
                            0
                        @endif
                    </span>
                </h3>

                <div class="overflow-y-auto max-h-64 divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                    @if($selected_session_id)
                        @php
                            $recentPresent = \App\Models\Attendance::where('group_session_id', $selected_session_id)
                                ->with('student')
                                ->orderBy('id', 'desc')
                                ->get();
                        @endphp
                        @forelse($recentPresent as $att)
                            <div class="py-2.5 flex items-center justify-between">
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $att->student?->name }}</span>
                                <span class="text-emerald-600 font-semibold">حضر ✅ ({{ \Carbon\Carbon::parse($att->updated_at)->format('h:i A') }})</span>
                            </div>
                        @empty
                            <p class="text-center text-gray-400 py-6">لم يتم تسجيل أي حضور في هذه الجلسة بعد</p>
                        @endforelse
                    @else
                        <p class="text-center text-gray-400 py-6">يرجى اختيار الجلسة من فوق لعرض الحاضرين</p>
                    @endif
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
