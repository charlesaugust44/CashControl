<template>
    <div class="entries-container">
        <MonthPicker v-model:month="store.currentMonth"/>

        <!-- Loading state -->
        <div v-if="store.loading" class="empty-state">
            <div class="empty-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 12 12" to="360 12 12" repeatCount="indefinite"/>
                    </circle>
                </svg>
            </div>
            <h3 class="empty-title">Loading entries...</h3>
        </div>

        <!-- Empty state -->
        <div v-else-if="!store.loading && store.events.length === 0" class="empty-state">
            <div class="empty-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="empty-title">No entries found</h3>
            <p class="empty-description">
                This asset doesn't have any entries for the selected month.
                Try selecting a different month or check if there are any transactions recorded.
            </p>
        </div>

        <!-- Entries list -->
        <DataList v-else :items="store.events">
            <template v-slot:item="{item}">
                <EntryItem :item="item"/>
            </template>
        </DataList>
    </div>
</template>

<script setup>
import {watch} from "vue";
import {storeToRefs} from "pinia";
import {useAssetStore} from "../store/asset.js";

import MonthPicker from "./MonthPicker.vue";
import DataList from "./DataList.vue";
import EntryItem from "./EntryItem.vue";

const store = useAssetStore();
const {currentMonth} = storeToRefs(store);

watch(currentMonth, () => {
    if (store.current.id) {
        store.fetchAssetEntries();
    }
});
</script>

<style scoped>
.entries-container {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    height: 100%;
    width: 100%;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1;
    padding: 2rem;
    text-align: center;
}

.empty-icon {
    margin-bottom: 1rem;
    color: #9ca3af;
    opacity: 0.7;
}

.empty-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 0.5rem 0;
}

.empty-description {
    font-size: 0.875rem;
    color: #6b7280;
    max-width: 24rem;
    line-height: 1.5;
    margin: 0;
}

/* Dark mode styles */
@media (prefers-color-scheme: dark) {
    .empty-icon {
        color: #6b7280;
    }

    .empty-title {
        color: #f3f4f6;
    }

    .empty-description {
        color: #9ca3af;
    }
}
</style>
