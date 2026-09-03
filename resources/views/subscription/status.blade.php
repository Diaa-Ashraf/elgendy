<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حالة الاشتراك - {{ $tenant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Cairo', sans-serif; }
        body { background: var(--bg-dark); color: var(--text-main); min-height: 100vh; padding: 2rem 1rem; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: var(--card-bg); border-radius: 1.25rem; border: 1px solid var(--border-color); padding: 2rem; margin-bottom: 1.5rem; }
        .header-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; }
        .status-badge { display: inline-block; padding: 0.4rem 1rem; border-radius: 9999px; font-weight: 700; font-size: 0.9rem; }
        .status-active { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .status-trial { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .status-expired { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        .grid-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1.5rem; }
        .info-box { background: rgba(15, 23, 42, 0.6); padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--border-color); }
        .info-label { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem; }
        .info-val { font-size: 1.15rem; font-weight: 700; }
        .btn { display: inline-block; background: var(--primary); color: white; padding: 0.75rem 1.5rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; text-align: center; border: none; cursor: pointer; transition: all 0.2s; }
        .btn:hover { background: var(--primary-dark); }
        .btn-outline { background: transparent; border: 1px solid var(--border-color); color: var(--text-muted); }
        .btn-outline:hover { background: rgba(255, 255, 255, 0.05); color: white; }
        .table-responsive { width: 100%; overflow-x: auto; margin-top: 1rem; }
        table { width: 100%; border-collapse: collapse; text-align: right; }
        th, td { padding: 0.85rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; }
        th { color: var(--text-muted); font-weight: 600; }
        .alert-success { background: rgba(16, 185, 129, 0.2); border: 1px solid var(--success); color: #34d399; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="container">
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 class="header-title">خطة اشتراك: {{ $tenant->name }}</h1>
                    <p style="color: var(--text-muted);">إدارة خطتك الحالية وتجديد الاشتراك الشهري</p>
                </div>
                <div>
                    @if($status === 'active')
                        <span class="status-badge status-active">● نشط ومفعل</span>
                    @elseif($status === 'trial')
                        <span class="status-badge status-trial">● فترة تجريبية مجانية</span>
                    @else
                        <span class="status-badge status-expired">● منتهي أو متوقف</span>
                    @endif
                </div>
            </div>

            <div class="grid-info">
                <div class="info-box">
                    <div class="info-label">الخطة الحالية</div>
                    <div class="info-val">{{ $subscription?->plan?->name ?? 'بدون خطة' }}</div>
                </div>
                <div class="info-box">
                    <div class="info-label">قيمة الاشتراك الشهري</div>
                    <div class="info-val">{{ $subscription?->plan?->price_monthly ? number_format($subscription->plan->price_monthly, 0) . ' ج.م' : 'مجاناً' }}</div>
                </div>
                <div class="info-box">
                    <div class="info-label">تاريخ نهاية الاشتراك</div>
                    <div class="info-val">
                        @if($status === 'trial')
                            {{ $subscription?->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d') : 'غير محدد' }}
                        @else
                            {{ $subscription?->ends_at ? $subscription->ends_at->format('Y-m-d') : 'غير محدد' }}
                        @endif
                    </div>
                </div>
            </div>

            <div style="margin-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="{{ route('tenant.subscription.pay', ['tenant' => $tenant->slug]) }}" class="btn">💳 تجديد الاشتراك / رفع إيصال</a>
                <a href="{{ route('tenant.subscription.history', ['tenant' => $tenant->slug]) }}" class="btn btn-outline">سجل المدفوعات السابقة</a>
                <a href="/admin/{{ $tenant->slug }}" class="btn btn-outline">العودة للوحة التحكم</a>
            </div>
        </div>

        <div class="card">
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">آخر إيصالات الدفع</h2>
            @if($recentPayments->isEmpty())
                <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">لا توجد إيصالات سداد مسجلة بعد.</p>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>المبلغ</th>
                                <th>طريقة الدفع</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayments as $payment)
                                <tr>
                                    <td style="font-weight: 700; color: #34d399;">{{ number_format($payment->amount, 0) }} ج.م</td>
                                    <td>{{ $payment->payment_method === 'instapay' ? 'InstaPay' : 'Vodafone Cash' }}</td>
                                    <td>
                                        @if($payment->status === 'approved')
                                            <span style="color: #34d399; font-weight: 700;">معتمد</span>
                                        @elseif($payment->status === 'pending')
                                            <span style="color: #fbbf24; font-weight: 700;">قيد المراجعة</span>
                                        @else
                                            <span style="color: #f87171; font-weight: 700;">مرفوض</span>
                                        @endif
                                    </td>
                                    <td style="color: var(--text-muted);">{{ $payment->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
