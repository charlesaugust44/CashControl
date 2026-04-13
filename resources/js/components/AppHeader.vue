<template>
    <header class="app-header">
        <MobileToggle @toggle="$emit('toggle-sidebar')"/>
        <h1 class="app-header__title">{{ title }}</h1>
        <div class="app-header__spacer"></div>
    </header>
</template>

<script setup>
import MobileToggle from "./SideMenu/MobileToggle.vue";
import {useRoute} from "vue-router";
import {computed} from "vue";

const route = useRoute();

const title = computed(() => {
    if (route.matched.length > 0) {
        const meta = route.matched[route.matched.length - 1].meta;
        return meta?.label || route.name || 'Dashboard';
    }
    return route.name || 'Dashboard';
});

defineEmits(['toggle-sidebar']);
</script>

<style scoped>
.app-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    height: 64px;
    padding: 0 1.5rem;
    background-color: var(--bs-body-bg, #ffffff);
    border-bottom: 1px solid var(--bs-border-color, #e5e7eb);
    flex-shrink: 0;
}

.app-header__title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--bs-body-color, #1f2937);
    line-height: 1.2;
}

.app-header__spacer {
    flex: 1;
}

@media (max-width: 767px) {
    .app-header {
        padding: 0 1rem;
    }

    .app-header__title {
        font-size: 1.25rem;
    }
}
</style>
