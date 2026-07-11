@props([
    'alternate' => false,
    'more' => false,
])

@php
    $reviewColorMap = [
        'red' => '#ef4444',
        'orange' => '#f97316',
        'amber' => '#f59e0b',
        'yellow' => '#eab308',
        'lime' => '#84cc16',
        'green' => '#22c55e',
        'emerald' => '#10b981',
        'teal' => '#14b8a6',
        'cyan' => '#06b6d4',
        'sky' => '#0ea5e9',
        'blue' => '#3b82f6',
        'indigo' => '#6366f1',
        'violet' => '#8b5cf6',
        'purple' => '#a855f7',
        'pink' => '#ec4899',
        'rose' => '#f43f5e',
    ];

    $resolveAvatarColor = function (?string $color) use ($reviewColorMap): string {
        $color = trim((string) $color);

        if ($color !== '' && str_starts_with($color, '#')) {
            return $color;
        }

        return $reviewColorMap[$color] ?? '#e11d48';
    };
@endphp

@if($reviews->isEmpty())
    <div class="rounded-[1.5rem] bg-secondary-200/70 px-6 py-8 text-secondary-600 sm:px-7">
        Opinie pojawią się tutaj wkrótce.
    </div>
@elseif($more)
    @include('section.reviews-slider', [
        'reviews' => $reviews,
        'alternate' => $alternate,
        'resolveAvatarColor' => $resolveAvatarColor,
        'reviewCount' => $reviewCount,
        'reviewAverageRating' => $reviewAverageRating,
    ])
@else
    @php
        $reviewList = $reviews->values();
        $wallItems = collect([['type' => 'summary']])->concat(
            $reviewList->map(fn ($review) => ['type' => 'review', 'review' => $review])
        )->values();

        $estimateWallItemHeight = function (array $item): int {
            if ($item['type'] === 'summary') {
                return 320;
            }

            $review = $item['review'];
            $titleLength = mb_strlen(trim((string) $review->title));
            $bodyLength = mb_strlen(trim(strip_tags((string) $review->review)));

            return 240 + (int) ceil(($titleLength + $bodyLength) / 110) * 26;
        };

        $distributeWallItems = function (\Illuminate\Support\Collection $items, int $columns) use ($estimateWallItemHeight): \Illuminate\Support\Collection {
            $stacks = collect(range(0, $columns - 1))->mapWithKeys(
                fn (int $column): array => [(string) $column => collect()]
            );

            $heights = array_fill(0, $columns, 0);

            foreach ($items as $item) {
                $targetColumn = array_search(min($heights), $heights, true);

                $stacks[(string) $targetColumn]->push($item);
                $heights[$targetColumn] += $estimateWallItemHeight($item);
            }

            return $stacks;
        };

        $twoColumnReviews = $distributeWallItems($wallItems, 2);
        $threeColumnReviews = $distributeWallItems($wallItems, 3);
    @endphp

    <div class="mx-auto mt-24">
        <div class="space-y-5 sm:hidden">
            @foreach($wallItems as $item)
                @if($item['type'] === 'summary')
                    @include('section.partials.review-summary-card', [
                        'reviewCount' => $reviewCount,
                        'reviewAverageRating' => $reviewAverageRating,
                    ])
                @else
                    @include('section.partials.review-card', [
                        'review' => $item['review'],
                        'alternate' => $alternate,
                        'resolveAvatarColor' => $resolveAvatarColor,
                    ])
                @endif
            @endforeach
        </div>

        <div class="hidden items-start gap-5 sm:grid 2xl:hidden sm:grid-cols-2">
            @foreach($twoColumnReviews as $columnItems)
                <div class="space-y-5">
                    @foreach($columnItems as $item)
                        @if($item['type'] === 'summary')
                            @include('section.partials.review-summary-card', [
                                'reviewCount' => $reviewCount,
                                'reviewAverageRating' => $reviewAverageRating,
                            ])
                        @else
                            @include('section.partials.review-card', [
                                'review' => $item['review'],
                                'alternate' => $alternate,
                                'resolveAvatarColor' => $resolveAvatarColor,
                            ])
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="hidden items-start 2xl:grid 2xl:grid-cols-3 2xl:gap-5">
            @foreach($threeColumnReviews as $columnItems)
                <div class="space-y-5">
                    @foreach($columnItems as $item)
                        @if($item['type'] === 'summary')
                            @include('section.partials.review-summary-card', [
                                'reviewCount' => $reviewCount,
                                'reviewAverageRating' => $reviewAverageRating,
                            ])
                        @else
                            @include('section.partials.review-card', [
                                'review' => $item['review'],
                                'alternate' => $alternate,
                                'resolveAvatarColor' => $resolveAvatarColor,
                            ])
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endif
