{{--
    Livewire error-modal recovery.

    Livewire 4 derives its update endpoint from APP_KEY (/livewire-<hash>/update)
    and guards it with RequireLivewireHeaders (404 when the X-Livewire header is
    missing). After a back/forward-cache restore, a stale tab left open across a
    deploy, an expired session (419) or a stale component snapshot, a background
    Livewire request (e.g. the notifications poll) can fail — and Livewire then
    shows the raw error response in a dismissible modal ("403/404/Page expired").

    Instead of surfacing that modal, recover silently by reloading once to fetch
    a fresh page + endpoint. A short sessionStorage guard prevents reload loops
    if the failure is genuinely persistent.
--}}
<script>
    document.addEventListener('livewire:init', () => {
        if (! window.Livewire || typeof window.Livewire.hook !== 'function') return;

        window.Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if ([403, 404, 419].indexOf(status) === -1) return;

                // Suppress Livewire's error modal for these recoverable statuses.
                preventDefault();

                try {
                    var now = Date.now();
                    var last = parseInt(sessionStorage.getItem('lw-recover-at') || '0', 10);
                    // Reload at most once per 8s window to avoid an infinite loop
                    // if the error is persistent rather than a stale-state glitch.
                    if (now - last < 8000) return;
                    sessionStorage.setItem('lw-recover-at', String(now));
                } catch (e) {
                    // sessionStorage unavailable (private mode) — fall through to reload.
                }

                window.location.reload();
            });
        });
    });
</script>
