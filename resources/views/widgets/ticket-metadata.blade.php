{{-- Status & Priority --}}
<div class="fi-section space-y-6">
    <x-filament::section>
        <x-slot name="heading">Details</x-slot>

        <dl class="space-y-4">
            <div>
                <dt class="fi-text text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                    <x-filament::badge :color="$record->status?->color_name">
                        {{ $record->status?->title }}
                    </x-filament::badge>
                </dd>
            </div>
            <div>
                <dt class="fi-text text-sm font-medium text-gray-500">Priority</dt>
                <dd class="mt-1">
                    <x-filament::badge :color="$record->priority?->color_name">
                        {{ $record->priority?->title }}
                    </x-filament::badge>
                </dd>
            </div>
            <div>
                <dt class="fi-text text-sm font-medium text-gray-500">Type</dt>
                <dd class="mt-1">
                    <x-filament::badge :color="$record->type?->color_name">
                        {{ $record->type?->title }}
                    </x-filament::badge>
                </dd>
            </div>
            <div>
                <dt class="fi-text text-sm font-medium text-gray-500">Department</dt>
                <dd class="mt-1">
                    <x-filament::badge :color="$record->department?->color_name">
                        {{ $record->department?->title }}
                    </x-filament::badge>
                </dd>
            </div>
        </dl>
    </x-filament::section>

    {{-- Assignees --}}
    <x-filament::section>
        <x-slot name="heading">Assignees</x-slot>

        @if($record->assignees->isNotEmpty())
            <div class="space-y-3">
                @foreach($record->assignees as $assignee)
                    <div class="flex items-center gap-x-3">
                        <x-filament::avatar
                            :src="$assignee->avatar"
                            :alt="$assignee->name"
                            size="md" />
                        <span class="fi-text text-sm text-gray-950">{{ $assignee->name }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="fi-text text-sm text-gray-500">No assignees</p>
        @endif
    </x-filament::section>

    {{-- Watchers --}}
    <x-filament::section>
        <x-slot name="heading">Watchers</x-slot>

        @if($record->watchers->isNotEmpty())
            <div class="space-y-3">
                @foreach($record->watchers as $watcher)
                    <div class="flex items-center gap-x-3">
                        <x-filament::avatar
                            :src="$watcher->avatar"
                            :alt="$watcher->name"
                            size="md" />
                        <span class="fi-text text-sm text-gray-950">{{ $watcher->name }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="fi-text text-sm text-gray-500">No watchers</p>
        @endif
    </x-filament::section>

    {{-- Dates --}}
    <x-filament::section>
        <x-slot name="heading">Dates</x-slot>

        <dl class="space-y-4">
            @if($record->created_at)
                <div>
                    <dt class="fi-text text-sm font-medium text-gray-500">Created</dt>
                    <dd class="fi-text text-sm text-gray-950 mt-1">{{ $record->created_at->format('M j, Y g:i A') }}</dd>
                </div>
            @endif
            @if($record->due_at)
                <div>
                    <dt class="fi-text text-sm font-medium text-gray-500">Due Date</dt>
                    <dd class="fi-text text-sm text-gray-950 mt-1">{{ $record->due_at->format('M j, Y g:i A') }}</dd>
                </div>
            @endif
            @if($record->closed_at)
                <div>
                    <dt class="fi-text text-sm font-medium text-gray-500">Closed</dt>
                    <dd class="fi-text text-sm text-gray-950 mt-1">{{ $record->closed_at->format('M j, Y g:i A') }}</dd>
                </div>
            @endif
        </dl>
    </x-filament::section>
</div>
