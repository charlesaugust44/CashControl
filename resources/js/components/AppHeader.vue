<template>
    <header class="app-header">
        <MobileToggle @toggle="$emit('toggle-sidebar')"/>
        <h1 class="app-header__title">{{ store.title }}</h1>
        <div class="app-header__spacer"></div>
    </header>
</template>

<script setup>
import MobileToggle from "./SideMenu/MobileToggle.vue";
import {useHeaderStore} from "../store/header.js";
import {watch} from "vue";
import {useRoute} from "vue-router";

const store = useHeaderStore();

const route = useRoute();

function routeTitle() {
    if (!route?.matched || route.matched.length === 0) {
        return null;
    }

    return route.matched[route.matched.length - 1]?.meta?.label;
}

watch(routeTitle, title => {
    store.title = title
}, {immediate: true});


defineEmits(['toggle-sidebar']);
</script>

<style scoped>
.app-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0 1.5rem;
    background-color: var(--bs-body-bg, #ffffff);
    border-bottom: 1px solid var(--bs-border-color, #e5e7eb);
    height: var(--header-height);
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
