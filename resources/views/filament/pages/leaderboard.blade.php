<x-filament-panels::page>
    <div class="space-y-6">
        <!-- شريط الفلاتر والتحكم بتصميم متناسق مع الثيم المظلم -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 shadow-xl">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div>
                    <label class="block text-xs font-bold text-gray-300 mb-1.5">تصفية حسب المرحلة الدراسية</label>
                    <select wire:model.live="selectedStage" style="background-color: #ffffff !important; color: #000000 !important; font-weight: 800 !important;" class="w-full rounded-xl px-3.5 py-2.5 text-xs border border-gray-300 focus:ring-2 focus:ring-amber-500 shadow-sm cursor-pointer">
                        <option value="" style="background-color: #ffffff; color: #000000;"> جميع المراحل الدراسية</option>
                        @foreach($this->stages as $stg)
                            <option value="{{ $stg->id }}" style="background-color: #ffffff; color: #000000;">{{ $stg->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-300 mb-1.5">تصفية حسب المجموعة</label>
                    <select wire:model.live="selectedGroup" style="background-color: #ffffff !important; color: #000000 !important; font-weight: 800 !important;" class="w-full rounded-xl px-3.5 py-2.5 text-xs border border-gray-300 focus:ring-2 focus:ring-amber-500 shadow-sm cursor-pointer">
                        <option value="" style="background-color: #ffffff; color: #000000;">👥 جميع المجموعات</option>
                        @foreach($this->groups as $grp)
                            <option value="{{ $grp->id }}" style="background-color: #ffffff; color: #000000;">{{ $grp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5">
                    <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-black shadow-inner">
                        <span> الترتيب الذكي: الامتحانات (75%) + الحضور (25%)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- كروت المراكز الثلاثة الأولى (Top 3 Podium) بتصميم فخم وإضاءة ملكية -->
        @php
            $top3 = $this->topStudents->take(3);
            $rest = $this->topStudents->skip(3);
        @endphp

        @if($top3->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                @foreach($top3 as $item)
                    @php
                        $isFirst = $item['rank'] === 1;
                        $isSecond = $item['rank'] === 2;
                        $isThird = $item['rank'] === 3;
                        $cardBg = $isFirst 
                            ? 'bg-gradient-to-b from-amber-950/40 via-gray-900 to-gray-900 border-amber-500/60 ring-2 ring-amber-500/30' 
                            : ($isSecond 
                                ? 'bg-gradient-to-b from-slate-800/40 via-gray-900 to-gray-900 border-slate-600' 
                                : 'bg-gradient-to-b from-amber-950/20 via-gray-900 to-gray-900 border-amber-800/60');
                        $badgeBg = $isFirst ? 'bg-amber-500 text-gray-950 font-black' : ($isSecond ? 'bg-slate-300 text-gray-950 font-black' : 'bg-amber-700 text-white font-black');
                    @endphp
                    <div class="relative {{ $cardBg }} border rounded-2xl p-6 text-center shadow-xl transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl">
                        <!-- شارة المركز -->
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 {{ $badgeBg }} text-xs px-4 py-1.5 rounded-full shadow-lg">
                            {{ $item['badge'] }}
                        </div>

                        <div class="w-16 h-16 rounded-2xl mx-auto mb-3 flex items-center justify-center text-3xl shadow-inner {{ $isFirst ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : ($isSecond ? 'bg-slate-700/30 text-slate-300 border border-slate-600/40' : 'bg-amber-800/20 text-amber-500 border border-amber-700/30') }}">
                            @if($isFirst)  @elseif($isSecond) 🥈 @else 🥉 @endif
                        </div>

                        <h3 class="text-lg font-black text-white mb-1">{{ $item['student']->name }}</h3>
                        <p class="text-xs text-gray-400 mb-4">{{ $item['stage_name'] }} • {{ $item['group_name'] }}</p>

                        <div class="grid grid-cols-2 gap-2 bg-gray-950/60 border border-gray-800 rounded-xl p-3.5 mb-4 text-xs">
                            <div class="text-right">
                                <div class="text-gray-400 text-[11px]">متوسط الامتحانات</div>
                                <div class="font-black text-emerald-400 text-base font-mono mt-0.5">{{ $item['exam_average'] }}%</div>
                            </div>
                            <div class="text-left">
                                <div class="text-gray-400 text-[11px]">نسبة الحضور</div>
                                <div class="font-black text-blue-400 text-base font-mono mt-0.5">{{ $item['attendance_rate'] }}%</div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-2 border-t border-gray-800">
                            <span class="font-bold text-gray-300">مجموع النقاط: <strong class="text-amber-400 text-sm font-mono">{{ $item['total_points'] }}</strong></span>
                            <a href="{{ route('student.certificate.print', ['record' => $item['student']->id, 'badge' => $item['badge']]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-gray-950 font-black rounded-xl text-xs transition-all shadow-md shadow-amber-500/20 hover:scale-105">
                                <span>📜 طباعة شهادة تقدير</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- جدول باقي قائمة المتفوقين -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between bg-gray-850">
                <h4 class="font-black text-sm text-white flex items-center gap-2">
                    <x-heroicon-o-trophy class="w-5 h-5 text-amber-500" />
                    <span>جدول ترتيب المتفوقين والأوائل</span>
                </h4>
                <span class="text-xs px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-full font-bold">
                    إجمالي المعروضين: {{ $this->topStudents->count() }} طالب
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead>
                        <tr class="text-xs font-black text-gray-400 bg-gray-800 border-b border-gray-700">
                            <th class="py-3.5 px-4"># الترتيب</th>
                            <th class="py-3.5 px-4">اسم الطالب</th>
                            <th class="py-3.5 px-4">المرحلة الدراسية</th>
                            <th class="py-3.5 px-4">المجموعة</th>
                            <th class="py-3.5 px-4 text-center text-emerald-400">متوسط الامتحانات</th>
                            <th class="py-3.5 px-4 text-center text-blue-400">نسبة الحضور</th>
                            <th class="py-3.5 px-4 text-center text-amber-400">النقاط الكلية</th>
                            <th class="py-3.5 px-4 text-left">إجراءات وتكريم</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800 font-semibold">
                        @forelse($this->topStudents as $st)
                            <tr class="hover:bg-gray-800/60 transition-colors {{ $st['rank'] <= 3 ? 'font-black' : '' }}">
                                <td class="py-3.5 px-4">
                                    <span style="color: #ffffff !important; font-weight: 900 !important;" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-black shadow-md {{ $st['rank'] === 1 ? 'bg-amber-500 ring-2 ring-amber-400' : ($st['rank'] === 2 ? 'bg-slate-500 ring-2 ring-slate-400' : ($st['rank'] === 3 ? 'bg-amber-800 ring-2 ring-amber-700' : 'bg-gray-800 border border-gray-600')) }}">
                                        {{ $st['rank'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-100 font-black">
                                    {{ $st['student']->name }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-400 text-xs">
                                    {{ $st['stage_name'] }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-400 text-xs">
                                    {{ $st['group_name'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center text-emerald-400 font-mono font-black">
                                    {{ $st['exam_average'] }}%
                                </td>
                                <td class="py-3.5 px-4 text-center text-blue-400 font-mono font-black">
                                    {{ $st['attendance_rate'] }}%
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-block px-3 py-1 bg-amber-950/80 border border-amber-800 text-amber-300 rounded-xl font-mono font-black text-xs">
                                        {{ $st['total_points'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-left">
                                    <a href="{{ route('student.certificate.print', ['record' => $st['student']->id, 'badge' => $st['badge']]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-950/80 hover:bg-emerald-900 border border-emerald-800 text-emerald-300 text-xs font-black rounded-xl transition-all hover:scale-105 shadow-sm">
                                        <span>📜 طباعة شهادة</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    لا يوجد طلاب مسجلين أو بيانات كافية لعرض لوحة الشرف وفق الفلاتر المختارة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
