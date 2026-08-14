@php
    $notifications = $this->getNotifications();
    $unreadNotificationsCount = $this->getUnreadNotificationsCount();
@endphp

<div
    @if ($pollingInterval = $this->getPollingInterval())
        wire:poll.{{ $pollingInterval }}
    @endif
    class="flex"
>
    @if ($trigger = $this->getTrigger())
        <x-filament-notifications::database.trigger>
            {{ $trigger->with(['unreadNotificationsCount' => $unreadNotificationsCount]) }}
        </x-filament-notifications::database.trigger>
    @endif

    <x-filament-notifications::database.modal
        :notifications="$notifications"
        :unread-notifications-count="$unreadNotificationsCount"
    />

    @if ($broadcastChannel = $this->getBroadcastChannel())
        <x-filament-notifications::database.echo
            :channel="$broadcastChannel"
        />
    @endif
</div>

{{-- Real-Time Sound & Title Notification script --}}
<script>
    (function() {
        const key = 'filament_last_unread_count';
        const current = <?= \Illuminate\Support\Js::from($unreadNotificationsCount) ?>;
        const previous = parseInt(localStorage.getItem(key) || '0');

        if (current > previous && previous >= 0) {
            try {
                const soundUrl = '<?= asset("sounds/notification.wav") ?>';
                const audio = new Audio(soundUrl);
                audio.volume = 0.6;
                const promise = audio.play();
                if (promise !== undefined) {
                    promise.catch(() => {
                        // Fallback sound if local wav fails to play or user hasn't interacted
                        const fallbackAudio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                        fallbackAudio.volume = 0.5;
                        fallbackAudio.play().catch(() => {});
                    });
                }
            } catch(e) {}
        }

        // Update document title badge dynamically
        try {
            let baseTitle = document.title.replace(/^\(\d+\)\s*/, '');
            if (current > 0) {
                document.title = '(' + current + ') ' + baseTitle;
            } else {
                document.title = baseTitle;
            }
        } catch(e) {}

        localStorage.setItem(key, current);
    })();
</script>

