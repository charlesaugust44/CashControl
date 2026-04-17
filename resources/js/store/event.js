import {defineStore} from "pinia";
import EventService from "../services/EventService.js";

export const useEventStore = defineStore("events", {
    state: () => ({
        events: [],
        loading: false,
        error: null,
        currentMonth: new Date(),
        eventService: new EventService()
    }),
    actions: {
        async fetchEventsByMonth() {
            this.loading = true;
            this.error = null;

            try {
                const response = await this.eventService.listByMonth(this.currentMonth);
                this.events = response.data;
                return response.data;
            } catch (err) {
                this.error = err.message || 'Failed to fetch events';
                this.events = [];
                throw err;
            } finally {
                this.loading = false;
            }
        },
    }
});
