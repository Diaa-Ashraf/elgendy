<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OnlineExamController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\StudentPdfController;
use App\Http\Controllers\StudentPortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 🌐 1. مسارات منصة الـ SaaS العامة (Platform Marketing & Pricing)
|--------------------------------------------------------------------------
*/
Route::get('/', [PlatformController::class, 'index'])->name('platform.home');
Route::get('/pricing', [PlatformController::class, 'pricing'])->name('platform.pricing');
Route::get('/register', [PlatformController::class, 'showRegister'])->name('platform.register');
Route::post('/register', [PlatformController::class, 'register'])->name('platform.register.submit');

/*
|--------------------------------------------------------------------------
| 🏫 2. مسارات المؤسسات التعليمية والمدرسين (Tenant Scope: /t/{tenant})
|--------------------------------------------------------------------------
*/
Route::prefix('t/{tenant}')
    ->middleware(['tenant.resolve'])
    ->group(function () {

        // 📱 أ. بوابة الطالب (Student Portal)
        Route::prefix('student')->name('tenant.student.')->group(function () {
            Route::get('/login', [StudentPortalController::class, 'showLogin'])->name('login');
            Route::post('/login', [StudentPortalController::class, 'login'])->name('login.submit');
            Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
            Route::get('/logout', [StudentPortalController::class, 'logout'])->name('logout');

            // اختبارات الطالب أونلاين
            Route::get('/exams/{id}', [OnlineExamController::class, 'show'])->name('exams.show');
            Route::get('/exams/{id}/start', [OnlineExamController::class, 'start'])->name('exams.start');
            Route::post('/exams/{id}/submit', [OnlineExamController::class, 'submit'])->name('exams.submit');
            Route::get('/exams/{id}/result', [OnlineExamController::class, 'result'])->name('exams.result');
        });

        // 👨‍👩‍👦 ب. بوابة ولي الأمر (Parent Portal)
        Route::prefix('parent')->name('tenant.parent.')->group(function () {
            Route::get('/login', [ParentPortalController::class, 'showLogin'])->name('login');
            Route::post('/login', [ParentPortalController::class, 'login'])->name('login.submit');
            Route::get('/dashboard', [ParentPortalController::class, 'dashboard'])->name('dashboard');
            Route::post('/payment', [ParentPortalController::class, 'submitPayment'])->name('payment.submit');
            Route::get('/logout', [ParentPortalController::class, 'logout'])->name('logout');
        });

        // 💳 د. إدارة اشتراك المدرس وسداد الرسوم (Teacher Subscription)
        Route::prefix('subscription')->name('tenant.subscription.')->group(function () {
            Route::get('/status', [\App\Http\Controllers\SubscriptionPaymentController::class, 'status'])->name('status');
            Route::get('/pay', [\App\Http\Controllers\SubscriptionPaymentController::class, 'showPay'])->name('pay');
            Route::post('/pay', [\App\Http\Controllers\SubscriptionPaymentController::class, 'submitPay'])->name('pay.submit');
            Route::get('/history', [\App\Http\Controllers\SubscriptionPaymentController::class, 'history'])->name('history');
        });

        // 🌐 هـ. الموقع التعريفي للمدرس وحجز الطلاب (Teacher Landing & Enrollment)
        Route::get('/', [HomeController::class, 'index'])->name('tenant.home');
        Route::post('/enroll', [HomeController::class, 'submitEnrollment'])->name('tenant.enroll.submit');
        Route::get('/api/stages/{stage}/groups', [HomeController::class, 'getGroupsByStage'])->name('tenant.stage.groups');
    });

/*
|--------------------------------------------------------------------------
| 📄 3. المستندات والطباعة (Authenticated Admin Utilities)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/students/{record}/ledger/pdf', [StudentPdfController::class, 'printLedger'])->name('student.ledger.pdf');
    Route::get('/admin/students/{record}/card/print', [StudentPdfController::class, 'printCard'])->name('student.card.print');
    Route::get('/admin/exams/{record}/pdf', [StudentPdfController::class, 'printExam'])->name('exam.pdf.print');
});

/*
|--------------------------------------------------------------------------
| 🖼️ 4. مسار قراءة ملفات الميديا والصور احتياطياً لاستضافات cPanel
|--------------------------------------------------------------------------
*/
Route::get('/storage/{path}', function ($path) {
    $path1 = storage_path('app/public/' . $path);
    $path2 = storage_path('public/' . $path);
    
    $fullPath = file_exists($path1) ? $path1 : (file_exists($path2) ? $path2 : null);

    if (! $fullPath) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*')->name('storage.local');
