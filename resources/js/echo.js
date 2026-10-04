import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export function getReverbConfig() {
    const metaKey = document.querySelector('meta[name="reverb-app-key"]')?.content;
    const metaHost = document.querySelector('meta[name="reverb-host"]')?.content;
    const metaPort = document.querySelector('meta[name="reverb-port"]')?.content;
    const metaScheme = document.querySelector('meta[name="reverb-scheme"]')?.content;

    const key = (metaKey && metaKey.trim() !== '') ? metaKey.trim() : (import.meta.env.VITE_REVERB_APP_KEY || '');
    const host = (metaHost && metaHost.trim() !== '') ? metaHost.trim() : (import.meta.env.VITE_REVERB_HOST || window.location.hostname);
    const rawPort = (metaPort && metaPort.trim() !== '') ? metaPort.trim() : (import.meta.env.VITE_REVERB_PORT || (window.location.protocol === 'https:' ? '443' : '80'));
    const scheme = (metaScheme && metaScheme.trim() !== '') ? metaScheme.trim() : (import.meta.env.VITE_REVERB_SCHEME || (window.location.protocol === 'https:' ? 'https' : 'http'));

    const port = Number.parseInt(rawPort, 10) || (scheme === 'https' ? 443 : 80);

    return { key, host, port, scheme };
}

export function initEcho() {
    if (window.Echo) {
        return window.Echo;
    }

    const { key, host, port, scheme } = getReverbConfig();

    if (!key) {
        console.warn('[Reverb] App key is missing. Echo WebSocket connection skipped.');
        return null;
    }

    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: key,
            wsHost: host,
            wsPort: port,
            wssPort: port,
            forceTLS: scheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        return window.Echo;
    } catch (error) {
        console.error('[Reverb] Failed to initialize Echo:', error);
        return null;
    }
}
