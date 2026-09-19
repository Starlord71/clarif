<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportUploadController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/reports')->name('home');

Route::get('/lang/{locale}', LocaleController::class)->name('lang');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

Route::get('/reports/create', [ReportUploadController::class, 'create'])->name('reports.create');

Route::post('/reports', [ReportUploadController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('reports.store');

Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');

Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

Route::get('/about', AboutController::class)->name('about');
