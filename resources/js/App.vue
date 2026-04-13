<template>
    <div class="d-flex flex-column overflow-hidden">
        <SideMenu :routes="sidebarRoutes" :is-open="sidebarOpen" @toggle-sidebar="toggleSidebar"/>
        <AppHeader @toggle-sidebar="toggleSidebar"/>
        <main class="p-4 bg-light">
            <RouterView/>
        </main>
    </div>
</template>

<script setup>
import SideMenu from './components/SideMenu/index.vue'
import {RouterView} from 'vue-router'
import {sidebarRoutes} from "./router/index.js";
import AppHeader from "./components/AppHeader.vue";
import {ref} from "vue";

const sidebarOpen = ref(false);

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
}
</script>
