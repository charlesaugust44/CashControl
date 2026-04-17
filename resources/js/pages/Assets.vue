<template>
    <DataList :items="store.assets">
        <template v-slot:item="{item}">
            <AssetItem :item="item"/>
        </template>
    </DataList>
</template>

<script setup>
import {useAssetStore} from "../store/asset.js";
import {onMounted} from "vue";

import DataList from "../components/DataList.vue";
import AssetItem from "../components/AssetItem.vue";

const store = useAssetStore();

onMounted(() => {
    store.fetchAssets();
});
</script>

<style scoped>

</style>
