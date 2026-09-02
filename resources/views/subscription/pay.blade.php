<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سداد اشتراك المنصة - {{ $tenant->name }}</title>
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
            --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Cairo', sans-serif; }
        body { background: var(--bg-dark); color: var(--text-main); min-height: 100vh; padding: 2rem 1rem; }
        .container { max-width: 650px; margin: 0 auto; }
        .card { background: var(--card-bg); border-radius: 1.25rem; border: 1px solid var(--border-color); padding: 2rem; }
        .header-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; }
        .payment-methods { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1.5rem 0; }
        .method-card { background: rgba(15, 23, 42, 0.6); border: 2px solid var(--border-color); border-radius: 1rem; padding: 1rem; text-align: center; cursor: pointer; }
        .method-card.active { border-color: var(--primary); background: rgba(79, 70, 229, 0.1); }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem; color: #e2e8f0; }
        input, select { width: 100%; padding: 0.85rem 1rem; background: #0f172a; border: 1px solid var(--border-color); border-radius: 0.75rem; color: white; font-size: 1rem; }
        input:focus, select:focus { outline: none; border-color: var(--primary); }
        .btn { display: block; width: 100%; background: var(--primary); color: white; padding: 1rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; text-align: center; border: none; cursor: pointer; font-size: 1.1rem; }
        .btn:hover { background: var(--primary-dark); }
        .error-msg { color: var(--danger); font-size: 0.85rem; margin-top: 0.35rem; }
        .notice-box { background: rgba(79, 70, 229, 0.1); border: 1px solid rgba(79, 70, 229, 0.3); padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1 class="header-title">تجديد اشتراك: {{ $tenant->name }}</h1>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">خطة الاشتراك: <strong>{{ $subscription?->plan?->name ?? 'الافتراضية' }}</strong> ({{ number_format($subscription?->plan?->price_monthly ?? 0, 0) }} ج.م / شهر)</p>

            <div class="notice-box">
                <p>📌 <strong>بيانات التحويل الحالية:</strong></p>
                <p>• <strong>InstaPay:</strong> ادفع على المعرف: <code>saas-edu@instapay</code></p>
                <p>• <strong>Vodafone Cash:</strong> أرسل إلى رقم: <code>01000000000</code></p>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">* بعد إتمام التحويل، يرجى ملء البيانات أدناه وإرفاق سكرين شوت الإيصال للاعتماد.</p>
            </div>

            <form action="{{ route('tenant.subscription.pay.submit', ['tenant' => $tenant->slug]) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label>طريقة الدفع المستخدمة</label>
                    <select name="payment_method" required>
                        <option value="instapay" {{ old('payment_method') === 'instapay' ? 'selected' : '' }}>InstaPay (إنستاباي)</option>
                        <option value="vodafone_cash" {{ old('payment_method') === 'vodafone_cash' ? 'selected' : '' }}>Vodafone Cash (فودافون كاش)</option>
                    </select>
                    @error('payment_method') <div class="error-msg">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>المبلغ المحول (ج.م)</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $subscription?->plan?->price_monthly ?? '') }}" required>
                    @error('amount') <div class="error-msg">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>رقم الهاتف أو المعرف الذي تم التحويل منه</label>
                    <input type="text" name="sender_phone" value="{{ old('sender_phone') }}" placeholder="01xxxxxxxxx أو yourname@instapay">
                    @error('sender_phone') <div class="error-msg">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>رقم العملية المرجعي (اختياري)</label>
                    <input type="text" name="transaction_reference" value="{{ old('transaction_reference') }}" placeholder="رقم المعاملة من تطبيق البنك">
                    @error('transaction_reference') <div class="error-msg">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>صورة إيصال التحويل (Screenshot)</label>
                    <input type="file" name="receipt_image" accept="image/*" required>
                    @error('receipt_image') <div class="error-msg">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn">🚀 تأكيد وإرسال الإيصال</button>
            </form>

            <p style="text-align: center; margin-top: 1.5rem;">
                <a href="{{ route('tenant.subscription.status', ['tenant' => $tenant->slug]) }}" style="color: var(--text-muted); text-decoration: none;">إلغاء والعودة لصفحة الاشتراك</a>
            </p>
        </div>
    </div>
</body>
</html>
