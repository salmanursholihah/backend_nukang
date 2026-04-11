<?php

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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::redirect('/', '/dashboard-general-dashboard');

// // Dashboard
// Route::get('/dashboard-general-dashboard', function () {
//     return view('pages.dashboard-general-dashboard', ['type_menu' => 'dashboard']);
// });
// Route::get('/dashboard-ecommerce-dashboard', function () {
//     return view('pages.dashboard-ecommerce-dashboard', ['type_menu' => 'dashboard']);
// });


// // Layout
// Route::get('/layout-default-layout', function () {
//     return view('pages.layout-default-layout', ['type_menu' => 'layout']);
// });

// // Blank Page
// Route::get('/blank-page', function () {
//     return view('pages.blank-page', ['type_menu' => '']);
// });

// // Bootstrap
// Route::get('/bootstrap-alert', function () {
//     return view('pages.bootstrap-alert', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-badge', function () {
//     return view('pages.bootstrap-badge', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-breadcrumb', function () {
//     return view('pages.bootstrap-breadcrumb', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-buttons', function () {
//     return view('pages.bootstrap-buttons', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-card', function () {
//     return view('pages.bootstrap-card', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-carousel', function () {
//     return view('pages.bootstrap-carousel', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-collapse', function () {
//     return view('pages.bootstrap-collapse', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-dropdown', function () {
//     return view('pages.bootstrap-dropdown', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-form', function () {
//     return view('pages.bootstrap-form', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-list-group', function () {
//     return view('pages.bootstrap-list-group', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-media-object', function () {
//     return view('pages.bootstrap-media-object', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-modal', function () {
//     return view('pages.bootstrap-modal', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-nav', function () {
//     return view('pages.bootstrap-nav', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-navbar', function () {
//     return view('pages.bootstrap-navbar', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-pagination', function () {
//     return view('pages.bootstrap-pagination', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-popover', function () {
//     return view('pages.bootstrap-popover', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-progress', function () {
//     return view('pages.bootstrap-progress', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-table', function () {
//     return view('pages.bootstrap-table', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-tooltip', function () {
//     return view('pages.bootstrap-tooltip', ['type_menu' => 'bootstrap']);
// });
// Route::get('/bootstrap-typography', function () {
//     return view('pages.bootstrap-typography', ['type_menu' => 'bootstrap']);
// });


// // components
// Route::get('/components-article', function () {
//     return view('pages.components-article', ['type_menu' => 'components']);
// });
// Route::get('/components-avatar', function () {
//     return view('pages.components-avatar', ['type_menu' => 'components']);
// });
// Route::get('/components-chat-box', function () {
//     return view('pages.components-chat-box', ['type_menu' => 'components']);
// });
// Route::get('/components-empty-state', function () {
//     return view('pages.components-empty-state', ['type_menu' => 'components']);
// });
// Route::get('/components-gallery', function () {
//     return view('pages.components-gallery', ['type_menu' => 'components']);
// });
// Route::get('/components-hero', function () {
//     return view('pages.components-hero', ['type_menu' => 'components']);
// });
// Route::get('/components-multiple-upload', function () {
//     return view('pages.components-multiple-upload', ['type_menu' => 'components']);
// });
// Route::get('/components-pricing', function () {
//     return view('pages.components-pricing', ['type_menu' => 'components']);
// });
// Route::get('/components-statistic', function () {
//     return view('pages.components-statistic', ['type_menu' => 'components']);
// });
// Route::get('/components-tab', function () {
//     return view('pages.components-tab', ['type_menu' => 'components']);
// });
// Route::get('/components-table', function () {
//     return view('pages.components-table', ['type_menu' => 'components']);
// });
// Route::get('/components-user', function () {
//     return view('pages.components-user', ['type_menu' => 'components']);
// });
// Route::get('/components-wizard', function () {
//     return view('pages.components-wizard', ['type_menu' => 'components']);
// });

// // forms
// Route::get('/forms-advanced-form', function () {
//     return view('pages.forms-advanced-form', ['type_menu' => 'forms']);
// });
// Route::get('/forms-editor', function () {
//     return view('pages.forms-editor', ['type_menu' => 'forms']);
// });
// Route::get('/forms-validation', function () {
//     return view('pages.forms-validation', ['type_menu' => 'forms']);
// });

// // google maps
// // belum tersedia

// // modules
// Route::get('/modules-calendar', function () {
//     return view('pages.modules-calendar', ['type_menu' => 'modules']);
// });
// Route::get('/modules-chartjs', function () {
//     return view('pages.modules-chartjs', ['type_menu' => 'modules']);
// });
// Route::get('/modules-datatables', function () {
//     return view('pages.modules-datatables', ['type_menu' => 'modules']);
// });
// Route::get('/modules-flag', function () {
//     return view('pages.modules-flag', ['type_menu' => 'modules']);
// });
// Route::get('/modules-font-awesome', function () {
//     return view('pages.modules-font-awesome', ['type_menu' => 'modules']);
// });
// Route::get('/modules-ion-icons', function () {
//     return view('pages.modules-ion-icons', ['type_menu' => 'modules']);
// });
// Route::get('/modules-owl-carousel', function () {
//     return view('pages.modules-owl-carousel', ['type_menu' => 'modules']);
// });
// Route::get('/modules-sparkline', function () {
//     return view('pages.modules-sparkline', ['type_menu' => 'modules']);
// });
// Route::get('/modules-sweet-alert', function () {
//     return view('pages.modules-sweet-alert', ['type_menu' => 'modules']);
// });
// Route::get('/modules-toastr', function () {
//     return view('pages.modules-toastr', ['type_menu' => 'modules']);
// });
// Route::get('/modules-vector-map', function () {
//     return view('pages.modules-vector-map', ['type_menu' => 'modules']);
// });
// Route::get('/modules-weather-icon', function () {
//     return view('pages.modules-weather-icon', ['type_menu' => 'modules']);
// });

// // auth
// Route::get('/auth-forgot-password', function () {
//     return view('pages.auth-forgot-password', ['type_menu' => 'auth']);
// });
// Route::get('/auth-login', function () {
//     return view('pages.auth-login', ['type_menu' => 'auth']);
// });
// Route::get('/auth-login2', function () {
//     return view('pages.auth-login2', ['type_menu' => 'auth']);
// });
// Route::get('/auth-register', function () {
//     return view('pages.auth-register', ['type_menu' => 'auth']);
// });
// Route::get('/auth-reset-password', function () {
//     return view('pages.auth-reset-password', ['type_menu' => 'auth']);
// });

// // error
// Route::get('/error-403', function () {
//     return view('pages.error-403', ['type_menu' => 'error']);
// });
// Route::get('/error-404', function () {
//     return view('pages.error-404', ['type_menu' => 'error']);
// });
// Route::get('/error-500', function () {
//     return view('pages.error-500', ['type_menu' => 'error']);
// });
// Route::get('/error-503', function () {
//     return view('pages.error-503', ['type_menu' => 'error']);
// });

// // features
// Route::get('/features-activities', function () {
//     return view('pages.features-activities', ['type_menu' => 'features']);
// });
// Route::get('/features-post-create', function () {
//     return view('pages.features-post-create', ['type_menu' => 'features']);
// });
// Route::get('/features-post', function () {
//     return view('pages.features-post', ['type_menu' => 'features']);
// });
// Route::get('/features-profile', function () {
//     return view('pages.features-profile', ['type_menu' => 'features']);
// });
// Route::get('/features-settings', function () {
//     return view('pages.features-settings', ['type_menu' => 'features']);
// });
// Route::get('/features-setting-detail', function () {
//     return view('pages.features-setting-detail', ['type_menu' => 'features']);
// });
// Route::get('/features-tickets', function () {
//     return view('pages.features-tickets', ['type_menu' => 'features']);
// });

// // utilities
// Route::get('/utilities-contact', function () {
//     return view('pages.utilities-contact', ['type_menu' => 'utilities']);
// });
// Route::get('/utilities-invoice', function () {
//     return view('pages.utilities-invoice', ['type_menu' => 'utilities']);
// });
// Route::get('/utilities-subscribe', function () {
//     return view('pages.utilities-subscribe', ['type_menu' => 'utilities']);
// });

// // credits
// Route::get('/credits', function () {
//     return view('pages.credits', ['type_menu' => '']);
// });



// Route::middleware(['auth'])->prefix('admin')->group(function () {
// Route::middleware(['auth','admin'])->prefix('admin')->group(function () {
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

//     Route::get('/users', [UserController::class, 'index']);
//     Route::delete('/users/{id}', [UserController::class, 'destroy']);

//     Route::get('/tukang', [TukangController::class, 'index']);
//     Route::post('/tukang/{id}/verify', [TukangController::class, 'verify']);

//     Route::get('/categories', [CategoryController::class, 'index']);
//     Route::post('/categories', [CategoryController::class, 'store']);
//     Route::put('/categories/{id}', [CategoryController::class, 'update']);
//     Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

//     Route::get('/services', [ServiceController::class, 'index']);
//     Route::post('/services', [ServiceController::class, 'store']);
//     Route::put('/services/{id}', [ServiceController::class, 'update']);
//     Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

//     Route::get('/orders', [OrderController::class, 'index']);

//     Route::get('/surveys', [SurveyController::class, 'index']);

//     Route::get('/reviews', [ReviewController::class, 'index']);

//     Route::get('/earnings', [EarningsController::class, 'index']);
//     Route::post('/earnings/{id}/pay', [EarningsController::class, 'pay']);
// });


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

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    Route::get('reviews/{id}/hide', [ReviewController::class, 'hide'])->name('reviews.hide');
    Route::get('reviews/{id}/show', [ReviewController::class, 'showReview'])->name('reviews.showReview');

    Route::resource('surveys', SurveyController::class);
});
