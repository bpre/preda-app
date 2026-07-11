@php
    $surfaceClass = $alternate ? 'ui-review-card-light' : 'ui-review-card-muted';
    $googleReviewsUrl = config('services.google_business_profile.reviews_url');
@endphp

<div
    class="container mx-auto flex w-full flex-col max-sm:!px-0"
    x-data="{
      atBeginning: false,
      atEnd: false,
      hasOverflow: false,
      resizeHandler: null,
      getStep() {
          let slider = this.$refs.slider

          if (! slider || ! slider.firstElementChild) {
              return 0
          }

          let cardWidth = slider.firstElementChild.getBoundingClientRect().width
          let gap = parseFloat(window.getComputedStyle(slider).columnGap || window.getComputedStyle(slider).gap || 0)

          return cardWidth + gap
      },
      next() {
          let slider = this.$refs.slider
          slider.scrollBy({ left: this.getStep(), behavior: 'smooth' })
      },
      prev() {
          let slider = this.$refs.slider
          slider.scrollBy({ left: this.getStep() * -1, behavior: 'smooth' })
      },
      update() {
          let slider = this.$refs.slider

          if (! slider) {
              return
          }

          this.hasOverflow = slider.scrollWidth > slider.clientWidth + 4
          this.atBeginning = slider.scrollLeft <= 4
          this.atEnd = slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 4
      },
      init() {
          this.resizeHandler = () => this.update()
          this.$nextTick(() => this.update())
          window.addEventListener('resize', this.resizeHandler)
      },
      destroy() {
          if (this.resizeHandler) {
              window.removeEventListener('resize', this.resizeHandler)
          }
      }
  }"
>
    <div
        class="flex flex-col w-full"
        aria-labelledby="carousel-label"
        role="region"
        tabindex="0"
        x-on:keydown.left="prev"
        x-on:keydown.right="next"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:px-2">
            <div class="ui-review-slider-summary">
                <div class="ui-review-slider-summary-title">DOSKONAŁA</div>

                <div class="ui-review-slider-summary-copy ui-review-slider-summary-side">
                    Średnia ocen <strong>{{ number_format($reviewAverageRating, 1, ',', ' ') }}</strong> na podstawie <strong>{{ number_format($reviewCount, 0, ',', ' ') }}</strong> opinii
                </div>

                <div class="ui-review-slider-stars" aria-hidden="true">
                    @for($i = 0; $i < 5; $i++)
                        <svg viewBox="0 0 16 15" class="h-4 w-4 flex-none">
                            <path d="M7.197 0.549c.104-.322.405-.54.743-.54.339 0 .64.218.744.54l1.289 3.973c.038.117.113.219.213.291.099.072.22.111.344.111l4.176-.001c.339 0 .64.218.744.54.103.321-.01.668-.28.864l-3.378 2.453a.756.756 0 0 0-.213.29.743.743 0 0 0 0 .36l1.292 3.972c.103.322-.01.668-.28.864a.777.777 0 0 1-.9 0L8.27 11.73a.78.78 0 0 0-.345-.111.78.78 0 0 0-.344.111l-3.375 2.455a.778.778 0 0 1-.9 0c-.27-.196-.383-.542-.28-.864L4.318 9.35a.741.741 0 0 0 0 .36.752.752 0 0 0-.213-.29L.728 6.247c-.27-.196-.383-.543-.28-.864.104-.322.405-.54.744-.54l4.175.001a.78.78 0 0 0 .344-.111.754.754 0 0 0 .213-.291L7.197.549Z" fill="#F6BB06"/>
                        </svg>
                    @endfor
                </div>

                @if(filled($googleReviewsUrl))
                    <a href="{{ $googleReviewsUrl }}" target="_blank" rel="noopener noreferrer" class="ui-review-slider-brand ui-review-slider-summary-side">
                        <img src="{{ asset('images/google-logo.svg') }}" alt="Google" class="ui-review-slider-logo" loading="lazy" />
                    </a>
                @else
                    <span class="ui-review-slider-brand ui-review-slider-summary-side">
                        <img src="{{ asset('images/google-logo.svg') }}" alt="Google" class="ui-review-slider-logo" loading="lazy" />
                    </span>
                @endif
            </div>

            <div class="inline-flex justify-end space-x-2" x-show="hasOverflow" x-cloak>
                <button
                  class="flex size-8 items-center rounded-full bg-accent-600 text-white transition-colors duration-200 hover:bg-accent-500 active:bg-accent-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                  :class="{ 'opacity-50 ': atBeginning }"
                  :aria-disabled="atBeginning"
                  :tabindex="atBeginning ? -1 : 0"
                  x-on:click="prev"
                  tabindex="0"
                  ><span aria-hidden="true" class="mx-auto"> &larr; </span><span
                    class="sr-only">Skip to previous slide page</span
                  ></button
                >
                <button
                  class="flex size-8 items-center rounded-full bg-accent-600 text-white transition-colors duration-200 hover:bg-accent-500 active:bg-accent-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                  :class="{ 'opacity-50 ': atEnd }"
                  :aria-disabled="atEnd"
                  :tabindex="atEnd ? -1 : 0"
                  x-on:click="next"
                  tabindex="0"
                  ><span aria-hidden="true" class="mx-auto"> &rarr; </span><span
                    class="sr-only">Skip to next slide page</span
                  ></button>
            </div>
        </div>

        <ul
            class="mt-6 flex w-full gap-4 overflow-x-scroll rounded-2xl no-scrollbar snap-x snap-mandatory"
            role="listbox"
            aria-labelledby="carousel-content-label"
            tabindex="0"
            x-ref="slider"
            x-on:scroll.debounce.75ms="update"
        >

            @foreach($reviews as $review)
            @php
                $name = trim((string) $review->name);
                $displayName = mb_convert_case($name !== '' ? $name : 'Anonimowa opinia', MB_CASE_TITLE, 'UTF-8');
                $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
                $avatarColor = $resolveAvatarColor($review->color);
                $avatarUrl = trim((string) ($review->avatar_url ?? ''));
            @endphp

            <li
                class="w-full shrink-0 snap-start basis-full md:basis-[calc((100%-1rem)/2)] xl:basis-[calc((100%-2rem)/3)]"
                role="option"
            >
                <figure
                    class="ui-review-card {{ $surfaceClass }} h-full sm:p-8 lg:p-10"
                    x-data="{
                        expanded: false,
                        expandable: false,
                        update() {
                            if (! this.$refs.body) {
                                return
                            }

                            if (this.expanded) {
                                this.expandable = true
                                return
                            }

                            this.expandable = this.$refs.body.scrollHeight > this.$refs.body.clientHeight + 2
                        },
                    }"
                    x-init="$nextTick(() => update())"
                    x-on:resize.window.debounce.120ms="update()"
                >
                    <div class="ui-review-meta">
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            @if($avatarUrl !== '')
                                <div class="ui-review-avatar ui-review-avatar--image">
                                    <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="ui-review-avatar-image" loading="lazy" referrerpolicy="no-referrer" />
                                </div>
                            @else
                                <div class="ui-review-avatar" style="background-color: {{ $avatarColor }}">
                                    {{ $initial }}
                                </div>
                            @endif

                            <div class="min-w-0">
                                <div class="ui-review-author">{{ $displayName }}</div>
                                <div class="ui-review-count">{{ ile_opinii($review->amount) }}</div>
                            </div>
                        </div>

                        <img src="{{ asset('images/google-icon.svg') }}" alt="Google" class="ui-review-provider" loading="lazy" />
                    </div>

                    <div class="ui-review-stars">
                        @for($i = 0; $i < 5; $i++)
                            <svg viewBox="0 0 16 15" aria-hidden="true" class="h-4 w-4 flex-none">
                                <path d="M7.197 0.549c.104-.322.405-.54.743-.54.339 0 .64.218.744.54l1.289 3.973c.038.117.113.219.213.291.099.072.22.111.344.111l4.176-.001c.339 0 .64.218.744.54.103.321-.01.668-.28.864l-3.378 2.453a.756.756 0 0 0-.213.29.743.743 0 0 0 0 .36l1.292 3.972c.103.322-.01.668-.28.864a.777.777 0 0 1-.9 0L8.27 11.73a.78.78 0 0 0-.345-.111.78.78 0 0 0-.344.111l-3.375 2.455a.778.778 0 0 1-.9 0c-.27-.196-.383-.542-.28-.864L4.318 9.35a.741.741 0 0 0 0-.36.752.752 0 0 0-.213-.29L.728 6.247c-.27-.196-.383-.543-.28-.864.104-.322.405-.54.744-.54l4.175.001a.78.78 0 0 0 .344-.111.754.754 0 0 0 .213-.291L7.197.549Z" fill="#F6BB06"/>
                            </svg>
                        @endfor
                        <img src="{{ asset('images/ti-verified.svg') }}" alt="Zweryfikowana opinia" class="ui-review-verified" loading="lazy" />

                        <time datetime="{{ optional($review->date)->format('Y-m-d') }}" class="ui-review-date">
                            {{ time_ago($review->date) }}
                        </time>
                    </div>

                    @if(trim((string) $review->review) !== '')
                        <blockquote
                            x-ref="body"
                            class="ui-review-body transition-all duration-300"
                            :class="expanded ? '' : 'ui-review-body-clamped'"
                        >{{ mos(trim((string) $review->review)) }}</blockquote>

                        <button
                            x-cloak
                            x-show="expandable"
                            type="button"
                            class="ui-review-toggle"
                            x-on:click="expanded = ! expanded; $nextTick(() => update())"
                        >
                            <span x-text="expanded ? 'Zwiń opinię' : 'Czytaj dalej'"></span>
                        </button>
                    @endif
                </figure>
            </li>

            @endforeach

          </ul>
    </div>
</div>
