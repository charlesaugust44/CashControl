<template>
    <nav class="sidebar-nav">
        <SidebarNavItem v-for="route in routes" :key="route.path" :route="route"
                        :to="route.to"
                        :icon="route.icon"
                        :label="route.label"
                        @click="$emit('item-click')"
        />
    </nav>
</template>

<script setup>
import SidebarNavItem from './SidebarNavItem.vue';

const props = defineProps({
    routes: {type: Array, required: true}
});

defineEmits(['item-click']);
</script>

<style scoped>
.sidebar-nav {
    display: flex;
    flex-direction: column;
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
}
</style>
