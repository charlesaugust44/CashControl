import {createRouter, createWebHistory} from "vue-router";

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: () => import('../pages/Dashboard.vue'),
        meta: {
            icon: 'bi-speedometer2',
            label: 'Dashboard',
            showInSidebar: true
        }
    },
    {
        path: '/assets',
        name: 'asset-list',
        component: () => import('../pages/Assets.vue'),
        meta: {
            icon: 'bi-wallet2',
            label: 'Assets',
            showInSidebar: true
        }
    },
    {
        path: '/assets/form/:id?',
        name: 'asset-form',
        component: () => import('../pages/AssetForm.vue'),
        meta: {
            label: '',
        },
    },
    {
        path: '/entries',
        name: 'entry-list',
        component: () => import('../pages/Entries.vue'),
        meta: {
            icon: 'bi-journal-text',
            label: 'Entries',
            showInSidebar: true
        }
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('../pages/NotFound.vue'),
        meta: {
            label: '404 - Page Not Found'
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
