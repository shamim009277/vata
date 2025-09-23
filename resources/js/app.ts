//import '../css/app.css';
import '../../public/backend/assets/js/jquery.min.js';
import 'boxicons/css/boxicons.min.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import 'vue3-toastify/dist/index.css'
import Vue3Toastify from 'vue3-toastify'

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })

        vueApp.use(plugin)
        vueApp.use(ZiggyVue)
        vueApp.use(Vue3Toastify, {
            autoClose: 3000,
            position: "top-right",
            theme: 'colored',
            pauseOnHover: true,
            closeOnClick: true,
            hideProgressBar: false,
            draggable: true,
            newestOnTop: true,
        })

        // ✅ Global translation helper
        vueApp.config.globalProperties.$t = (key) => {
            const keys = key.split('.')
            let value = props.initialPage.props.translations
            for (const k of keys) {
                value = value[k]
                if (!value) break
            }
            return value || key
        }

        vueApp.mount(el);
    },
});

// This will set light / dark mode on page load...
initializeTheme();
