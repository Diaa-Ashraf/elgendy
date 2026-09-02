<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuperAdminStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Subscription::where('status', 'active')->count();
        $trialTenants = Subscription::where('status', 'trial')->count();
        $totalRevenue = SubscriptionPayment::where('status', 'approved')->sum('amount');

        return [
            Stat::make('إجمالي المدرسين والمراكز', $totalTenants)
                ->description('المسجلين في المنصة')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('الاشتراكات النشطة', $activeTenants)
                ->description('اشتراكات مدفوعة حالياً')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('فترات تجريبية مجانية', $trialTenants)
                ->description('مدرسين جدد في الـ 7 أيام المجانية')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('إجمالي إيرادات المنصة', number_format($totalRevenue, 0) . ' ج.م')
                ->description('إجمالي الإيصالات المعتمدة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
