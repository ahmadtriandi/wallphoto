{{--
    Koneksi realtime ke Laravel Reverb.
    Alamatnya diambil dari .env (REVERB_HOST, REVERB_PORT, REVERB_SCHEME),
    jadi file ini sama untuk laptop dan VPS.
--}}
@php($reverb = config('broadcasting.connections.reverb'))
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
<script>
    try {
        window.EchoInstance = new Echo({
            broadcaster: 'pusher',
            key: @json($reverb['key'] ?? ''),
            wsHost: @json($reverb['options']['host'] ?? request()->getHost()),
            wsPort: @json((int) ($reverb['options']['port'] ?? 443)),
            wssPort: @json((int) ($reverb['options']['port'] ?? 443)),
            forceTLS: @json(($reverb['options']['scheme'] ?? 'https') === 'https'),
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
            cluster: 'mt1',
        });
    } catch (e) {
        console.warn('Realtime tidak aktif, wall memakai cek berkala tiap 5 detik:', e);
    }
</script>
