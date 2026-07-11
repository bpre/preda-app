import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/views/themes/duo/assets/css/duo.css',
                'resources/views/themes/duo/assets/js/duo.js',
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
                'resources/js/admin.js',
                'resources/css/filament/kancelaria/theme.css',
                'resources/css/filament/portal/theme.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
