<div style="display: grid; gap: 1rem;">
    <style>
        .task-comment-content {
            white-space: pre-line;
        }

        .task-comment-content a {
            color: rgb(37 99 235);
            font-weight: 600;
            text-decoration: underline;
            text-decoration-thickness: 1.5px;
            text-underline-offset: 2px;
        }

        .task-comment-content a:hover {
            color: rgb(29 78 216);
        }
    </style>

    <div style="display: grid; gap: .75rem;">
        {{ $this->form }}

        <div style="display: flex; justify-content: flex-end;">
            <x-filament::button
                type="button"
                icon="heroicon-o-paper-airplane"
                wire:click.prevent="addComment"
                wire:target="addComment"
            >
                Dodaj komentarz
            </x-filament::button>
        </div>
    </div>

    <div style="display: grid; gap: .75rem;">
        <div style="font-size: .875rem; font-weight: 600;">
            Komentarze
        </div>

        @forelse ($comments as $comment)
            <div
                wire:key="task-comment-{{ $comment->getKey() }}"
                style="display: grid; gap: .5rem; border: 1px solid rgb(229 231 235); border-radius: .75rem; padding: 1rem;"
            >
                <div style="display: flex; flex-wrap: wrap; gap: .5rem; align-items: baseline; justify-content: space-between;">
                    <div style="font-weight: 600;">
                        {{ $comment->user?->name ?? '-' }}
                    </div>

                    <div style="font-size: .75rem; color: rgb(107 114 128);">
                        {{ $comment->created_at?->format('Y-m-d H:i') ?? '-' }}
                    </div>
                </div>

                <div class="task-comment-content">
                    {!! str($comment->comment)->sanitizeHtml() !!}
                </div>
            </div>
        @empty
            <div style="border: 1px dashed rgb(209 213 219); border-radius: .75rem; padding: 1rem; color: rgb(107 114 128);">
                Brak komentarzy.
            </div>
        @endforelse
    </div>
</div>
