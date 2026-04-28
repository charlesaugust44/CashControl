import {defineStore} from "pinia";
import AssetService from "../services/AssetService.js";

export const useAssetStore = defineStore("assets", {
    state: () => ({
        assets: [],
        loading: false,
        error: null,
        currentMonth: new Date(),
        assetService: new AssetService(),
        total: 0,
        current: {
            id: null,
            name: null,
            balance: null,
        },
    }),
    actions: {
        async fetchAssets() {
            this.loading = true;
            this.error = null;

            try {
                const response = await this.assetService.list();
                this.assets = response.data;
                this.totalBalance();

                return response.data;
            } catch (err) {
                this.error = err.message || 'Failed to fetch assets';
                this.assets = [];
                throw err;
            } finally {
                this.loading = false;
            }
        },
        totalBalance() {
            this.total = this.assets.reduce((total, asset) => total + (asset.balance || 0), 0);
            return this.total;
        },
        getName() {
            return this.current?.name ?? null;
        },
        clearForm() {
            this.current = {
                id: null,
                name: null,
                balance: null,
            };
        }
    },
});
