<?php
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PayrollController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('students', StudentController::class);
    Route::get('students/{student}/balance', [StudentController::class, 'getBalance']);
    Route::get('students/{student}/payments', [StudentController::class, 'getPayments']);

    Route::apiResource('payments', PaymentController::class)->except(['update', 'store']);
    Route::post('payments/record', [PaymentController::class, 'recordPayment']);

    Route::apiResource('payrolls', PayrollController::class)->only(['index', 'show']);
    Route::post('payroll/calculate', [PayrollController::class, 'calculateMonthly']);
    Route::post('payroll/{payroll}/finalize', [PayrollController::class, 'finalizePayroll']);
});
