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
    Route::get('/logout', [ParentPortalController::class, 'logout'])->name('parent.logout');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/students/{record}/ledger/pdf', [StudentPdfController::class, 'printLedger'])->name('student.ledger.pdf');
    Route::get('/admin/students/{record}/card/print', [StudentPdfController::class, 'printCard'])->name('student.card.print');
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
