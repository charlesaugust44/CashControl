<template>
    <div class="d-flex">
        <SideMenu :routes="sidebarRoutes"/>
        <main class="grow p-4 bg-light min-vh-100">
            <RouterView/>
        </main>
    </div>
</template>

<script setup>
import SideMenu from './components/SideMenu/index.vue'
import {RouterView} from 'vue-router'
import {sidebarRoutes} from "./router/index.js";
</script>
