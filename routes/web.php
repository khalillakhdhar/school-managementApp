<?php

use App\Http\Controllers\DocumentPdfController;
use App\Http\Controllers\PasswordChangeController;
use Illuminate\Support\Facades\Route;

// Page d'accueil publique (site vitrine) — indépendante du back-office Filament.
Route::get('/', function () {
    $schoolName = 'EliteCampus';
    try {
        $schoolName = \App\Models\SchoolSetting::get('school_name', 'EliteCampus') ?: 'EliteCampus';
    } catch (\Throwable) {
        // base indisponible : on garde le nom par défaut
    }

    return view('landing', ['appName' => 'EliteCampus', 'schoolName' => $schoolName]);
})->name('home');

// First-login / forced password change (any authenticated user)
Route::middleware('auth')->group(function () {
    Route::get('/account/password', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/account/password', [PasswordChangeController::class, 'update'])->name('password.change.update');

    // Exports PDF (contrôle d'accès par rôle dans le contrôleur)
    Route::get('/pdf/bulletin/{student}/{term}', [DocumentPdfController::class, 'bulletin'])->name('pdf.bulletin');
    Route::get('/pdf/payslip/{payroll}', [DocumentPdfController::class, 'payslip'])->name('pdf.payslip');
});
