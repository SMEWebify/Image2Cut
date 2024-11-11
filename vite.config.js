import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css',
                'node_modules/lodash/lodash.min.js',
                'node_modules/dropzone/dist/dropzone-min.js'
            ],
            refresh: true,
        }),
    ],
});
