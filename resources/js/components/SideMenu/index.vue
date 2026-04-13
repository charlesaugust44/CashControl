<template>
    <div>
        <aside class="app-sidebar" :class="{ 'is-open': isOpen }">
            <div class="sidebar-header">
                <SidebarBrand/>
                <button class="sidebar-close" @click="$emit('toggle-sidebar')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <SidebarNav @item-click="$emit('toggle-sidebar')" :routes="routes"/>
        </aside>

        <div v-if="isOpen" class="sidebar-backdrop" @click="$emit('toggle-sidebar')"></div>
    </div>
</template>

<script setup>
import SidebarBrand from './SidebarBrand.vue';
import SidebarNav from './SidebarNav.vue';

const props = defineProps({
    routes: {type: Array, required: true},
    isOpen: {type: Boolean, default: false},
});

defineEmits(['toggle-sidebar']);
</script>

<style scoped>
.app-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    max-width: 280px;
    height: 100vh;
    background-color: var(--bs-body-bg);
    border-right: 1px solid var(--bs-border-color);
    transform: translateX(-100%);
    visibility: hidden;
    transition: transform 0.3s ease-in-out, visibility 0.3s;
    z-index: 1045;
    display: flex;
    flex-direction: column;
}

.app-sidebar.is-open {
    transform: translateX(0);
    visibility: visible;
}

@media (min-width: 768px) {
    .app-sidebar {
        position: relative !important;
        transform: none !important;
        visibility: visible !important;
        z-index: auto;
    }
}

.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--bs-border-color);
}

.sidebar-close {
    width: 2rem;
    height: 2rem;
    margin-right: 0.5rem;
    border: none;
    border-radius: var(--bs-border-radius);
    opacity: 0.5;
    cursor: pointer;
}

.sidebar-close:hover {
    opacity: 0.75;
}

.sidebar-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
}

@media (min-width: 768px) {
    .sidebar-backdrop, .sidebar-close {
        display: none;
    }
}
</style>
