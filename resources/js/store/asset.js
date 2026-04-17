import {defineStore} from "pinia";
import AssetService from "../services/AssetService.js";

export const useAssetStore = defineStore("assets", {
    state: () => ({
        assets: [],
        loading: false,
        error: null,
        currentMonth: new Date(),
        assetService: new AssetService()
    }),
    actions: {
        async fetchAssets() {
            this.loading = true;
            this.error = null;

            try {
                const response = await this.assetService.list();
                this.assets = response.data;
                return response.data;
            } catch (err) {
                this.error = err.message || 'Failed to fetch assets';
                this.assets = [];
                throw err;
            } finally {
                this.loading = false;
            }
        },
    }
});
