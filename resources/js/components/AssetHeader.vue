<template>
    <div class="asset-header">
        <button class="add-asset-btn" @click="handleAddAsset">
            <i class="bi bi-plus-circle"></i>
            <span>Add Asset</span>
        </button>

        <div class="assets-total">
            <i class="bi bi-wallet2"></i>
            <div class="total-info">
                <span class="total-label">Total Assets</span>
                <span class="total-amount" :class="$format.signal(total)">
                        {{ $format.currency(total) }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import {useRouter} from "vue-router";
import {useAppHeader} from "../composables/useAppHeader.js";

const router = useRouter();

defineProps({
    total: {type: Number, required: true},
});

const handleAddAsset = () => {
    router.push('/assets/form/new');
};
</script>

<style scoped>

.asset-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    color: #333;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.header-left {
    display: flex;
    gap: 24px;
    align-items: center;
}

.assets-total {
    display: flex;
    align-items: center;
    gap: 12px;
}

.assets-total i {
    font-size: 28px;
    opacity: 0.9;
}

.total-info {
    display: flex;
    flex-direction: column;
}

.total-label {
    font-size: 12px;
    opacity: 0.8;
    letter-spacing: 0.5px;
}

.total-amount {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
}

.assets-count i {
    font-size: 18px;
}

.assets-count span {
    font-size: 14px;
    font-weight: 500;
}

.add-asset-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    color: #333;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.add-asset-btn i {
    font-size: 18px;
}

.add-asset-btn:hover {
    background: rgba(0, 0, 0, 0.3);
    transform: translateY(-1px);
}

.add-asset-btn:active {
    transform: translateY(0);
}

@media (max-width: 640px) {
    .asset-header {
        gap: 10px;
        align-items: stretch;
        padding: 10px;
    }

    .total-amount {
        font-size: 20px;
    }

    .add-asset-btn {
        justify-content: center;
    }
}
</style>
