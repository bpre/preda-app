<style>
    .preda-generated-documents {
        width: 100%;
        padding: 1.25rem 2rem 1.5rem;
        border-top: 1px solid rgb(229 231 235);
        background: rgb(249 250 251);
    }

    .preda-generated-documents__title {
        margin: 0 0 1rem;
        font-size: .95rem;
        font-weight: 700;
        color: rgb(17 24 39);
    }

    .preda-generated-documents__empty {
        padding: 1rem 0;
        color: rgb(107 114 128);
        font-size: .875rem;
    }

    .preda-generated-documents__table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }

    .preda-generated-documents__table thead {
        border-bottom: 1px solid rgb(229 231 235);
        background: rgb(243 244 246);
    }

    .preda-generated-documents__table th,
    .preda-generated-documents__table td {
        padding: .5rem 1rem;
        text-align: left;
        vertical-align: middle;
    }

    .preda-generated-documents__table th:first-child,
    .preda-generated-documents__table td:first-child {
        padding-left: 1rem;
    }

    .preda-generated-documents__table th {
        padding-top: .35rem;
        padding-bottom: .35rem;
        color: rgb(75 85 99);
        font-weight: 600;
    }

    .preda-generated-documents__table tbody tr + tr {
        border-top: 1px solid rgb(243 244 246);
    }

    .preda-generated-documents__table tbody tr:nth-child(odd) {
        background: rgb(255 255 255);
    }

    .preda-generated-documents__table tbody tr:nth-child(even) {
        background: rgb(249 250 251);
    }

    .preda-generated-documents__filename {
        width: 100%;
        min-width: 24rem;
        padding: 0;
        border: 0;
        border-bottom: 1px solid transparent;
        border-radius: 0;
        background: transparent;
        color: rgb(17 24 39);
        box-shadow: none;
        font: inherit;
    }

    .preda-generated-documents__filename:focus {
        outline: none;
        border-bottom-color: transparent;
        box-shadow: none;
    }

    .preda-generated-documents__date {
        white-space: nowrap;
        color: rgb(55 65 81);
    }

    .preda-generated-documents__actions {
        white-space: nowrap;
    }

    .preda-generated-documents__actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .25rem .375rem;
        border: 0;
        background: transparent;
        font-weight: 600;
        cursor: pointer;
    }

    .preda-generated-documents__download {
        color: rgb(202 138 4);
    }

    .preda-generated-documents__download:hover {
        text-decoration: underline;
    }

    .preda-generated-documents__delete {
        color: rgb(220 38 38);
    }
</style>

<div class="preda-generated-documents">
    <div>
        <h3 class="preda-generated-documents__title">
            Wygenerowane dokumenty
        </h3>
    </div>

    @if ($documents->isEmpty())
        <div class="preda-generated-documents__empty">
            Nie wygenerowano jeszcze żadnych dokumentów.
        </div>
    @else
        <div style="width: 100%; overflow-x: auto;">
            <table class="preda-generated-documents__table">
                <thead>
                    <tr>
                        <th style="width: 70%;">Nazwa pliku</th>
                        <th>Data wygenerowania</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                        <tr wire:key="generated-document-{{ $document->getKey() }}">
                            <td>
                                <input
                                    type="text"
                                    value="{{ $document->filename }}"
                                    wire:change="renameGeneratedDocument('{{ $document->getKey() }}', $event.target.value)"
                                    class="preda-generated-documents__filename"
                                />
                            </td>
                            <td class="preda-generated-documents__date">
                                {{ $document->generated_at?->format('Y-m-d H:i') ?: '-' }}
                            </td>
                            <td class="preda-generated-documents__actions">
                                <a
                                    href="{{ route('matter-generated-documents.download', $document) }}"
                                    class="preda-generated-documents__download"
                                >
                                    Pobierz
                                </a>
                                {{ $this->getAction('confirmDeleteGeneratedDocument', false)(['documentId' => $document->getKey()]) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
