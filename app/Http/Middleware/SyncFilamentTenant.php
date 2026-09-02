<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncFilamentTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($tenant = Filament::getTenant()) {
            app(TenantContext::class)->set($tenant);
        }

        return $next($request);
    }
}
