import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    define: {
        // Provide fallback values for VITE_REVERB_* variables at build time
        // These will be overridden by actual environment variables at runtime in development
        'import.meta.env.VITE_REVERB_APP_KEY': JSON.stringify(process.env.VITE_REVERB_APP_KEY || ''),
        'import.meta.env.VITE_REVERB_HOST': JSON.stringify(process.env.VITE_REVERB_HOST || 'localhost'),
        'import.meta.env.VITE_REVERB_PORT': JSON.stringify(process.env.VITE_REVERB_PORT || '8080'),
        'import.meta.env.VITE_REVERB_SCHEME': JSON.stringify(process.env.VITE_REVERB_SCHEME || 'http'),
    },
});