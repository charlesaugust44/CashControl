import {BaseApiService} from "./BaseApiService.js";
import axiosInstance from "./axios.js";

export default class AssetService extends BaseApiService {
    constructor() {
        super("/assets");
    }

    entries(id) {
        return axiosInstance.get(`/assets/${id}/entries`);
    }
}
