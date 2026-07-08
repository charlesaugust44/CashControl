class ToastManager {
    constructor() {
        this._container = null;
        this.toasts = new Map();
        this.nextId = 1;
    }

    get container() {
        if (!this._container) {
            this._container = document.getElementById('toast-container');
            if (!this._container) {
                console.error('Toast container not found!');
                return null;
            }
        }
        return this._container;
    }

    show({ type = 'info', title, message, duration = 5000 }) {
        const container = this.container;
        if (!container) {
            console.error('Cannot show toast: container not available');
            return null;
        }

        const id = this.nextId++;
        const toast = this.createToast({ id, type, title, message });
        
        container.appendChild(toast);
        this.toasts.set(id, toast);

        if (duration > 0) {
            setTimeout(() => this.dismiss(id), duration);
        }

        return id;
    }

    createToast({ id, type, title, message }) {
        const toast = document.createElement('div');
        toast.className = `cc-toast cc-toast--${type}`;
        toast.dataset.toastId = id;

        const icons = {
            success: 'bi-check-circle-fill',
            error: 'bi-x-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };

        toast.innerHTML = `
            <i class="bi ${icons[type]} cc-toast__icon"></i>
            <div class="cc-toast__content">
                ${title ? `<div class="cc-toast__title">${title}</div>` : ''}
                ${message ? `<div class="cc-toast__message">${message}</div>` : ''}
            </div>
        `;

        toast.addEventListener('click', () => this.dismiss(id));

        return toast;
    }

    dismiss(id) {
        const toast = this.toasts.get(id);
        if (!toast) return;

        toast.classList.add('cc-toast-removing');
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.toasts.delete(id);
        }, 300);
    }

    success(title, message, duration = 5000) {
        return this.show({ type: 'success', title, message, duration });
    }

    error(title, message, duration = 0) {
        return this.show({ type: 'error', title, message, duration });
    }

    warning(title, message, duration = 5000) {
        return this.show({ type: 'warning', title, message, duration });
    }

    info(title, message, duration = 0) {
        return this.show({ type: 'info', title, message, duration });
    }
}

// Initialize global toast manager
window.toast = new ToastManager();
