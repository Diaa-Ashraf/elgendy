<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل المدفوعات - {{ $tenant->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Cairo', sans-serif; }
        body { background: var(--bg-dark); color: var(--text-main); min-height: 100vh; padding: 2rem 1rem; }
        .container { max-width: 850px; margin: 0 auto; }
        .card { background: var(--card-bg); border-radius: 1.25rem; border: 1px solid var(--border-color); padding: 2rem; }
        .header-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; }
        .table-responsive { width: 100%; overflow-x: auto; margin-top: 1.5rem; }
        table { width: 100%; border-collapse: collapse; text-align: right; }
        th, td { padding: 0.85rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; }
        th { color: var(--text-muted); font-weight: 600; }
        .btn { display: inline-block; background: var(--primary); color: white; padding: 0.6rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 class="header-title">سجل مدفوعات اشتراكات المنصة</h1>
                    <p style="color: var(--text-muted);">كافة التحويلات والإيصالات السابقة وحالتها</p>
                </div>
                <a href="{{ route('tenant.subscription.status', ['tenant' => $tenant->slug]) }}" class="btn">العودة لحالة الاشتراك</a>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>رقم العملية</th>
                            <th>المبلغ</th>
                            <th>الطريقة</th>
                            <th>الرقم المحول منه</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>#{{ $payment->id }}</td>
                                <td style="font-weight: 700; color: #34d399;">{{ number_format($payment->amount, 0) }} ج.م</td>
                                <td>{{ $payment->payment_method === 'instapay' ? 'InstaPay' : 'Vodafone Cash' }}</td>
                                <td>{{ $payment->sender_phone ?? '-' }}</td>
                                <td>
                                    @if($payment->status === 'approved')
                                        <span style="color: #34d399; font-weight: 700;">معتمد</span>
                                    @elseif($payment->status === 'pending')
                                        <span style="color: #fbbf24; font-weight: 700;">قيد المراجعة</span>
                                    @else
                                        <span style="color: #f87171; font-weight: 700;">مرفوض</span>
                                    @endif
                                </td>
                                <td style="color: var(--text-muted);">{{ $payment->created_at->format('Y-m-d h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">لا توجد أي إيصالات سابقة مسجلة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</body>
</html>
