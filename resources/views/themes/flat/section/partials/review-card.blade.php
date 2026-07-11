@php
    $name = trim((string) $review->name);
    $displayName = mb_convert_case($name !== '' ? $name : 'Anonimowa opinia', MB_CASE_TITLE, 'UTF-8');
    $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
    $avatarColor = $resolveAvatarColor($review->color);
    $avatarUrl = trim((string) ($review->avatar_url ?? ''));
@endphp

<article
    class="ui-review-card {{ $alternate ? 'ui-review-card-light' : 'ui-review-card-muted' }}"
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
                <div class="ui-review-author" data-nosnippet>{{ $displayName }}</div>
                <time datetime="{{ optional($review->date)->format('Y-m-d') }}" class="ui-review-date ui-review-date-inline">
                    {{ time_ago($review->date) }}
                </time>
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
</article>
