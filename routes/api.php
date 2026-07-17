<?php

use App\Http\Controllers\Api\ContactSubmissionController;
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

Route::get('/health', static function () {
    return response()->json([
        'success' => true,
        'status' => 'healthy',
        'version' => config('app.version'),
        'checks' => [
            'application' => [
                'ok' => true,
            ],
        ],
    ]);
})->name('api.health');

Route::post('/contact', ContactSubmissionController::class)
    ->middleware('throttle:contact-submissions')
    ->name('contact.store');
