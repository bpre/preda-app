@php($firstFaq = method_exists($faqs, 'first') ? $faqs->first() : collect($faqs)->first())
@php($firstFaqId = data_get($firstFaq, 'id'))
@php($accordionCardClasses = 'overflow-hidden rounded-[1.25rem] border border-primary-200 bg-white')
@php($accordionButtonClasses = 'flex w-full items-center justify-between bg-primary-50 px-6 py-4 text-left transition-colors duration-200 hover:bg-primary-100 focus-visible:bg-primary-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600')
@php($accordionTitleClasses = 'text-lg font-semibold text-primary-900')
@php($accordionIconClasses = 'h-5 w-5 text-secondary-500 transition-transform duration-200')
@php($accordionBodyClasses = 'border-t border-primary-200 px-6 py-4 prose prose-slate')

<div class="space-y-4" x-data="{ openItem: @js($firstFaqId) }">
    @foreach($faqs as $faq)
        <div class="{{ $accordionCardClasses }}">
            <button
                class="{{ $accordionButtonClasses }}"
                @click="openItem = openItem === {{ $faq->id }} ? null : {{ $faq->id }}"
                :aria-expanded="openItem === {{ $faq->id }}"
            >
                <span class="{{ $accordionTitleClasses }}">
                    {!! $faq->question !!}
                </span>
                <svg
                    class="{{ $accordionIconClasses }}"
                    :class="{ 'rotate-180': openItem === {{ $faq->id }} }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div
                x-cloak
                x-show="openItem === {{ $faq->id }}"
                x-collapse
                :aria-hidden="openItem !== {{ $faq->id }}"
                :inert="openItem !== {{ $faq->id }}"
                class="overflow-hidden bg-white"
            >
                <div class="{{ $accordionBodyClasses }}">
                    {!! mos($faq->answer) !!}
                </div>
            </div>
        </div>
    @endforeach
</div>
