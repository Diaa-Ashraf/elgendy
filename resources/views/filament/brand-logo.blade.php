@php
    $__settingService = app(\App\Services\SettingService::class);
    $__logo = $__settingService->url('center_logo');
    $__centerName = $__settingService->get('center_name', 'المنظومة التعليمية');
@endphp

<div class="flex items-center gap-3 py-1">
    @if($__logo)
        <img src="{{ $__logo }}" alt="{{ $__centerName }}" class="h-9 w-auto max-w-[120px] object-contain drop-shadow-sm transition-transform duration-200 hover:scale-105" />
    @else
        <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-500 font-black text-base shadow-sm">
            🎓
        </div>
    @endif
    <div class="flex flex-col text-right leading-tight">
        <span class="font-black text-sm md:text-base text-slate-900 dark:text-white tracking-tight" style="font-family: 'Cairo', sans-serif;">
            {{ $__centerName }}
        </span>
        <span class="text-[10px] font-extrabold text-amber-600 dark:text-amber-400">
            لوحة الإدارة والتحكم
        </span>
    </div>
</div>
