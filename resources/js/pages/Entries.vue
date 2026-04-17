<template>
    <div class="entries-container">
        <MonthPicker v-model:month="store.currentMonth"/>
        <DataList :items="store.events">
            <template v-slot:item="{item}">
                <EntryItem :item="item"/>
            </template>
        </DataList>
    </div>
</template>

<script setup>
import {watch} from "vue";
import {useEventStore} from "../store/event.js";
import {storeToRefs} from "pinia";

import MonthPicker from "../components/MonthPicker.vue";
import DataList from "../components/DataList.vue";
import EntryItem from "../components/EntryItem.vue";

const store = useEventStore();
const {currentMonth} = storeToRefs(store);

watch(currentMonth, () => {
    store.fetchEventsByMonth();
}, {immediate: true});
</script>

<style scoped>
.entries-container {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    height: 100%;
    width: 100%;
}
</style>
