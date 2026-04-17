<template>
    <div class="month-nav-flat">
        <button class="btn-arrow" @click="() => updateMonth(-1)">
            <i class="bi bi-chevron-left"></i>
        </button>
        <div class="month-text-flat"
             :class="{'current-month': currentMonth}"
             @click="() => updateMonth()">
            {{ formattedMonth }}
        </div>
        <button class="btn-arrow" @click="() => updateMonth(1)">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>
</template>

<script setup>

import {computed} from "vue";

const emit = defineEmits(['update:month']);
const props = defineProps({
    month: {type: Date, required: true},
});

const formattedMonth = computed(() => {
    const monthName = props.month.toLocaleString('default', {month: 'long'})
    const year = props.month.getFullYear();

    return `${monthName} ${year}`;
});

const currentMonth = computed(() => {
    const now = new Date();

    return props.month.getMonth() === now.getMonth() &&
        props.month.getFullYear() === now.getFullYear();
})

function updateMonth(stepValue = 0) {
    if (stepValue === 0) {
        emit('update:month', new Date());
        return;
    }

    const newDate = new Date(props.month);
    newDate.setMonth(props.month.getMonth() + stepValue);
    emit('update:month', newDate);
}
</script>

<style scoped>
.month-nav-flat {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    justify-content: space-between;
    padding: 0.5rem 0.25rem;
    border-bottom: 1px solid #d4dae2;
}

.btn-arrow {
    background: none;
    border: none;
    padding: 0.25rem 0.5rem;
    font-size: 1.2rem;
    color: #4a5a6e;
    cursor: pointer;
    transition: color 0.15s ease;
}

.btn-arrow:hover {
    color: #000;
}

.btn-arrow:focus-visible {
    outline: 2px solid #1a56db;
    outline-offset: 2px;
}

.month-text-flat {
    font-size: 1rem;
    letter-spacing: -0.2px;
    user-select: none;
    background: var(--bs-secondary);
    color: white;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(13, 110, 253, 0.3);
}

.month-text-flat:hover {
    cursor: pointer;
}

.current-month {
    background: var(--bs-primary);
}
</style>
