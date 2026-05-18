import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build/',
        cssCodeSplit: true,
        rollupOptions: {
            output: {
                assetFileNames: (css) => {
                    if (css.name.split('.').pop() === 'css') {
                        return 'css/[name].min.css';
                    } else {
                        return 'icons/' + css.name;
                    }
                },
                entryFileNames: 'js/[name].js',
            },
        },
    },
    plugins: [
        laravel(
            {
                input: [
                    'resources/scss/bootstrap.scss',
                    'resources/scss/icons.scss',
                    'resources/scss/app.scss',
                    'resources/scss/custom.scss',
                ],
                refresh: true,
            }
        ),
        viteStaticCopy({
            targets: [
                {
                    src: 'resources/fonts/**/*',
                    dest: '',
                    rename: { stripBase: 1 },
                },
                {
                    src: 'resources/images/**/*',
                    dest: '',
                    rename: { stripBase: 1 },
                },
                {
                    src: 'resources/js/**/*',
                    dest: '',
                    rename: { stripBase: 1 },
                },
                {
                    src: 'resources/json/**/*',
                    dest: '',
                    rename: { stripBase: 1 },
                },
                {
                    src: 'resources/libs/**/*',
                    dest: '',
                    rename: { stripBase: 1 },
                },
            ],
        }),
    ],
});