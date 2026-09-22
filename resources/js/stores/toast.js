import { defineStore } from 'pinia';

let nextId = 1;

export const useToastStore = defineStore('toast', {
    state: () => ({
        items: [],
    }),

    actions: {
        push(message, type = 'info', timeout = 4000) {
            const id = nextId++;
            this.items.push({ id, message, type });

            if (timeout) {
                setTimeout(() => this.remove(id), timeout);
            }

            return id;
        },

        success(message) {
            return this.push(message, 'success');
        },

        error(message) {
            return this.push(message, 'error', 6000);
        },

        info(message) {
            return this.push(message, 'info');
        },

        remove(id) {
            this.items = this.items.filter((item) => item.id !== id);
        },
    },
});
