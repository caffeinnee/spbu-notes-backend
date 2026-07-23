<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;

// Handle CORS preflight (OPTIONS) requests
Route::options('/{any}', function () {
    return response()->json('ok', 200);
})->where('any', '.*');

// ── Public routes ─────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ── Protected routes (Sanctum token required) ─────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/profile',          [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout',          [AuthController::class, 'logout']);

    // Notes CRUD (role-based access enforced inside controller)
    Route::get('/notes',          [NoteController::class, 'index']);
    Route::post('/notes',         [NoteController::class, 'store']);
    Route::put('/notes/{id}',     [NoteController::class, 'update']);
    Route::delete('/notes/{id}',  [NoteController::class, 'destroy']);
    Route::get('/notes/{id}/download', [NoteController::class, 'download']);

    // PIN verification with rate limiting (5 attempts per minute)
    Route::middleware('throttle:5,1')->post('/notes/{id}/verify-pin', [NoteController::class, 'verifyPin']);
});