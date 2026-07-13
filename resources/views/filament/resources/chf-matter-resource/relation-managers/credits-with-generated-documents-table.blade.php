@php
    $visibleColumns = collect($this->getTable()->getVisibleColumns())->values();
    $records = $this->getTableRecords();
    $defaultRecordActions = $this->getTable()->getRecordActions();
    $hasRecordActions = count($defaultRecordActions) > 0;
    $colspan = $visibleColumns->count() + ($hasRecordActions ? 1 : 0);
@endphp

<style>
    .preda-credit-documents-table {
        width: 100%;
        border-collapse: collapse;
    }

    .preda-credit-documents-table .fi-ta-header-cell,
    .preda-credit-documents-table .fi-ta-cell {
        padding: 1rem 1.5rem;
        text-align: left;
        vertical-align: middle;
    }

    .preda-credit-documents-table .fi-ta-header-cell {
        background: rgb(249 250 251);
        color: rgb(17 24 39);
        font-weight: 700;
        border-bottom: 1px solid rgb(229 231 235);
    }

    .preda-credit-documents-table__sort {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font: inherit;
        color: inherit;
        cursor: pointer;
    }

    .preda-credit-documents-table__sort-indicator {
        color: rgb(107 114 128);
        font-size: .75rem;
        line-height: 1;
    }

    .preda-credit-documents-table__record-row {
        border-bottom: 1px solid rgb(229 231 235);
    }

    .preda-credit-documents-table__documents-row {
        border-bottom: 1px solid rgb(229 231 235);
    }

    .preda-credit-documents-table__documents-cell {
        padding: 0;
        background: rgb(249 250 251);
    }
</style>

<div style="width: 100%; overflow-x: auto;">
    <table class="fi-ta-table preda-credit-documents-table">
        <thead>
            <tr>
                @foreach ($visibleColumns as $column)
                    @php
                        $columnName = $column->getName();
                        $isColumnSortable = $column->isSortable();
                        $isColumnActivelySorted = $this->getTableSortColumn() === $columnName;
                    @endphp

                    <th
                        {{
                            $column->getExtraHeaderAttributeBag()
                                ->class([
                                    'fi-ta-header-cell',
                                    'fi-ta-header-cell-' . str($columnName)->camel()->kebab(),
                                ])
                        }}
                    >
                        @if ($isColumnSortable)
                            <button
                                type="button"
                                wire:click="sortTable('{{ $columnName }}')"
                                wire:loading.attr="disabled"
                                wire:target="sortTable('{{ $columnName }}')"
                                class="preda-credit-documents-table__sort"
                            >
                                <span>{{ $column->getLabel() }}</span>

                                @if ($isColumnActivelySorted)
                                    <span class="preda-credit-documents-table__sort-indicator">
                                        {{ $this->getTableSortDirection() === 'desc' ? 'desc' : 'asc' }}
                                    </span>
                                @endif
                            </button>
                        @else
                            {{ $column->getLabel() }}
                        @endif
                    </th>
                @endforeach

                @if ($hasRecordActions)
                    <th class="fi-ta-actions-header-cell fi-ta-empty-header-cell"></th>
                @endif
            </tr>
        </thead>

        <tbody>
            @foreach ($records as $record)
                @php
                    $recordKey = $this->getTableRecordKey($record);
                    $recordActions = array_reduce(
                        $defaultRecordActions,
                        function (array $carry, $action) use ($record): array {
                            $action = $action->getClone();

                            if (! $action instanceof \Filament\Actions\BulkAction) {
                                $action->record($record);
                            }

                            if ($action->isHidden()) {
                                return $carry;
                            }

                            $carry[] = $action;

                            return $carry;
                        },
                        initial: [],
                    );
                    $documents = $record->generatedDocuments ?? collect();
                @endphp

                <tr
                    wire:key="{{ $this->getId() }}.credit-record.{{ $recordKey }}"
                    class="fi-ta-row preda-credit-documents-table__record-row"
                >
                    @foreach ($visibleColumns as $column)
                        @php
                            $column->record($record);
                            $column->rowLoop($loop->parent);
                            $column->recordKey($recordKey);
                        @endphp

                        <td
                            {{
                                $column->getExtraCellAttributeBag()->class([
                                    'fi-ta-cell',
                                    'fi-ta-cell-' . str($column->getName())->camel()->kebab(),
                                ])
                            }}
                        >
                            <div class="fi-ta-col">
                                {{ $column }}
                            </div>
                        </td>
                    @endforeach

                    @if ($hasRecordActions)
                        <td class="fi-ta-cell">
                            <div class="fi-ta-actions">
                                @foreach ($recordActions as $action)
                                    {{ $action }}
                                @endforeach
                            </div>
                        </td>
                    @endif
                </tr>

                @if ($documents->isNotEmpty())
                    <tr
                        wire:key="{{ $this->getId() }}.credit-record.{{ $recordKey }}.generated-documents"
                        class="preda-credit-documents-table__documents-row"
                    >
                        <td colspan="{{ $colspan }}" class="preda-credit-documents-table__documents-cell">
                            @include('filament.resources.chf-matter-resource.relation-managers.generated-documents-table', [
                                'documents' => $documents,
                            ])
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
