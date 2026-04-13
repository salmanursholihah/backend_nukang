<?php

<<<<<<< HEAD
use App\Http\Controllers\Web\Auth\AuthController;
use App\Http\Controllers\Web\Superadmin\CategoryController;
use App\Http\Controllers\Web\Superadmin\DashboardController;
use App\Http\Controllers\Web\Superadmin\EarningsController;
use App\Http\Controllers\Web\Superadmin\OrderController;
use App\Http\Controllers\Web\Superadmin\ReviewController;
use App\Http\Controllers\Web\Superadmin\ServiceController;
use App\Http\Controllers\Web\Superadmin\SurveyController;
use App\Http\Controllers\Web\Superadmin\TukangController;
use App\Http\Controllers\Web\Superadmin\UserController;
use App\Models\PartnerEarning;
use App\Services\Bca\TokenService;
use App\Services\Bca\VirtualAccountService;
=======
use App\Http\Controllers\Web\Admin\CategoryController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\EarningController;
use App\Http\Controllers\Web\Admin\OrderController;
use App\Http\Controllers\Web\Admin\ReportController;
use App\Http\Controllers\Web\Admin\ReviewController;
use App\Http\Controllers\Web\Admin\ServiceController;
use App\Http\Controllers\Web\Admin\SurveyController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\Admin\WithdrawalController;
use App\Http\Controllers\Web\AuthController;
>>>>>>> 7ce728f3b5a40b966c12bbd32c474593d4a3e292
use Illuminate\Support\Facades\Route;

// =============================================================
// PUBLIC — Tanpa Auth
// =============================================================

Route::get('/', fn() => redirect()->route('admin.dashboard'));

// ── Auth ──────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');


// =============================================================
// PROTECTED — Auth Required
// =============================================================

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // =========================================================
    // ADMIN ROUTES
    // =========================================================

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // ── Dashboard ─────────────────────────────────────
            // GET  /admin/dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


            // ── Users ─────────────────────────────────────────
            // GET    /admin/users              → daftar semua user
            // GET    /admin/users/create       → form tambah user
            // POST   /admin/users              → simpan user baru
            // GET    /admin/users/{user}        → detail user
            // GET    /admin/users/{user}/edit   → form edit user
            // PUT    /admin/users/{user}        → update user
            // DELETE /admin/users/{user}        → hapus user
            // PUT    /admin/users/{user}/toggle → aktif/nonaktif
            // PUT    /admin/users/{user}/verify → verifikasi tukang
            Route::resource('/users', UserController::class);
            Route::put('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
            Route::put('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');


            // ── Categories ────────────────────────────────────
            // GET    /admin/categories
            // GET    /admin/categories/create
            // POST   /admin/categories
            // GET    /admin/categories/{category}
            // GET    /admin/categories/{category}/edit
            // PUT    /admin/categories/{category}
            // DELETE /admin/categories/{category}
            Route::resource('/categories', CategoryController::class);


<<<<<<< HEAD
Route::get('/', function () {
    return redirect()->route('login');
});

////integrasi va bca
Route::get('/test-token', function () {
    return app(TokenService::class)->getToken();
});
Route::get('/test-va', function () {
    return app(VirtualAccountService::class)->createVA();
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
=======
            // ── Services ──────────────────────────────────────
            // GET    /admin/services
            // GET    /admin/services/create
            // POST   /admin/services
            // GET    /admin/services/{service}
            // GET    /admin/services/{service}/edit
            // PUT    /admin/services/{service}
            // DELETE /admin/services/{service}
            Route::resource('/services', ServiceController::class);
>>>>>>> 7ce728f3b5a40b966c12bbd32c474593d4a3e292


            // ── Orders ────────────────────────────────────────
            // GET    /admin/orders             → daftar semua order
            // GET    /admin/orders/{order}     → detail order
            // PUT    /admin/orders/{order}/cancel → batalkan order
            Route::get('/orders',                [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}',        [OrderController::class, 'show'])->name('orders.show');
            Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');


            // ── Survey Requests ───────────────────────────────
            // GET    /admin/surveys            → daftar semua survey
            // GET    /admin/surveys/{survey}   → detail survey
            Route::get('/surveys',          [SurveyController::class, 'index'])->name('surveys.index');
            Route::get('/surveys/{survey}', [SurveyController::class, 'show'])->name('surveys.show');


<<<<<<< HEAD
    Route::resource('categories', CategoryController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('users', UserController::class);
    Route::resource('tukangs', TukangController::class);
    Route::post('/tukangs/{id}/verify', [TukangController::class, 'verify'])
        ->name('tukangs.verify');
    Route::post('/tukangs/{id}/reject', [TukangController::class, 'reject'])
        ->name('tukangs.reject');
    Route::resource('orders', OrderController::class);
    Route::resource('earnings', EarningsController::class);
    Route::post('/earnings/{id}/pay', [EarningsController::class, 'pay'])
        ->name('earnings.pay');
    Route::resource('reviews', ReviewController::class);
=======
            // ── Earnings ──────────────────────────────────────
            // GET  /admin/earnings             → daftar semua earning
            // GET  /admin/earnings/{earning}   → detail earning
            // PUT  /admin/earnings/{earning}/settle → settle earning
            Route::get('/earnings',                      [EarningController::class, 'index'])->name('earnings.index');
            Route::get('/earnings/{earning}',            [EarningController::class, 'show'])->name('earnings.show');
            Route::put('/earnings/{earning}/settle',     [EarningController::class, 'settle'])->name('earnings.settle');
>>>>>>> 7ce728f3b5a40b966c12bbd32c474593d4a3e292


            // ── Withdrawals ───────────────────────────────────
            // GET  /admin/withdrawals                    → daftar penarikan
            // GET  /admin/withdrawals/{withdrawal}       → detail penarikan
            // PUT  /admin/withdrawals/{withdrawal}/approve → setujui
            // PUT  /admin/withdrawals/{withdrawal}/reject  → tolak
            Route::get('/withdrawals',                         [WithdrawalController::class, 'index'])->name('withdrawals.index');
            Route::get('/withdrawals/{withdrawal}',            [WithdrawalController::class, 'show'])->name('withdrawals.show');
            Route::put('/withdrawals/{withdrawal}/approve',    [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
            Route::put('/withdrawals/{withdrawal}/reject',     [WithdrawalController::class, 'reject'])->name('withdrawals.reject');


            // ── Reviews ───────────────────────────────────────
            // GET    /admin/reviews             → daftar semua review
            // GET    /admin/reviews/{review}    → detail review
            // DELETE /admin/reviews/{review}    → hapus review
            // PUT    /admin/reviews/{review}/unpublish → sembunyikan
            Route::get('/reviews',                     [ReviewController::class, 'index'])->name('reviews.index');
            Route::get('/reviews/{review}',            [ReviewController::class, 'show'])->name('reviews.show');
            Route::delete('/reviews/{review}',         [ReviewController::class, 'destroy'])->name('reviews.destroy');
            Route::put('/reviews/{review}/unpublish',  [ReviewController::class, 'unpublish'])->name('reviews.unpublish');


            // ── Reports ───────────────────────────────────────
            // GET  /admin/reports              → halaman laporan
            // GET  /admin/reports/orders       → laporan order
            // GET  /admin/reports/revenue      → laporan pendapatan
            // GET  /admin/reports/tukangs      → laporan performa tukang
            Route::get('/reports',          [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/orders',   [ReportController::class, 'orders'])->name('reports.orders');
            Route::get('/reports/revenue',  [ReportController::class, 'revenue'])->name('reports.revenue');
            Route::get('/reports/tukangs',  [ReportController::class, 'tukangs'])->name('reports.tukangs');
        });
});
