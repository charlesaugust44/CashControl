<script setup>
</script>
<template>
    <div class="sidebar-brand">
        <i class="bi bi-cash-coin sidebar-brand__icon"></i>
        <span class="sidebar-brand__text">Cash Control</span>
        <slot/>
    </div>
</template>

<style scoped>
.sidebar-brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    width: 100%;
    border-bottom: 1px solid contrast-color(var(--bs-primary));
    background-color: var(--bs-primary);
    color: contrast-color(var(--bs-primary));

    @media (min-width: 767px) {
        justify-content: flex-start;
    }
}

.sidebar-brand__icon {
    margin-right: 0.5rem;
    font-size: 1.5rem;
}

.sidebar-brand__text {
    font-size: 1.25rem;
    font-weight: 600;
}

</style>
