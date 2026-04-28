<template>
    <div class="assets-container">
        <AssetHeader :total="store.total"/>
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

import DataList from "../components/DataList.vue";
import AssetItem from "../components/AssetItem.vue";
import AssetHeader from "../components/AssetHeader.vue";

const store = useAssetStore();

onMounted(() => {
    store.fetchAssets();
});
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
