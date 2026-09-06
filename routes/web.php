<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\StudentPdfController;
use Illuminate\Support\Facades\Route;

// 🌐 الواجهة العامة للموقع التعريفي
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/enroll', [HomeController::class, 'submitEnrollment'])->name('enroll.submit');
Route::get('/api/stages/{stage}/groups', [HomeController::class, 'getGroupsByStage'])->name('stage.groups');

// 📱 بوابة ولي الأمر
Route::prefix('parent')->group(function () {
    Route::get('/login', [ParentPortalController::class, 'showLogin'])->name('parent.login');
    Route::post('/login', [ParentPortalController::class, 'login'])->name('parent.login.submit');
    Route::get('/dashboard', [ParentPortalController::class, 'dashboard'])->name('parent.dashboard');
    Route::post('/payment', [ParentPortalController::class, 'submitPayment'])->name('parent.payment.submit');
    Route::get('/logout', [ParentPortalController::class, 'logout'])->name('parent.logout');

    // 📝 الاختبارات الإلكترونية أونلاين
    Route::get('/exams/{id}', [\App\Http\Controllers\OnlineExamController::class, 'show'])->name('parent.exams.show');
    Route::get('/exams/{id}/start', [\App\Http\Controllers\OnlineExamController::class, 'start'])->name('parent.exams.start');
    Route::post('/exams/{id}/submit', [\App\Http\Controllers\OnlineExamController::class, 'submit'])->name('parent.exams.submit');
    Route::get('/exams/{id}/result', [\App\Http\Controllers\OnlineExamController::class, 'result'])->name('parent.exams.result');

    // 📚 الواجبات والتكليفات المنزلية
    Route::get('/homeworks/{id}', [\App\Http\Controllers\HomeworkPortalController::class, 'show'])->name('parent.homeworks.show');
    Route::post('/homeworks/{id}/submit', [\App\Http\Controllers\HomeworkPortalController::class, 'submit'])->name('parent.homeworks.submit');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/students/{record}/ledger/pdf', [StudentPdfController::class, 'printLedger'])->name('student.ledger.pdf');
    Route::get('/admin/students/{record}/card/print', [StudentPdfController::class, 'printCard'])->name('student.card.print');
    Route::get('/admin/exams/{record}/pdf', [StudentPdfController::class, 'printExam'])->name('exam.pdf.print');
});

// 🖼️ مسار قراءة ملفات الميديا والصور احتياطياً لاستضافات cPanel و Hostinger
Route::get('/storage/{path}', function ($path) {
    $path1 = storage_path('app/public/' . $path);
    $path2 = storage_path('public/' . $path);

    $fullPath = file_exists($path1) ? $path1 : (file_exists($path2) ? $path2 : null);

    if (! $fullPath) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*')->name('storage.local');
