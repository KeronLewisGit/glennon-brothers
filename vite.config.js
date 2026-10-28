import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

// Fonts are downloaded at build time and served from /build, so visitors never hit a font CDN.
// Two families only: Zilla Slab, a slab serif with the weight of a stencilled pack mark, for
// headings, numerals and pack tickets; Instrument Sans, a clear, slightly condensed grotesque,
// for everything people read.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Zilla Slab', {
                    alias: 'display',
                    variable: '--ff-display',
                    weights: [500, 600, 700],
                    styles: ['normal'],
                    subsets: ['latin'],
                    preload: [{ weight: 700 }],
                    fallbacks: ['Rockwell', 'Georgia', 'serif'],
                }),
                bunny('Instrument Sans', {
                    alias: 'sans',
                    variable: '--ff-sans',
                    weights: [400, 500, 600, 700],
                    styles: ['normal'],
                    subsets: ['latin'],
                    preload: [{ weight: 400 }, { weight: 600 }],
                    fallbacks: ['ui-sans-serif', 'system-ui', 'sans-serif'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: { watch: { ignored: ['**/storage/framework/views/**'] } },
});
