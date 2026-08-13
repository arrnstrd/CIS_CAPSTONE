import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/login.css', 'resources/css/qr-station.css', 'resources/css/qr-generation.css', 'resources/js/app.js', 'resources/js/qr-station.js', 'resources/js/qr-generation.js'],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
