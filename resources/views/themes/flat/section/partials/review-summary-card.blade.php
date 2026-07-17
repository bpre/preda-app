@php
    $googleReviewsUrl = config('services.google_business_profile.reviews_url');
@endphp

<article class="ui-review-summary-card">
    <div class="ui-review-summary-kicker">DOSKONAŁA</div>

    <div class="ui-review-summary-stars" aria-hidden="true">
        @for($i = 0; $i < 5; $i++)
            <svg viewBox="0 0 16 15" class="h-4 w-4 flex-none">
                <path d="M7.197 0.549c.104-.322.405-.54.743-.54.339 0 .64.218.744.54l1.289 3.973c.038.117.113.219.213.291.099.072.22.111.344.111l4.176-.001c.339 0 .64.218.744.54.103.321-.01.668-.28.864l-3.378 2.453a.756.756 0 0 0-.213.29.743.743 0 0 0 0 .36l1.292 3.972c.103.322-.01.668-.28.864a.777.777 0 0 1-.9 0L8.27 11.73a.78.78 0 0 0-.345-.111.78.78 0 0 0-.344.111l-3.375 2.455a.778.778 0 0 1-.9 0c-.27-.196-.383-.542-.28-.864L4.318 9.35a.741.741 0 0 0 0-.36.752.752 0 0 0-.213-.29L.728 6.247c-.27-.196-.383-.543-.28-.864.104-.322.405-.54.744-.54l4.175.001a.78.78 0 0 0 .344-.111.754.754 0 0 0 .213-.291L7.197.549Z" fill="#F6BB06"/>
            </svg>
        @endfor
    </div>

    <p class="ui-review-summary-copy">
        Na podstawie <strong>{{ number_format($reviewCount, 0, ',', ' ') }}</strong> opinii
    </p>

    @if(filled($googleReviewsUrl))
        <a href="{{ $googleReviewsUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex">
            <img src="{{ asset('images/google-logo.svg') }}" alt="Google" width="255" height="80" class="ui-review-summary-logo" loading="lazy" />
        </a>
    @else
        <img src="{{ asset('images/google-logo.svg') }}" alt="Google" width="255" height="80" class="ui-review-summary-logo" loading="lazy" />
    @endif

    <p class="ui-review-summary-score">
        średnia ocen: <strong>{{ number_format($reviewAverageRating, 1, ',', ' ') }}</strong>
    </p>
</article>
