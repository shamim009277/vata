// import '../css/app.css';
import '../../public/backend/assets/js/jquery.min.js';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import 'vue3-toastify/dist/index.css';
import Vue3Toastify from 'vue3-toastify';

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
    resolve: (name) =>
        resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        /**
         * 🗓️ GLOBAL FUNCTION: Format date (DD-MM-YYYY)
         */
        app.config.globalProperties.$formatDate = (dateString: string): string => {
            if (!dateString) return '';

            // কিছু Laravel date string টাইমজোন ছাড়া আসে, তাই new Date() invalid হতে পারে
            const date = new Date(dateString.includes('T') ? dateString : dateString.replace(' ', 'T'));
            if (isNaN(date.getTime())) return dateString; // invalid হলে 그대로 ফিরিয়ে দাও

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}-${month}-${year}`;
        };

        /**
         * 🕒 GLOBAL FUNCTION: Format time in 12-hour format
         */
        app.config.globalProperties.$formatTime12Hour = (dateString: string): string => {
            if (!dateString) return '';

            const date = new Date(dateString.includes('T') ? dateString : dateString.replace(' ', 'T'));
            if (isNaN(date.getTime())) {
                // fallback: "2025-10-18 16:35:06" → শুধু সময় অংশ নাও
                const [_, timePart] = dateString.split(' ');
                return timePart || '';
            }

            let hours = date.getHours();
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            return `${hours}:${minutes}:${seconds} ${ampm}`;
        };

        app.use(plugin);
        app.use(ZiggyVue);
        app.use(Vue3Toastify, {
            autoClose: 3000,
            position: 'top-right',
            theme: 'colored',
            pauseOnHover: true,
            closeOnClick: true,
            hideProgressBar: false,
            draggable: true,
            newestOnTop: true,
        });

        app.mount(el);
    },
});

// This will set light / dark mode on page load...
initializeTheme();
