<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Customer\PaymentController;
use App\Http\Controllers\Api\Customer\ReviewController;
use App\Http\Controllers\Api\Customer\SurveyRequestController;
use App\Http\Controllers\Api\Public\CategoryController;
use App\Http\Controllers\Api\Public\ServiceController;
use App\Http\Controllers\Api\Public\TukangController;
use App\Http\Controllers\Api\Tukang\JobOrderController;
use Illuminate\Support\Facades\Route;

// =============================================================
// PUBLIC — Tanpa Auth
// =============================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// // Kategori & Service publik (untuk halaman explore)
Route::get('/categories',                  [CategoryController::class, 'index']);
Route::get('/categories/{category}',       [CategoryController::class, 'show']);
Route::get('/services',                    [ServiceController::class, 'index']);
Route::get('/services/{service}',          [ServiceController::class, 'show']);

// // Cari tukang terdekat (publik, berdasarkan lat/lng)
Route::get('/tukangs',                     [TukangController::class, 'index']);
Route::get('/tukangs/{tukang}',            [TukangController::class, 'show']);
Route::get('/tukangs/{tukang}/reviews',    [TukangController::class, 'reviews']);
Route::get('/tukangs/{tukang}/services',   [TukangController::class, 'services']);


// // =============================================================
// // PROTECTED — Auth Required (semua role)
// // =============================================================

Route::middleware('auth:sanctum')->group(function () {

    //     // ── Auth ─────────────────────────────────────────────────
    Route::post('/logout',       [AuthController::class, 'logout']);
    Route::get('/me',            [AuthController::class, 'me']);
    Route::put('/me',            [AuthController::class, 'updateProfile']);
    Route::put('/me/password',   [AuthController::class, 'updatePassword']);
    Route::post('/me/avatar',    [AuthController::class, 'updateAvatar']);


    //     // =========================================================
    //     // CUSTOMER ROUTES
    //     // =========================================================

    Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {

        //         // ── Orders ───────────────────────────────────────────
        //         // GET    /customer/orders            → daftar order milik customer
        //         // POST   /customer/orders            → buat order baru
        //         // GET    /customer/orders/{order}    → detail order
        //         // DELETE /customer/orders/{order}    → batalkan order
        Route::get('/orders',                        [OrderController::class, 'index']);
        Route::post('/orders',                       [OrderController::class, 'store']);
        Route::get('/orders/{order}',                [OrderController::class, 'show']);
        Route::delete('/orders/{order}',             [OrderController::class, 'cancel']);
        Route::get('/orders/{order}/progresses',     [OrderController::class, 'progresses']);
        Route::get('/orders/{order}/payment',        [OrderController::class, 'paymentDetail']);

        //         // ── Payments ─────────────────────────────────────────
        //         // POST   /customer/payments          → bayar order
        //         // GET    /customer/payments/{payment}→ cek status pembayaran
                Route::post('/payments',                     [PaymentController::class, 'store']);
                Route::get('/payments/{payment}',            [PaymentController::class, 'show']);
                Route::post('/payments/{payment}/callback',  [PaymentController::class, 'callback']);

        //         // ── Survey Requests ───────────────────────────────────
        //         // GET    /customer/survey-requests           → daftar survey
        //         // POST   /customer/survey-requests           → minta survey
        //         // GET    /customer/survey-requests/{survey}  → detail survey
        //         // PUT    /customer/survey-requests/{survey}/approve → setuju estimasi → jadi order
        //         // DELETE /customer/survey-requests/{survey}  → batalkan survey
                Route::get('/survey-requests',                          [SurveyRequestController::class, 'index']);
                Route::post('/survey-requests',                         [SurveyRequestController::class, 'store']);
                Route::get('/survey-requests/{survey}',                 [SurveyRequestController::class, 'show']);
                Route::put('/survey-requests/{survey}/approve',         [SurveyRequestController::class, 'approve']);
                Route::delete('/survey-requests/{survey}',              [SurveyRequestController::class, 'cancel']);

        //         // ── Reviews ───────────────────────────────────────────
        //         // POST   /customer/reviews           → beri review setelah order selesai
        //         // GET    /customer/reviews           → daftar review yang pernah dibuat
                Route::get('/reviews',               [ReviewController::class, 'index']);
                Route::post('/reviews',              [ReviewController::class, 'store']);
            });


        //     // =========================================================
        //     // TUKANG ROUTES
        //     // =========================================================

            Route::middleware('role:tukang')->prefix('tukang')->name('tukang.')->group(function () {

        //         // ── Profile Tukang ────────────────────────────────────
        //         // GET  /tukang/profile       → lihat profil sendiri
        //         // PUT  /tukang/profile       → update profil
        //         // POST /tukang/profile/photo → upload foto profil
        //         Route::get('/profile',              [TukangProfileController::class, 'show']);
        //         Route::put('/profile',              [TukangProfileController::class, 'update']);
        //         Route::post('/profile/photo',       [TukangProfileController::class, 'updatePhoto']);
        //         Route::post('/profile/id-card',     [TukangProfileController::class, 'uploadIdCard']);

        //         // ── Services (keahlian tukang) ────────────────────────
        //         // GET    /tukang/services            → daftar service yang dikuasai
        //         // POST   /tukang/services            → tambah service
        //         // DELETE /tukang/services/{service}  → hapus service
        //         Route::get('/services',                         [TukangProfileController::class, 'services']);
        //         Route::post('/services',                        [TukangProfileController::class, 'addService']);
        //         Route::delete('/services/{service}',            [TukangProfileController::class, 'removeService']);

        //         // ── Lokasi Real-time ──────────────────────────────────
        //         // PUT  /tukang/location          → update koordinat GPS
        //         // PUT  /tukang/location/toggle   → toggle online/offline
        //         Route::put('/location',             [TukangLocationController::class, 'update']);
        //         Route::put('/location/toggle',      [TukangLocationController::class, 'toggle']);

        //         // ── Job Orders (order yang masuk ke tukang) ───────────
        //         // GET  /tukang/orders                        → daftar order masuk
        //         // GET  /tukang/orders/{order}                → detail order
        //         // PUT  /tukang/orders/{order}/accept         → terima order
        //         // PUT  /tukang/orders/{order}/reject         → tolak order
        //         // PUT  /tukang/orders/{order}/start          → mulai pengerjaan
        //         // PUT  /tukang/orders/{order}/complete       → selesaikan order
        //         // POST /tukang/orders/{order}/progress       → tambah progress foto
                Route::get('/orders',                          [JobOrderController::class, 'index']);
                Route::get('/orders/{order}',                  [JobOrderController::class, 'show']);
                Route::put('/orders/{order}/accept',           [JobOrderController::class, 'accept']);
                Route::put('/orders/{order}/reject',           [JobOrderController::class, 'reject']);
                Route::put('/orders/{order}/start',            [JobOrderController::class, 'start']);
                Route::put('/orders/{order}/complete',         [JobOrderController::class, 'complete']);
                Route::post('/orders/{order}/progress',        [JobOrderController::class, 'addProgress']);
                Route::delete('/orders/{order}/progress/{progress}', [JobOrderController::class, 'deleteProgress']);

        //         // ── Survey (survey yang ditugaskan ke tukang) ─────────
        //         // GET  /tukang/surveys                       → daftar survey masuk
        //         // GET  /tukang/surveys/{survey}              → detail survey
        //         // PUT  /tukang/surveys/{survey}/accept       → terima survey
        //         // PUT  /tukang/surveys/{survey}/reject       → tolak survey
        //         // PUT  /tukang/surveys/{survey}/set-price    → isi estimasi harga
        //         Route::get('/surveys',                         [JobSurveyController::class, 'index']);
        //         Route::get('/surveys/{survey}',                [JobSurveyController::class, 'show']);
        //         Route::put('/surveys/{survey}/accept',         [JobSurveyController::class, 'accept']);
        //         Route::put('/surveys/{survey}/reject',         [JobSurveyController::class, 'reject']);
        //         Route::put('/surveys/{survey}/set-price',      [JobSurveyController::class, 'setPrice']);

        //         // ── Earnings ──────────────────────────────────────────
        //         // GET  /tukang/earnings          → riwayat pendapatan
        //         // GET  /tukang/earnings/summary  → total saldo, pending, paid
        //         Route::get('/earnings',             [EarningController::class, 'index']);
        //         Route::get('/earnings/summary',     [EarningController::class, 'summary']);

        //         // ── Withdrawals ───────────────────────────────────────
        //         // GET  /tukang/withdrawals       → riwayat penarikan
        //         // POST /tukang/withdrawals       → ajukan penarikan
        //         // GET  /tukang/withdrawals/{w}   → detail penarikan
        //         Route::get('/withdrawals',          [WithdrawalController::class, 'index']);
        //         Route::post('/withdrawals',         [WithdrawalController::class, 'store']);
        //         Route::get('/withdrawals/{withdrawal}', [WithdrawalController::class, 'show']);
            });


        //     // =========================================================
        //     // SHARED ROUTES — Customer & Tukang
        //     // =========================================================

        //     Route::middleware('role:customer,tukang')->group(function () {

        //         // ── Chats ─────────────────────────────────────────────
        //         // GET  /chats             → daftar chat
        //         // POST /chats             → mulai chat baru dengan tukang/customer
        //         // GET  /chats/{chat}      → detail chat + info lawan bicara
        //         Route::get('/chats',                    [ChatController::class, 'index']);
        //         Route::post('/chats',                   [ChatController::class, 'store']);
        //         Route::get('/chats/{chat}',             [ChatController::class, 'show']);

        //         // ── Messages ──────────────────────────────────────────
        //         // GET  /chats/{chat}/messages        → ambil pesan (dengan pagination)
        //         // POST /chats/{chat}/messages        → kirim pesan
        //         // PUT  /chats/{chat}/messages/read   → tandai semua pesan sudah dibaca
        //         Route::get('/chats/{chat}/messages',         [MessageController::class, 'index']);
        //         Route::post('/chats/{chat}/messages',        [MessageController::class, 'store']);
        //         Route::put('/chats/{chat}/messages/read',    [MessageController::class, 'markRead']);

        //         // ── Notifications ─────────────────────────────────────
        //         // GET  /notifications            → daftar notifikasi
        //         // GET  /notifications/unread     → jumlah belum dibaca
        //         // PUT  /notifications/{id}/read  → tandai satu notif dibaca
        //         // PUT  /notifications/read-all   → tandai semua dibaca
        //         Route::get('/notifications',                  [NotificationController::class, 'index']);
        //         Route::get('/notifications/unread-count',     [NotificationController::class, 'unreadCount']);
        //         Route::put('/notifications/{id}/read',        [NotificationController::class, 'markRead']);
        //         Route::put('/notifications/read-all',         [NotificationController::class, 'markAllRead']);
        //     });


        //     // =========================================================
        //     // ADMIN ROUTES
        //     // =========================================================

        //     Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        //         // ── Dashboard ─────────────────────────────────────────
        //         Route::get('/dashboard',    [AdminDashboardController::class, 'index']);

        //         // ── Users ─────────────────────────────────────────────
        //         // GET    /admin/users                → daftar semua user
        //         // GET    /admin/users/{user}         → detail user
        //         // PUT    /admin/users/{user}         → edit user
        //         // DELETE /admin/users/{user}         → hapus user (soft delete)
        //         // PUT    /admin/users/{user}/toggle  → aktif/nonaktif user
        //         // PUT    /admin/users/{user}/verify  → verifikasi tukang
        //         Route::get('/users',                      [AdminUserController::class, 'index']);
        //         Route::get('/users/{user}',               [AdminUserController::class, 'show']);
        //         Route::put('/users/{user}',               [AdminUserController::class, 'update']);
        //         Route::delete('/users/{user}',            [AdminUserController::class, 'destroy']);
        //         Route::put('/users/{user}/toggle',        [AdminUserController::class, 'toggle']);
        //         Route::put('/users/{user}/verify',        [AdminUserController::class, 'verify']);

        //         // ── Categories ────────────────────────────────────────
        //         Route::get('/categories',                 [AdminCategoryController::class, 'index']);
        //         Route::post('/categories',                [AdminCategoryController::class, 'store']);
        //         Route::get('/categories/{category}',      [AdminCategoryController::class, 'show']);
        //         Route::put('/categories/{category}',      [AdminCategoryController::class, 'update']);
        //         Route::delete('/categories/{category}',   [AdminCategoryController::class, 'destroy']);

        //         // ── Services ──────────────────────────────────────────
        //         Route::get('/services',                   [AdminServiceController::class, 'index']);
        //         Route::post('/services',                  [AdminServiceController::class, 'store']);
        //         Route::get('/services/{service}',         [AdminServiceController::class, 'show']);
        //         Route::put('/services/{service}',         [AdminServiceController::class, 'update']);
        //         Route::delete('/services/{service}',      [AdminServiceController::class, 'destroy']);

        //         // ── Orders ────────────────────────────────────────────
        //         Route::get('/orders',                     [AdminOrderController::class, 'index']);
        //         Route::get('/orders/{order}',             [AdminOrderController::class, 'show']);
        //         Route::put('/orders/{order}/cancel',      [AdminOrderController::class, 'cancel']);

        //         // ── Survey Requests ───────────────────────────────────
        //         Route::get('/surveys',                    [AdminSurveyController::class, 'index']);
        //         Route::get('/surveys/{survey}',           [AdminSurveyController::class, 'show']);

        //         // ── Earnings ──────────────────────────────────────────
        //         Route::get('/earnings',                         [AdminEarningController::class, 'index']);
        //         Route::get('/earnings/{earning}',               [AdminEarningController::class, 'show']);
        //         Route::put('/earnings/{earning}/settle',        [AdminEarningController::class, 'settle']);

        //         // ── Withdrawals ───────────────────────────────────────
        //         Route::get('/withdrawals',                       [AdminWithdrawalController::class, 'index']);
        //         Route::get('/withdrawals/{withdrawal}',          [AdminWithdrawalController::class, 'show']);
        //         Route::put('/withdrawals/{withdrawal}/approve',  [AdminWithdrawalController::class, 'approve']);
        //         Route::put('/withdrawals/{withdrawal}/reject',   [AdminWithdrawalController::class, 'reject']);

        //         // ── Reviews ───────────────────────────────────────────
        //         Route::get('/reviews',                    [AdminReviewController::class, 'index']);
        //         Route::get('/reviews/{review}',           [AdminReviewController::class, 'show']);
        //         Route::delete('/reviews/{review}',        [AdminReviewController::class, 'destroy']);
        //         Route::put('/reviews/{review}/unpublish', [AdminReviewController::class, 'unpublish']);
    });
// });
