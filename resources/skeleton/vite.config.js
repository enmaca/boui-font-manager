import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import fg from 'fast-glob';

const inputs = fg.sync([
    'resources/js/**/*.js',
    'resources/css/**/*.css',
    'resources/scss/**/*.scss',
]);

export default defineConfig({
    plugins: [
        laravel({
            input: inputs,
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@backoffice-ui': path.resolve(__dirname, 'vendor/uxmaltech/backoffice-ui/resources/js/boui/'),
            resources: path.resolve(__dirname, 'resources/'),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '0.0.0.0',
            clientPort: 5173,
        },
        fs: {
            // Permite servir archivos desde rutas adicionales dentro del contenedor
            allow: [
                '/app',
                '/app/resources',
                path.resolve(__dirname, 'vendor/uxmaltech/backoffice-ui/dist'),
                path.resolve(__dirname, 'resources'),
            ],
        },
    },
    emptyOutDir: true,
});
