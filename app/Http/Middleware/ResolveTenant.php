<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('tenant');

        if (! $slug) {
            abort(404, 'المؤسسة التعليمية غير محددة.');
        }

        // إذا كان باراميتر tenant هو بالفعل Instance من الموديل (Route Model Binding) أو نص slug
        $tenant = $slug instanceof Tenant
            ? $slug
            : Cache::remember("tenant:slug:{$slug}", 3600, function () use ($slug) {
                return Tenant::where('slug', $slug)->first();
            });

        if (! $tenant || ! $tenant->is_active) {
            abort(404, 'هذه المنصة التعليمية غير متوفرة أو تم تعطيلها مؤقتاً.');
        }

        // تسجيل الـ Tenant في الـ TenantContext Singleton ليكون متاحاً لجميع أجزاء النظام
        app(TenantContext::class)->set($tenant);

        // مشاركة الـ Tenant مع جميع ملفات الـ Views تلقائياً
        view()->share('currentTenant', $tenant);

        return $next($request);
    }
}
