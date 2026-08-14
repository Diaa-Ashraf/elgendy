@props([
    'notifications',
    'unreadNotificationsCount',
])

@php
    use Filament\Support\Enums\Alignment;

    $hasNotifications = $notifications->count();
    $isPaginated = $notifications instanceof \Illuminate\Contracts\Pagination\Paginator && $notifications->hasPages();
@endphp

<x-filament::modal
    :alignment="$hasNotifications ? null : Alignment::Center"
    close-button
    :description="$hasNotifications ? null : __('filament-notifications::database.modal.empty.description')"
    :heading="$hasNotifications ? null : __('filament-notifications::database.modal.empty.heading')"
    :icon="$hasNotifications ? null : 'heroicon-o-bell-slash'"
    :icon-alias="$hasNotifications ? null : 'notifications::database.modal.empty-state'"
    :icon-color="$hasNotifications ? null : 'gray'"
    id="database-notifications"
    slide-over
    :sticky-header="$hasNotifications"
    width="sm"
>
    @if ($hasNotifications)
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <x-filament-notifications::database.modal.heading
                    :unread-notifications-count="$unreadNotificationsCount"
                />

                <x-filament-notifications::database.modal.actions
                    :notifications="$notifications"
                    :unread-notifications-count="$unreadNotificationsCount"
                />
            </div>
        </x-slot>

        <div
            @class([
                '-mx-6 -mt-6 divide-y divide-gray-100 dark:divide-white/5',
                '-mb-6' => ! $isPaginated,
                'border-b border-gray-100 dark:border-white/5' => $isPaginated,
            ])
        >
            @foreach ($notifications as $notification)
                <div
                    @class([
                        'relative transition hover:bg-gray-50/80 dark:hover:bg-white/5',
                        'bg-slate-50/90 dark:bg-white/5 before:absolute before:start-0 before:top-0 before:h-full before:w-1 before:bg-indigo-600 dark:before:bg-indigo-500' => $notification->unread(),
                    ])
                >
                    {{ $this->getNotification($notification)->inline() }}
                </div>
            @endforeach
        </div>

        @if ($isPaginated)
            <x-slot name="footer">
                <x-filament::pagination :paginator="$notifications" />
            </x-slot>
        @endif
    @endif
</x-filament::modal>
