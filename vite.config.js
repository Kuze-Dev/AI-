import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                /**
                 * Filament asset entrypoints
                 */
                'resources/css/filament/tenant/themes/mint.css', // Add this line
                'resources/css/filament/app.css',
                'resources/js/filament/app.js',

                /**
                 * Web asset entrypoints
                 */
                'resources/css/web/app.css',
                'resources/js/web/app.js'
            ],
            refresh: true,
        }),
    ],
});
