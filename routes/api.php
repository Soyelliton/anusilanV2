<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\LoanTypeController;
use App\Http\Controllers\BorrowersController;
use App\Http\Controllers\LoansController;
use App\Http\Controllers\PaymentController;


Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');
});
    // Market
    Route::get('/market', [MarketController::class, 'index']);
    Route::post('/market', [MarketController::class, 'addMarket']);
    Route::delete('/market/{id}', [MarketController::class, 'delete']);

    // Loan Type
    Route::get('/loan_type', [LoanTypeController::class, 'index']);
    Route::post('/loan_type', [LoanTypeController::class, 'store']);
    Route::put('/loan_type/{id}', [LoanTypeController::class, 'update']);
    Route::delete('/loan_type/{id}', [LoanTypeController::class, 'destroy']);

    // Borrowers
    Route::get('/borrowers', [BorrowersController::class, 'index']);
    Route::post('/borrowers', [BorrowersController::class, 'store']);
    Route::get('/borrowers/{id}', [BorrowersController::class, 'show']);
    Route::get('/borrowers/{id}/edit', [BorrowersController::class, 'edit']);
    Route::put('/borrowers/{id}', [BorrowersController::class, 'update']);
    Route::delete('/borrowers/attachment/{id}', [BorrowersController::class, 'attachment_delete']);
    
    // Loan
    Route::get('/loans', [LoansController::class, 'index'])->name('loans.index');
    Route::post('/loans/approve', [LoansController::class, 'approve'])->name('loans.approve');
    Route::delete('/loans/delete/{id}', [LoansController::class, 'delete'])->name('loans.delete');
    Route::put('/loans/borrower-dropout/{id}', [LoansController::class, 'borrowerDropOut'])->name('loans.borrowerDropOut');

    // Transation
    Route::get('/transactions', [PaymentController::class, 'transactions']);
    Route::post('/save-payment', [PaymentController::class, 'savePayment']);

