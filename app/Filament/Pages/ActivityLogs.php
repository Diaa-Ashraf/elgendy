<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogs extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationLabel = 'سجل النشاطات والعمليات';

    protected static ?string $title = 'سجل النشاطات وتتبع عمليات النظام';

    protected static ?string $navigationGroup = 'إدارة النظام والسلطات';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.activity-logs';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') || $user->email === 'admin@admin.com';
    }

    public function getLogs()
    {
        return Activity::with(['causer', 'subject'])
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}
