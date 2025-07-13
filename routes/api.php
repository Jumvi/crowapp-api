<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes publiques

$AuthController = App\Http\Controllers\AuthController::class;
Route::post('/auth/login', [$AuthController, 'login']);
Route::post('/auth/register', [$AuthController, 'register']);

// Routes protégées par JWT
Route::middleware('auth:api')->group(function () use ($AuthController) {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/auth/logout', [$AuthController, 'logout']);
    Route::post('/auth/refresh', [$AuthController, 'refresh']);
    Route::get('/auth/me', [$AuthController, 'me']);
});
