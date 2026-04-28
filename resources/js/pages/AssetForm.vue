<template>
    <form @submit.prevent="handleSubmit" class="asset-form">
        <div class="mb-3">
            <label for="assetName" class="form-label">Asset Name</label>
            <input
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.name }"
                id="assetName"
                v-model="store.current.name"
                placeholder="e.g., Checking Account"
                :disabled="store.loading"
            />
            <div v-if="errors.name" class="invalid-feedback">
                {{ errors.name }}
            </div>
        </div>

        <div class="mb-3">
            <label for="assetBalance" class="form-label">Initial Balance</label>
            <input
                type="number"
                step="0.01"
                class="form-control"
                :class="{ 'is-invalid': errors.balance }"
                id="assetBalance"
                v-model.number="store.current.balance"
                :disabled="store.loading"
            />
            <div v-if="errors.balance" class="invalid-feedback">
                {{ errors.balance }}
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <button
                type="button"
                class="btn btn-outline-secondary"
                @click="handleCancel"
                :disabled="store.loading"
            >
                Cancel
            </button>
            <button
                type="submit"
                class="btn btn-primary"
                :disabled="store.loading"
            >
                <span v-if="store.loading" class="spinner-border spinner-border-sm me-2"></span>
                Save
            </button>
        </div>
    </form>
</template>

<script setup>
import {useAssetStore} from "../store/asset.js";
import {useHeaderStore} from "../store/header.js";
import {useRouter} from "vue-router";
import {reactive, onMounted} from "vue";

const store = useAssetStore();
const headerStore = useHeaderStore();
const router = useRouter();

const errors = reactive({
    name: null,
    balance: null
});

store.clearForm();

const handleCancel = () => {
    router.push('/assets');
};

const validate = () => {
    errors.name = null;
    errors.balance = null;

    let isValid = true;

    if (!store.current.name?.trim()) {
        errors.name = 'Asset name is required.';
        isValid = false;
    }

    if (store.current.balance < 0) {
        errors.balance = 'Balance cannot be negative.';
        isValid = false;
    }

    return isValid;
};

const handleSubmit = async () => {
    if (!validate()) return;

    try {
        await store.assetService.create({
            name: store.current.name,
            balance: store.current.balance || 0
        });

        router.push('/assets');
    } catch (error) {
        if (error.response?.data?.errors) {
            if (error.response.data.errors.name) {
                errors.name = error.response.data.errors.name[0];
            }
            if (error.response.data.errors.balance) {
                errors.balance = error.response.data.errors.balance[0];
            }
        } else {
            alert(error.message || 'Failed to create asset. Please try again.');
        }
    }
};

onMounted(() => {
    headerStore.title = store.current.name || "New Asset";
});
</script>

<style scoped>
.asset-form {
    padding: 1rem;
}
</style>
