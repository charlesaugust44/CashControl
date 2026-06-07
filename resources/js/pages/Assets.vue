<template>
    <div class="assets-container">
        <AssetHeader :total="store.total" @action-click="handleActionClick">
            <template v-slot:action-button>
                <i class="bi bi-plus-circle"></i>
                <span>Add Asset</span>
            </template>
        </AssetHeader>
        <DataList :items="store.assets">
            <template v-slot:item="{item}">
                <AssetItem :item="item"/>
            </template>
        </DataList>
    </div>
</template>

<script setup>
import {useAssetStore} from "../store/asset.js";
import {onMounted} from "vue";
import {useRouter} from "vue-router";

import DataList from "../components/DataList.vue";
import AssetItem from "../components/AssetItem.vue";
import AssetHeader from "../components/AssetHeader.vue";

const store = useAssetStore();
const router = useRouter();

onMounted(() => {
    store.fetchAssets();
});

function handleActionClick() {
    router.push('/assets/form/new');
}
</script>

<style scoped>
.assets-container {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    height: 100%;
    width: 100%;
}
</style>
