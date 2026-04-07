<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\TukangProfile;

class ReviewObserver
{
    public function created(Review $review): void
    {
        $this->sync($review->tukang_id);
    }
    public function updated(Review $review): void
    {
        $this->sync($review->tukang_id);
    }
    public function deleted(Review $review): void
    {
        $this->sync($review->tukang_id);
    }

    private function sync(int $tukangId): void
    {
        $avg   = Review::where('tukang_id', $tukangId)->where('is_published', true)->avg('rating') ?? 0;
        $count = Review::where('tukang_id', $tukangId)->where('is_published', true)->count();

        TukangProfile::where('user_id', $tukangId)
            ->update(['rating' => round($avg, 2), 'total_reviews' => $count]);
    }
}
