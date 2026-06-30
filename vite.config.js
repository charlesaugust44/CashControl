import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/theme.js',
                'resources/js/toast.js',
                'resources/js/dashboard.js',
                'resources/js/money-input.js',
                'resources/js/form-submit.js',
                'resources/js/bootstrap-dropdown.js',
                'resources/js/register-sw.js',
                'resources/css/pages/assets.css',
                'resources/css/pages/asset-detail.css',
                'resources/css/pages/asset-form.css',
                'resources/css/pages/entries.css',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        https: true,
        hmr: {
            host: '192.168.2.129',
            protocol: 'wss'
        },
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        assetsInlineLimit: 4096,
    },
});
