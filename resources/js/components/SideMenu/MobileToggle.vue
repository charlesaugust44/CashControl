<template>
    <button class="mobile-toggle" @click="$emit('toggle')">
        <i class="bi bi-list"></i>
    </button>
</template>

<script setup>
defineEmits(['toggle']);
</script>

<style scoped>
.mobile-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    border: none;
    background: var(--bs-body-bg);
    border-radius: var(--bs-border-radius);
    color: var(--bs-body-color);
    cursor: pointer;
}

.mobile-toggle i {
    font-size: 1.5rem;
}

@media (min-width: 768px) {
    .mobile-toggle {
        display: none;
    }
}
</style>
