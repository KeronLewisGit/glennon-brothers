import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

// Fonts are downloaded at build time and served from /build, so visitors never hit a font CDN.
// The group's own pairing, from its Teamtailor careers site: Montserrat (headings, 600) and Inter
// (body). Montserrat is also the closest open match to the geometric GLENNON wordmark; the old
// glennonbrothers.ie sets everything in Verdana, which stays in the body fallback stack.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Montserrat', {
                    alias: 'display',
                    variable: '--ff-display',
                    weights: [500, 600, 700],
                    styles: ['normal'],
                    subsets: ['latin'],
                    preload: [{ weight: 600 }],
                    fallbacks: ['Century Gothic', 'Avenir Next', 'sans-serif'],
                }),
                bunny('Inter', {
                    alias: 'sans',
                    variable: '--ff-sans',
                    weights: [400, 500, 600, 700],
                    styles: ['normal'],
                    subsets: ['latin'],
                    preload: [{ weight: 400 }, { weight: 600 }],
                    fallbacks: ['Verdana', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: { watch: { ignored: ['**/storage/framework/views/**'] } },
});
