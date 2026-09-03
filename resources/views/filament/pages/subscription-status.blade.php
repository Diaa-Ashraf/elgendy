<x-filament-panels::page>
    <div class="text-center py-6">
        <a href="{{ route('tenant.subscription.status', ['tenant' => \Filament\Facades\Filament::getTenant()?->slug]) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 text-white font-bold rounded-lg hover:bg-amber-600 transition">
            الانتقال إلى صفحة اشتراك وسداد المنصة 🚀
        </a>
    </div>
</x-filament-panels::page>
