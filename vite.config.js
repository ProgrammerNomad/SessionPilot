import { defineConfig } from 'vite';

export default defineConfig({
    publicDir: false,
    build: {
        outDir: 'public',
        rollupOptions: {
            input: {
                admin: 'resources/js/admin.js',
            },
            output: {
                entryFileNames: 'js/[name].js',
                assetFileNames: (info) => {
                    if (info.name?.endsWith('.css')) return 'css/[name][extname]';
                    return 'js/[name][extname]';
                },
            },
        },
        manifest: true,
        emptyOutDir: false,
    },
});
