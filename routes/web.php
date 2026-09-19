<?php

use App\Http\Controllers\ReportUploadController;
use App\Models\Report;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/reports', [ReportUploadController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('reports.store');

// Placeholder for the report detail view. Phase 3 replaces this with the real
// Blade view; for now it only exposes the ingestion state as JSON.
Route::get('/reports/{report}', function (Report $report) {
    return response()->json([
        'id' => $report->id,
        'status' => $report->status->value,
        'tool_name' => $report->tool_name,
        'total_findings' => $report->meta['total_findings'] ?? null,
        'error_message' => $report->error_message,
    ]);
})->name('reports.show');
