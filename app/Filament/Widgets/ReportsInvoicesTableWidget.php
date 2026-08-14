<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ReportsInvoicesTableWidget extends Widget
{
    protected static string $view = 'filament.widgets.reports-invoices-table-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';
}
