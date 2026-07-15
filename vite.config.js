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
                'resources/js/restore-confirm.js',
                'resources/js/delete-modal.js',
                'resources/js/register-sw.js',
                'resources/js/push-notifications.js',
                'resources/css/pages/assets.css',
                'resources/css/pages/asset-detail.css',
                'resources/css/pages/asset-form.css',
                'resources/css/pages/entries.css',
                'resources/css/pages/event-detail.css',
                'resources/css/pages/templates.css',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: "0.0.0.0",
        hmr: { host: "orion.sole-likert.ts.net" },
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        assetsInlineLimit: 4096,
    },
});
