import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Vue3Toastify, { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { createPinia } from 'pinia';
import { useUserStore } from './stores/userStore';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Create Pinia Store instance
        const pinia = createPinia();
        
        // Initialize the store and fetch roles and permissions
        const userStore = useUserStore(pinia);
        
        userStore.fetchRolesAndPermissions().then(() => {
        }).catch((error) => {
            console.error("Error fetching roles and permissions:", error);
        });

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Vue3Toastify, {autoClose: 2600,})
            .use(pinia)
            .mount(el);
    },
    progress: {
        color: '#1DC490',
    },
});
