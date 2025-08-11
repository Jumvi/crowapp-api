<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MediaController;

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

// Route de test
Route::get('/test', function () {
    return response()->json(['message' => 'API fonctionnelle']);
});

// Routes publiques
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);

// Routes protégées par JWT
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Routes d'authentification
    // Routes d'authentification
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/user/update', [AuthController::class, 'updateUser']);
    Route::delete('/auth/user/{id}/delete', [AuthController::class, 'deleteUser']);

    // Routes de profil utilisateur
    Route::get('/user/profile', [UserController::class, 'getUserProfile']);
    Route::put('/user/profile', [UserController::class, 'updateUserProfile']);
    Route::get('/user/medias', [UserController::class, 'getUserMedias']);

    // Routes de médias
    Route::post('/media/upload', [MediaController::class, 'uploadMedia']);
    Route::delete('/media/{id}', [MediaController::class, 'deleteMedia']);
});
