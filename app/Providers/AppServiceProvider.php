<?php

namespace App\Providers;

<<<<<<< HEAD
use Illuminate\Support\Facades\URL;
=======
use App\Models\Order;
use App\Models\Review;
use App\Observers\OrderObserver;
use App\Observers\ReviewObserver;
>>>>>>> 7ce728f3b5a40b966c12bbd32c474593d4a3e292
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
<<<<<<< HEAD
        URL::forceScheme('https');
=======
        Review::observe(ReviewObserver::class);
        Order::observe(OrderObserver::class);
>>>>>>> 7ce728f3b5a40b966c12bbd32c474593d4a3e292
    }
}
