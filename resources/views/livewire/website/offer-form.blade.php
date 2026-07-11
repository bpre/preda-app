<div>

    <script>
        window.addEventListener('gtm', () => {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'gtm.form_sent',
                'form_type': 'oferta'
            });
        });
    </script>

<div class="relative isolate mt-24 overflow-hidden bg-white py-24">
  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="mx-auto max-w-4xl text-center">
      <p class="text-base/7 font-semibold text-accent-600">Sprawdź koszty prowadzenia sprawy</p>
      <h1 class="mb-4 text-2xl sm:text-3xl md:text-5xl font-bold tracking-tight text-primary-700 my-2 max-w-full lg:text-6xl">Oferta</h1>
    </div>
    <p class="max-w-3xl prose prose-slate text-center mx-auto mt-12 mb-6">
        Oferujemy kilka wariantów prowadzenia sprawy o unieważnienie umowy kredytowej.<br>
        Warianty różnią się jedynie sposobem rozliczeń z kancelarią.<br>
        Zakres usług prawnych świadczonych w ramach każdego z wariantów jest taki sam.
    </p>
    <div class="isolate mx-auto grid max-w-md grid-cols-1 gap-y-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
      <div class="-mr-px flex flex-col justify-between rounded-[1.5rem] bg-white p-8 inset-ring inset-ring-primary-200 lg:mt-8 lg:rounded-r-none xl:p-10">
        <div>
          <div class="h-6"></div>
          <h3 id="tier-ekonomiczny" class="mt-4 text-center text-2xl/8 font-semibold text-primary-900">Wariant ekonomiczny</h3>
          <div class="text-center">
            <p class="mt-4 text-sm/6 text-secondary-600">Opłata wstępna:</p>
            <p class="text-4xl font-semibold tracking-tight text-primary-900">2.000 zł</p>
          </div>
          <ul role="list" class="mt-8 space-y-3 text-sm/6 text-secondary-600">
            <li class="flex gap-x-3">
              <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-6 w-5 flex-none text-accent-600">
                <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
              </svg>
              opłata wstępna płatna w 4 miesięcznych ratach po 500 zł
            </li>
            <li class="flex gap-x-3">
              <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-6 w-5 flex-none text-accent-600">
                <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
              </svg>
              premia za wygranie sprawy ustalana indywidualnie
            </li>
          </ul>
        </div>
        <a
            href="#form"
            aria-describedby="tier-ekonomiczny"
            wire:click="setVariant('ekonomiczny')"
            class="mt-8 inline-flex h-12 w-full items-center justify-center rounded-md border border-accent-200 px-3 text-center text-sm/6 font-semibold uppercase text-accent-600 shadow-xs transition-colors hover:border-accent-300 hover:bg-accent-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
        >
            Poznaj pełną ofertę
        </a>
      </div>

      <div class="flex flex-col justify-between rounded-[1.5rem] bg-white p-8 inset-ring inset-ring-primary-200 lg:mt-8 lg:rounded-none xl:p-10">
        <div>
          <div class="h-6"></div>
          <h3 id="tier-optymalny" class="mt-4 text-center text-2xl/8 font-semibold text-primary-900">Wariant optymalny</h3>
          <div class="text-center">
            <p class="mt-4 text-sm/6 text-secondary-600">Opłata wstępna:</p>
            <p class="text-4xl font-semibold tracking-tight text-primary-900">6.000 zł</p>
          </div>
          <ul role="list" class="mt-8 space-y-3 text-sm/6 text-secondary-600">
            <li class="flex gap-x-3">
              <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-6 w-5 flex-none text-accent-600">
                <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
              </svg>
              opłata wstępna płatna w 4 miesięcznych ratach po 1.500 zł
            </li>
            <li class="flex gap-x-3">
              <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-6 w-5 flex-none text-accent-600">
                <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
              </svg>
              premia za wygranie sprawy ustalana indywidualnie
            </li>
          </ul>
        </div>

        <a
            href="#form"
            aria-describedby="tier-optymalny"
            wire:click="setVariant('optymalny')"
            class="mt-8 inline-flex h-12 w-full items-center justify-center rounded-md border border-accent-200 px-3 text-center text-sm/6 font-semibold uppercase text-accent-600 shadow-xs transition-colors hover:border-accent-300 hover:bg-accent-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
        >
            Poznaj pełną ofertę
        </a>

      </div>

      <div class="-ml-px flex flex-col justify-between rounded-[1.5rem] bg-white p-8 inset-ring inset-ring-primary-200 lg:z-10 lg:rounded-bl-none xl:p-10">
        <div>
          <div class="h-8"></div>
          <div class="flex h-6 justify-center">
            <span class="inline-flex items-center rounded-full bg-emerald-600/10 px-2.5 py-1 text-xs font-semibold leading-5 text-emerald-700">
                Najczęściej wybierany
            </span>
          </div>
          <h3 id="tier-premium" class="mt-4 text-center text-2xl/8 font-semibold text-accent-600">Wariant premium</h3>
          <div class="text-center">
            <p class="mt-4 text-sm/6 text-secondary-600">Opłata wstępna:</p>
            <p class="text-4xl font-semibold tracking-tight text-primary-900">10.000 zł</p>
          </div>
          <ul role="list" class="mt-8 space-y-3 text-sm/6 text-secondary-600">
            <li class="flex gap-x-3">
              <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-6 w-5 flex-none text-accent-600">
                <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
              </svg>
              opłata wstępna płatna w 4 miesięcznych ratach po 2.500 zł
            </li>
            <li class="flex gap-x-3 font-bold">
              <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-6 w-5 flex-none text-accent-600">
                <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
              </svg>
              brak premii za wygranie sprawy
            </li>
          </ul>
        </div>
        <a
            href="#form"
            aria-describedby="tier-premium"
            wire:click="setVariant('premium')"
            class="mt-8 inline-flex h-12 w-full items-center justify-center rounded-md bg-accent-600 px-3 text-center text-sm/6 font-semibold uppercase text-white shadow-xs transition-colors hover:bg-accent-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
        >
            Poznaj pełną ofertę
        </a>
      </div>

    </div>

    <div class="mt-6 text-center text-sm/6 text-secondary-400">
        Oferta obowiązuje od 7.04.2026 r. do odwołania. Szczegółowe warunki współpracy ustalane są indywidualnie.
    </div>


  </div>
</div>







    <div class="mx-auto w-full max-w-3xl px-6 sm:px-8 lg:px-0 mt-24 py-32" id="form">

        @if($sent == true)

            <div class="mx-auto border border-primary-200 space-y-12 max-w-3xl animate-in fade-in zoom-in bg-primary-50 p-12 rounded-md flex gap-8">

                <div>
                    <x-icon name="heroicon-s-check-circle" class="w-24 fill-green-500" />
                </div>

                <div>

                    <h3 class="text-xl font-bold tracking-tight sm:text-4xl mt-2">
                        Dziękujemy za przesłanie zgłoszenia.
                    </h3>

                    <p class="text-lg sm:text-xl md:max-w-[50rem] mt-12">
                        Wkrótce otrzymasz spersonalizowaną ofertę.
                    </p>

                </div>

            </div>


        @else

            <h3 class="text-xl font-bold tracking-tight sm:text-4xl mb-12">
                Wypełnij formularz, aby otrzymać spersonalizowaną ofertę
            </h3>

            <div class="w-full">
                <form wire:submit.prevent="create" class="mx-auto w-full max-w-3xl" id="form-offer">
                    {{ $this->form }}
                </form>
            </div>

        @endif

    </div>

</div>
