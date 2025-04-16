<div class="fi-section space-y-6">
    {{-- Comments List --}}
    @foreach($record->comments()->with('user')->orderBy('created_at', 'asc')->get() as $comment)
        <div @class([
            'fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5',
            'border-l-4 border-warning-500' => $comment->is_private,
        ])>
            <div class="fi-section-content p-6">
                <div class="flex items-start gap-x-4">
                    <div class="fi-avatar shrink-0">
                        @if($comment->user?->avatar)
                            <img class="fi-avatar-image h-10 w-10 rounded-full" src="{{ $comment->user->avatar }}" alt="{{ $comment->user->name }}">
                        @else
                            <div class="fi-avatar-image flex h-10 w-10 rounded-full bg-gray-100 items-center justify-center">
                                <span class="text-sm font-medium text-gray-600">
                                    {{ substr($comment->user?->name ?? 'U', 0, 1) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-x-2">
                            <p class="fi-text text-sm font-medium text-gray-950">
                                {{ $comment->user?->name ?? 'Unknown User' }}
                            </p>
                            <span class="fi-text text-sm text-gray-500">
                                {{ $comment->created_at->diffForHumans() }}
                            </span>
                            @if($comment->is_private)
                                <span class="fi-badge rounded-lg bg-warning-50 px-2 py-1 text-xs font-medium text-warning-600">
                                    Internal Note
                                </span>
                            @endif
                            @if($comment->is_resolution)
                                <span class="fi-badge rounded-lg bg-success-50 px-2 py-1 text-xs font-medium text-success-600">
                                    Resolution
                                </span>
                            @endif
                        </div>
                        <div class="fi-text mt-2 text-sm text-gray-500 prose max-w-none">
                            {!! $comment->content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if($record->comments->isEmpty())
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5">
            <div class="fi-section-content p-6 text-center">
                <h3 class="fi-text text-sm font-medium text-gray-950">No comments yet</h3>
                <p class="fi-text mt-1 text-sm text-gray-500">Get the conversation started by adding a comment.</p>
            </div>
        </div>
    @endif

    {{-- Comment Form --}}
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5">
        <div class="fi-section-content p-6">
            <form wire:submit="addComment">
                {{ $this->form }}

                <div class="mt-4 flex justify-end">
                    <x-filament::button type="submit">
                        Add Comment
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</div>
