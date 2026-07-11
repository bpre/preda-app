@props([
    'to' => 782
])

<div class="absolute hidden lg:block top-[750px] justify-center left-0 w-screen">
    <div class="flex justify-center animate-bounce">
        <button  @click="scrollTo({ top: {{ $to }}, left: 100, behavior: 'smooth'});" aria-label="Przewiń w dół">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>
</div>
