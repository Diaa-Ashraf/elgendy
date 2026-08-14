<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ExecutiveStatsWidget;
use App\Filament\Widgets\QuickShortcutsWidget;
use App\Filament\Widgets\TodayDashboardTablesWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $settingService = app(\App\Services\SettingService::class);
        $logo = $settingService->get('center_logo');
        $favicon = $settingService->get('site_favicon');

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('نظام الأستاذ محمد الغندي التعليمي')
            ->brandLogo($logo ? asset('storage/' . $logo) : null)
            ->favicon($favicon ? asset('storage/' . $favicon) : null)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                QuickShortcutsWidget::class,
                ExecutiveStatsWidget::class,
                TodayDashboardTablesWidget::class,
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['mod+k'])
            ->font('Cairo')
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
