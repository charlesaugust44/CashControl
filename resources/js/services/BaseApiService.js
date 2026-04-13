import axiosInstance from "./axios.js";

export class BaseApiService {
    constructor(objectUri) {
        this.objectUri = objectUri;
    }

    list(params = {}) {
        return axiosInstance.get(this.objectUri, {params});
    }

    get(id) {
        return axiosInstance.get(`${this.objectUri}/${id}`);
    }

    create(data) {
        return axiosInstance.post(this.objectUri, data);
    }

    update(id, data) {
        return axiosInstance.put(`${this.objectUri}/${id}`, data);
    }

    patch(id, data) {
        return axiosInstance.patch(`${this.objectUri}/${id}`, data);
    }

    delete(id) {
        return axiosInstance.delete(`${this.objectUri}/${id}`);
    }

}
