<template>
    <div class="sidebar-brand" :class="{ 'sidebar-brand--mobile': mobile }">
        <i class="bi bi-cash-coin sidebar-brand__icon"></i>
        <span class="sidebar-brand__text">Cash Control</span>
    </div>
</template>

<script setup>
defineProps({
    mobile: {
        type: Boolean,
        default: false
    }
});
</script>

<style scoped>
.sidebar-brand {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--bs-border-color);
}

.sidebar-brand__icon {
    margin-right: 0.5rem;
    font-size: 1.5rem;
    color: var(--bs-success);
}

.sidebar-brand__text {
    font-size: 1.25rem;
    font-weight: 600;
}

.sidebar-brand--mobile {
    @media (min-width: 768px) {
        display: none;
    }
}
</style>
