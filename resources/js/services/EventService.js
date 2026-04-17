import {BaseApiService} from "./BaseApiService.js";
import axiosInstance from "./axios.js";

export default class EventService extends BaseApiService {
    constructor() {
        super("/events");
    }

    consolidate(id) {
        return axiosInstance.patch(`/events/${id}/consolidate`);
    }

    /**
     * @param {Date} month
     */
    listByMonth(month) {
        return axiosInstance.get(`/events/${month.getFullYear()}/${month.getMonth()+1}`);
    }
}
