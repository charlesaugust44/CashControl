<template>
    <div class="list-wrapper">
        <div v-for="item in items" :key="item.id" class="list-item">
            <slot name="item" :item="item"/>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    items: {type: Array, required: true},
});
</script>

<style scoped>

.list-wrapper {
    width: 100%;
    height: 100%;
    padding: .5rem;
    overflow-y: auto;
    flex: 1;
}
</style>
