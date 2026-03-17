<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Customer\CategoryController;
use App\Http\Controllers\Api\Customer\ChatController;
use App\Http\Controllers\Api\Customer\DashboardController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Customer\ProfileController;
use App\Http\Controllers\Api\Customer\ReviewController;
use App\Http\Controllers\Api\Customer\ServiceController;
use App\Http\Controllers\Api\Customer\SurveyController;
use App\Http\Controllers\Api\Customer\TukangController;
use App\Http\Controllers\Api\Tukang\TukangChatController;
use App\Http\Controllers\Api\Tukang\TukangDashboardController;
use App\Http\Controllers\Api\Tukang\TukangEarningController;
use App\Http\Controllers\Api\Tukang\TukangOrderController;
use App\Http\Controllers\Api\Tukang\TukangProfileController;
use App\Http\Controllers\Api\Tukang\TukangSurveyController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | Customer
    |--------------------------------------------------------------------------
    */
    Route::prefix('customer')
        ->middleware('role:customer')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index']);

            Route::get('/categories', [CategoryController::class, 'index']);
            Route::get('/categories/{id}', [CategoryController::class, 'show']);

            Route::get('/services', [ServiceController::class, 'index']);
            Route::post('/services', [ServiceController::class, 'store']);
            Route::get('/services/{id}', [ServiceController::class, 'show']);


            Route::get('/tukangs', [TukangController::class, 'index']);
            Route::get('/tukangs/{id}', [TukangController::class, 'show']);

            Route::get('/surveys', [SurveyController::class, 'index']);
            Route::post('/surveys', [SurveyController::class, 'store']);
            Route::get('/surveys/{id}', [SurveyController::class, 'show']);
            Route::post('/surveys/{id}/approve', [SurveyController::class, 'approve']);
            Route::post('/surveys/{id}/reject', [SurveyController::class, 'reject']);
            Route::post('/surveys/{id}/cancel', [SurveyController::class, 'cancel']);

            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{id}', [OrderController::class, 'show']);
            Route::post('/orders', [OrderController::class, 'store']);


            Route::get('/chats', [ChatController::class, 'index']);
            Route::post('/chats/start', [ChatController::class, 'startChat']);
            Route::get('/chats/{chatId}/messages', [ChatController::class, 'messages']);
            Route::post('/chats/{chatId}/messages', [ChatController::class, 'sendMessage']);

            Route::get('/reviews', [ReviewController::class, 'index']);
            Route::post('/reviews', [ReviewController::class, 'store']);

            Route::get('/profile', [ProfileController::class, 'show']);
            Route::post('/profile', [ProfileController::class, 'update']);
        });

    /*
    |--------------------------------------------------------------------------
    | Tukang / Partner
    |--------------------------------------------------------------------------
    */
    Route::prefix('tukang')
        ->middleware('role:tukang')
        ->group(function () {
            Route::get('/dashboard', [TukangDashboardController::class, 'index']);

            Route::get('/surveys', [TukangSurveyController::class, 'index']);
            Route::get('/surveys/{id}', [TukangSurveyController::class, 'show']);
            Route::post('/surveys/{id}/accept', [TukangSurveyController::class, 'accept']);
            Route::post('/surveys/{id}/reject', [TukangSurveyController::class, 'reject']);
            Route::post('/surveys/{id}/set-survey-fee', [TukangSurveyController::class, 'setSurveyFee']);
            Route::post('/surveys/{id}/send-estimation', [TukangSurveyController::class, 'sendEstimation']);

            Route::get('/orders', [TukangOrderController::class, 'index']);
            Route::get('/orders/{id}', [TukangOrderController::class, 'show']);
            Route::post('/orders/{id}/start', [TukangOrderController::class, 'start']);
            Route::post('/orders/{id}/progress', [TukangOrderController::class, 'storeProgress']);
            Route::post('/orders/{id}/complete', [TukangOrderController::class, 'complete']);

            Route::get('/chats', [TukangChatController::class, 'index']);
            Route::get('/chats/{chatId}/messages', [TukangChatController::class, 'messages']);
            Route::post('/chats/{chatId}/messages', [TukangChatController::class, 'sendMessage']);

            Route::get('/earnings', [TukangEarningController::class, 'index']);
            Route::get('/earnings/summary', [TukangEarningController::class, 'summary']);

            Route::get('/profile', [TukangProfileController::class, 'show']);
            Route::post('/profile', [TukangProfileController::class, 'update']);
        });
});
