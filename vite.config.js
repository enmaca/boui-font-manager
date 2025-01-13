import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    resolve: {
        alias: {
            '@backoffice-ui': path.resolve(__dirname, 'vendor/uxmaltech/backoffice-ui/resources/js/boui/'),
            resources: path.resolve(__dirname, 'resources/')
        }
    }
})
