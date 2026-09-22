import { defineStore } from 'pinia';

export const useConfirmStore = defineStore('confirm', {
    state: () => ({
        visible: false,
        title: 'Konfirmasi',
        message: '',
        confirmText: 'Hapus',
        cancelText: 'Batal',
        danger: true,
        resolver: null,
    }),

    actions: {
        ask(message, options = {}) {
            this.title = options.title ?? 'Konfirmasi';
            this.message = message;
            this.confirmText = options.confirmText ?? 'Hapus';
            this.cancelText = options.cancelText ?? 'Batal';
            this.danger = options.danger ?? true;
            this.visible = true;

            return new Promise((resolve) => {
                this.resolver = resolve;
            });
        },

        resolve(value) {
            this.visible = false;
            this.resolver?.(value);
            this.resolver = null;
        },
    },
});
