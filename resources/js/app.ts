// resources/js/app.ts
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h, Suspense } from 'vue';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        try {
            const page = await resolvePageComponent(
                `./pages/${name}.vue`,
                import.meta.glob<DefineComponent>('./pages/**/*.vue'),
            );
            return page;
        } catch (error) {
            console.error(`Error loading page ${name}:`, error);
            throw error;
        }
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () =>
                h(Suspense, null, {
                    default: () => h(App, props),
                    fallback: () =>
                        h('div', { class: 'loading' }, 'Loading...'),
                }),
        });

        app.use(plugin).mount(el);

        // Manejo global de errores
        app.config.errorHandler = (error, vm, info) => {
            console.error('Vue Error:', error);
            console.error('Component:', vm);
            console.error('Info:', info);
        };

        return app;
    },
    progress: {
        color: '#4B5563',
        delay: 250,
        includeCSS: true,
        showSpinner: true,
    },
});

// This will set light / dark mode on page load...
initializeTheme();
