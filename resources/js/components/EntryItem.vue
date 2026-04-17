<template>
    <div class="event-card">
        <div class="event-header">
            <h3 class="event-name">
                <i class="bi bi-tag"></i>
                {{ item.header?.name || 'Unnamed Event' }}
            </h3>
            <span class="event-type" :class="item.header?.type"> {{ item.header?.type || 'event' }} </span>
        </div>

        <div class="event-date">
            {{ $format.date(item.date) }}
        </div>

        <div class="event-entries">
            <div
                v-for="entry in item.entries"
                :key="entry.id"
                class="entry-item"
            >
                <span class="entry-asset">
                    <i class="bi bi-wallet2"></i>
                    {{ entry.asset.name }}
                </span>
                <span class="entry-amount" :class="$format.signal(entry.amount)">
                    {{ $format.currency(entry.amount) }}
                </span>
            </div>
        </div>

        <div v-if="item.note" class="event-note">
            {{ item.note }}
        </div>
    </div>
</template>

<script setup>
defineProps({item: {type: Object, required: true}});
</script>


<style scoped>

.event-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e5e5;
}

.event-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 6px;
}

.event-name {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    flex: 1;
}

.event-type {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 10px;
    font-weight: 500;
    text-transform: capitalize;
    background: #f0f0f0;
    color: #666;
}

.event-type.transfer {
    background: #e3f2fd;
    color: #1976d2;
}

.event-type.expense {
    background: #ffebee;
    color: #d32f2f;
}

.event-type.income {
    background: #e8f5e8;
    color: #388e3c;
}

.event-date {
    font-size: 13px;
    color: #888;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.event-entries {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.entry-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
}

.entry-asset {
    font-size: 14px;
    color: #555;
}

.entry-amount {
    font-size: 15px;
    font-weight: 500;
}

.entry-amount.positive {
    color: #2e7d32;
}

.entry-amount.negative {
    color: #c62828;
}

.entry-amount.zero {
    color: #999;
}

.event-note {
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid #eee;
    font-size: 13px;
    color: #777;
    font-style: italic;
}
</style>
