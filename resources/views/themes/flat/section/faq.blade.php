@php($firstFaq = method_exists($faqs, 'first') ? $faqs->first() : collect($faqs)->first())
@php($firstFaqId = data_get($firstFaq, 'id'))

<div class="space-y-4" x-data="{ openItem: @js($firstFaqId) }">
    @foreach($faqs as $faq)
        <div class="ui-accordion-card">
            <button
                class="ui-accordion-button"
                @click="openItem = openItem === {{ $faq->id }} ? null : {{ $faq->id }}"
                :aria-expanded="openItem === {{ $faq->id }}"
            >
                <span class="ui-accordion-title">
                    {!! $faq->question !!}
                </span>
                <svg
                    class="ui-accordion-icon"
                    :class="{ 'rotate-180': openItem === {{ $faq->id }} }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div
                :aria-hidden="openItem !== {{ $faq->id }}"
                :inert="openItem !== {{ $faq->id }}"
                :class="{ 'ui-accordion-panel--open': openItem === {{ $faq->id }} }"
                class="ui-accordion-panel"
            >
                <div class="ui-accordion-body prose prose-slate">
                    {!! mos($faq->answer) !!}
                </div>
            </div>
        </div>
    @endforeach
</div>
