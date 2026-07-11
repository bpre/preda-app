<?php

namespace App\View\Components\Section;

use Illuminate\View\View;
use App\Models\Website\Review;
use Illuminate\View\Component;

class Reviews extends Component
{

    public $reviews;
    public int $reviewCount = 0;
    public float $reviewAverageRating = 0.0;

    public function __construct(public $more = false) {}
    public function render(): View
    {
        $statsQuery = Review::where('is_published', true);
        $this->reviewCount = (clone $statsQuery)->count();
        $this->reviewAverageRating = round((float) ((clone $statsQuery)->avg('rating') ?? 0), 1);

        $query = Review::where('is_published', true)
            ->whereNotNull('review')
            ->where('review', '!=', '')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if($this->more) {
            $this->reviews = $query->limit(12)->get();
        } else {
            $this->reviews = $query->get();
        }

        return view('section.reviews');
    }
}
