<template>
    <RouterLink :to="to" class="nav-item" @click="$emit('click')">
        <i :class="['bi', icon, 'nav-item__icon']"></i>
        <span class="nav-item__label">{{ label }}</span>
    </RouterLink>
</template>

<script setup>
import { RouterLink } from 'vue-router';

defineProps({
    to: { type: String, required: true },
    icon: { type: String, required: true },
    label: { type: String, required: true }
});

defineEmits(['click']);
</script>

<style scoped>
.nav-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    margin-bottom: 0.25rem;
    border-radius: var(--bs-border-radius);
    color: var(--bs-body-color);
    text-decoration: none;
    transition: all 0.15s ease-in-out;
}

.nav-item:hover {
    background-color: var(--bs-tertiary-bg);
}

.nav-item.router-link-active {
    background-color: var(--bs-primary);
    color: white;
}

.nav-item.router-link-active .nav-item__icon {
    color: white;
}

.nav-item__icon {
    margin-right: 0.75rem;
    font-size: 1.25rem;
}

.nav-item__label {
    font-weight: 500;
}
</style>
