import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/theme.js',
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
        hmr: {
            host: '192.168.2.129'
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
