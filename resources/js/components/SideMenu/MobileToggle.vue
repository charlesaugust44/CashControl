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
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 1040;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-border-radius);
    color: var(--bs-body-color);
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
