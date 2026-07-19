@php
    $messages = collect($messages);
@endphp

@if ($messages->isNotEmpty())
    <x-filament::section>
        <x-slot name="heading">
            Wysłane oferty
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <th class="py-2 pr-4 font-medium">Data</th>
                        <th class="py-2 pr-4 font-medium">Oferta</th>
                        <th class="py-2 pr-4 font-medium">Do</th>
                        <th class="py-2 pr-4 font-medium">Temat</th>
                        <th class="py-2 font-medium">Wysłał(a)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($messages as $message)
                        <tr class="border-b border-gray-100 last:border-b-0 dark:border-white/5">
                            <td class="py-2 pr-4 whitespace-nowrap">
                                {{ $message->sent_at?->format('Y-m-d H:i') ?? '-' }}
                            </td>
                            <td class="py-2 pr-4 font-medium text-gray-950 dark:text-white">
                                {{ $message->crm_workflow_offer_label ?: ($message->workflowOffer?->label ?: ($message->default_offer_filename ?: '-')) }}
                            </td>
                            <td class="py-2 pr-4">
                                {{ $message->recipient_email ?: '-' }}
                            </td>
                            <td class="py-2 pr-4">
                                {{ $message->subject ?: '-' }}
                            </td>
                            <td class="py-2">
                                {{ $message->sender?->name ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
@endif
