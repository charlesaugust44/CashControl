<template>
    <div class="asset-detail-container">
        <AssetHeader :total="store.current.balance || 0" @action-click="handleActionClick">
            <template v-slot:action-button>
                <i class="bi bi-pencil-square"></i>
                <span>Edit</span>
            </template>
        </AssetHeader>
        <div class="entries-section">
            <AssetEntries/>
        </div>
    </div>
</template>

<script setup>
import {onMounted, computed} from "vue";
import {useAssetStore} from "../store/asset.js";
import {useRouter, useRoute} from "vue-router";
import {useHeaderStore} from "../store/header.js";

import AssetHeader from "../components/AssetHeader.vue";
import AssetEntries from "../components/AssetEntries.vue";

const store = useAssetStore();
const router = useRouter();
const route = useRoute();
const headerStore = useHeaderStore();

const assetId = computed(() => route.params.id);

const loadAsset = async () => {
    try {
        const response = await store.assetService.get(assetId.value);
        const asset = response.data;
        store.current = {
            id: asset.id,
            name: asset.name,
            balance: asset.balance
        };
        headerStore.title = asset.name;

        await store.fetchAssetEntries();
    } catch (error) {
        console.error('Failed to load asset:', error);
        router.push('/assets');
    }
};

const handleActionClick = () => {
    router.push(`/assets/form/${assetId.value}`);
};

onMounted(() => {
    loadAsset();
});
</script>

<style scoped>
.asset-detail-container {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    height: 100%;
    width: 100%;
}

.entries-section {
    flex: 1;
    overflow-y: auto;
}

.entries-title {
    padding: 1rem 1rem 0.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}
</style>
