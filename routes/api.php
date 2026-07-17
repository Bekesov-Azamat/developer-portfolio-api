<?php

use App\Http\Controllers\Api\ContactSubmissionController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MetricsController;
use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return response()->json([
        'success' => true,
        'data' => [
            'service' => config('app.name'),
            'version' => config('app.version'),
            'status' => 'operational',
        ],
    ]);
})->name('api.root');

Route::get('/health', HealthController::class)
    ->name('api.health');

Route::get('/metrics', MetricsController::class)
    ->name('api.metrics');

Route::post('/contact', ContactSubmissionController::class)
    ->middleware('throttle:contact-submissions')
    ->name('contact.store');
