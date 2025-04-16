<x-filament-panels::page>
    <div class="grid grid-cols-3 gap-6">
        {{-- Main Thread (Left 2/3) --}}
        <div class="col-span-2">
            {{-- Ticket Description --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 mb-6">
                <div class="fi-section-content p-6">
                    <div class="flex items-start gap-x-4">
                        <div class="fi-avatar shrink-0">
                            @if($ticket->submitter?->avatar)
                                <img class="fi-avatar-image h-10 w-10 rounded-full" src="{{ $ticket->submitter->avatar }}" alt="{{ $ticket->submitter->name }}">
                            @else
                                <div class="fi-avatar-image flex h-10 w-10 rounded-full bg-gray-100 items-center justify-center">
                                    <span class="text-sm font-medium text-gray-600">
                                        {{ substr($ticket->submitter?->name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-x-2">
                                <p class="fi-text text-sm font-medium text-gray-950">
                                    {{ $ticket->submitter?->name ?? 'Unknown User' }}
                                </p>
                                <span class="fi-text text-sm text-gray-500">
                                    {{ $ticket->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="fi-text mt-2 text-sm text-gray-500 prose max-w-none">
                                {!! $ticket->description !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Comments Thread --}}
            @livewire(\Crumbls\HelpDesk\Filament\Resources\TicketResource\Widgets\TicketCommentsWidget::class, [
                'ticket' => $ticket
            ])
        </div>

        {{-- Metadata Sidebar (Right 1/3) --}}
        <div class="space-y-6">
            {{-- Status & Priority --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5">
                <div class="fi-section-content p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="fi-text text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <x-filament::badge :color="$ticket->ticketStatus?->color_name">
                                    {{ $ticket->ticketStatus?->title }}
                                </x-filament::badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="fi-text text-sm font-medium text-gray-500">Priority</dt>
                            <dd class="mt-1">
                                <x-filament::badge :color="$ticket->priority?->color_name">
                                    {{ $ticket->priority?->title }}
                                </x-filament::badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="fi-text text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1">
                                <x-filament::badge :color="$ticket->ticketType?->color_name">
                                    {{ $ticket->ticketType?->title }}
                                </x-filament::badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="fi-text text-sm font-medium text-gray-500">Department</dt>
                            <dd class="mt-1">
                                <x-filament::badge :color="$ticket->department?->color_name">
                                    {{ $ticket->department?->title }}
                                </x-filament::badge>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Assignees --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5">
                <div class="fi-section-content p-6">
                    <h3 class="fi-text text-sm font-medium text-gray-950 mb-4">Assignees</h3>
                    @if($ticket->assignees->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($ticket->assignees as $assignee)
                                <div class="flex items-center gap-x-3">
                                    <div class="fi-avatar shrink-0">
                                        @if($assignee->avatar)
                                            <img class="fi-avatar-image h-8 w-8 rounded-full" src="{{ $assignee->avatar }}" alt="{{ $assignee->name }}">
                                        @else
                                            <div class="fi-avatar-image flex h-8 w-8 rounded-full bg-gray-100 items-center justify-center">
                                                <span class="text-xs font-medium text-gray-600">
                                                    {{ substr($assignee->name, 0, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="fi-text text-sm text-gray-950">{{ $assignee->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="fi-text text-sm text-gray-500">No assignees</p>
                    @endif
                </div>
            </div>

            {{-- Watchers --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5">
                <div class="fi-section-content p-6">
                    <h3 class="fi-text text-sm font-medium text-gray-950 mb-4">Watchers</h3>
                    @if($ticket->watchers->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($ticket->watchers as $watcher)
                                <div class="flex items-center gap-x-3">
                                    <div class="fi-avatar shrink-0">
                                        @if($watcher->avatar)
                                            <img class="fi-avatar-image h-8 w-8 rounded-full" src="{{ $watcher->avatar }}" alt="{{ $watcher->name }}">
                                        @else
                                            <div class="fi-avatar-image flex h-8 w-8 rounded-full bg-gray-100 items-center justify-center">
                                                <span class="text-xs font-medium text-gray-600">
                                                    {{ substr($watcher->name, 0, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="fi-text text-sm text-gray-950">{{ $watcher->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="fi-text text-sm text-gray-500">No watchers</p>
                    @endif
                </div>
            </div>

            {{-- Dates --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5">
                <div class="fi-section-content p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="fi-text text-sm font-medium text-gray-500">Created</dt>
                            <dd class="fi-text text-sm text-gray-950 mt-1">{{ $ticket->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        @if($ticket->due_at)
                            <div>
                                <dt class="fi-text text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="fi-text text-sm text-gray-950 mt-1">{{ $ticket->due_at->format('M j, Y g:i A') }}</dd>
                            </div>
                        @endif
                        @if($ticket->closed_at)
                            <div>
                                <dt class="fi-text text-sm font-medium text-gray-500">Closed</dt>
                                <dd class="fi-text text-sm text-gray-950 mt-1">{{ $ticket->closed_at->format('M j, Y g:i A') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
