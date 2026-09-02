<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app(TenantContext::class)->get();

        if (! $tenant) {
            return $next($request);
        }

        $subscription = $tenant->subscription;

        // إذا لم يكن لديه اشتراك مسجل أصلاً أو انتهى اشتراكه وتجربته
        if ($subscription && ! $subscription->isValid()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'انتهت صلاحية اشتراك هذه المنصة التعليمية. يرجى التواصل مع الإدارة للتجديد.',
                ], 403);
            }

            // إذا كان المستخدم في مسار تجديد الاشتراك لا نمنعه
            if ($request->routeIs('tenant.subscription.*')) {
                return $next($request);
            }

            return redirect()->route('tenant.subscription.status', ['tenant' => $tenant->slug])
                ->with('warning', 'انتهت الفترة التجريبية أو الاشتراك. يرجى تجديد الاشتراك للمتابعة.');
        }

        return $next($request);
    }
}
