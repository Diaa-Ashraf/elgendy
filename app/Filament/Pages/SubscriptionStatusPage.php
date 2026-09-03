<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;

class SubscriptionStatusPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'اشتراك المنصة والباقات 💳';

    protected static ?string $title = 'إدارة اشتراك المنصة السحابية';

    protected static ?string $navigationGroup = 'إدارة النظام والسلطات';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.subscription-status';

    public static function getNavigationUrl(): string
    {
        $tenant = Filament::getTenant();
        if ($tenant) {
            return route('tenant.subscription.status', ['tenant' => $tenant->slug]);
        }

        return '#';
    }
}
