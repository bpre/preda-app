<div>
    <style>
        .task-comments-modal {
            display: grid;
            gap: 1rem;
            grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
            align-items: start;
        }

        .task-comments-modal__details {
            display: grid;
            gap: .75rem;
            border: 1px solid rgb(229 231 235);
            border-radius: .75rem;
            padding: 1rem;
        }

        .task-comments-modal__meta {
            display: grid;
            gap: .75rem;
        }

        @media (max-width: 768px) {
            .task-comments-modal {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>

    <div class="task-comments-modal">
        <div class="task-comments-modal__details">
            <div>
                <div style="font-size: .75rem; color: rgb(107 114 128);">Zadanie</div>
                <div style="font-weight: 600;">{{ $task->label }}</div>
            </div>

            @if ($task->matter)
                <div>
                    <div style="font-size: .75rem; color: rgb(107 114 128);">Sprawa</div>
                    <div>{{ $task->matter->label }}</div>
                </div>
            @endif

            <div class="task-comments-modal__meta">
                <div>
                    <div style="font-size: .75rem; color: rgb(107 114 128);">Priorytet</div>
                    <div>
                        @switch((string) $task->priority)
                            @case('1')
                                niski
                                @break

                            @case('3')
                                wysoki
                                @break

                            @default
                                średni
                        @endswitch
                    </div>
                </div>

                <div>
                    <div style="font-size: .75rem; color: rgb(107 114 128);">Dodane przez</div>
                    <div>{{ $task->task_creator?->name ?? '-' }}</div>
                </div>

                <div>
                    <div style="font-size: .75rem; color: rgb(107 114 128);">Zadanie dla</div>
                    <div>{{ $task->is_private ? 'własne' : ($task->assignee?->name ?? '-') }}</div>
                </div>

                <div>
                    <div style="font-size: .75rem; color: rgb(107 114 128);">Utworzono</div>
                    <div>{{ $task->created_at?->format('Y-m-d H:i') ?? '-' }}</div>
                </div>

                <div>
                    <div style="font-size: .75rem; color: rgb(107 114 128);">Wykonano</div>
                    <div>{{ $task->done_at?->format('Y-m-d H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>

        <livewire:task-comments :task="$task" :key="'task-comments-' . $task->getKey()" />
    </div>
</div>
