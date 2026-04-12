import {createRouter, createWebHistory} from "vue-router";

const routes = [
    {
        path: '/',
        name: 'Dashboard',
        component: () => import('../pages/Dashboard.vue'),
        meta: {
            icon: 'bi-speedometer2',
            label: 'Dashboard',
            showInSidebar: true
        }
    },
    {
        path: '/assets',
        name: 'Assets',
        component: () => import('../pages/Assets.vue'),
        meta: {
            icon: 'bi-wallet2',
            label: 'Assets',
            showInSidebar: true
        }
    },
    {
        path: '/entries',
        name: 'Entries',
        component: () => import('../pages/Entries.vue'),
        meta: {
            icon: 'bi-journal-text',
            label: 'Entries',
            showInSidebar: true
        }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export const sidebarRoutes = routes
    .filter(route => route.meta?.showInSidebar)
    .map(route => ({
        to: route.path,
        icon: route.meta?.icon,
        label: route.meta?.label || route.name
    }));

export default router;
