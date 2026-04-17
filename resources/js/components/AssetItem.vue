<template>
    <div class="asset-card">
        <div class="asset-header">
            <div class="asset-title">
                <i class="bi bi-wallet2 asset-icon"></i>
                <h3 class="asset-name">{{ item.name || 'Unnamed Asset' }}</h3>
            </div>
            <span class="asset-balance" :class="$format.signal(item.balance)">
                {{ $format.currency(item.balance) }}
            </span>
        </div>

        <div class="asset-details">
            <div class="asset-detail-item">
                <i class="bi bi-calendar-check"></i>
                <span class="detail-label">Consolidation:</span>
                <span class="detail-value">{{ $format.date(item.consolidation) }}</span>
            </div>
            <div class="asset-detail-item">
                <i class="bi bi-clock-history"></i>
                <span class="detail-label">Last updated:</span>
                <span class="detail-value">{{ $format.dateTime(item.updated_at) }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    item: { type: Object, required: true }
});
</script>

<style scoped>
.asset-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e5e5;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.asset-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-color: #d0d0d0;
}

.asset-card:active {
    transform: scale(0.99);
}

.asset-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}

.asset-title {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

.asset-icon {
    font-size: 20px;
    color: #666;
}

.asset-name {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
}

.asset-balance {
    font-size: 18px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 8px;
    background: #f8f9fa;
}

.asset-balance.balance-positive {
    color: #2e7d32;
    background: #e8f5e8;
}

.asset-balance.balance-negative {
    color: #c62828;
    background: #ffebee;
}

.asset-balance.balance-zero {
    color: #666;
    background: #f5f5f5;
}

.asset-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

.asset-detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

.asset-detail-item i {
    font-size: 14px;
    color: #888;
    width: 16px;
}

.detail-label {
    color: #888;
    min-width: 90px;
}

.detail-value {
    color: #444;
    font-weight: 500;
}

.asset-action i {
    font-size: 18px;
}
</style>
