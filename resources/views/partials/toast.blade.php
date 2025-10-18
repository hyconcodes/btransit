<div x-data="toastStore()" x-init="init()" class="toast-container">
    <template x-for="t in toasts" :key="t.id">
        <div :class="['toast', t.type === 'success' ? 'toast-success' : 'toast-error']" x-show="t.show" x-transition>
            <span class="toast-icon"></span>
            <span class="tw-body" x-text="t.message"></span>
            <button @click="remove(t.id)" class="toast-cancel" aria-label="Close">&times;</button>
        </div>
    </template>
</div>

<script>
    window.toastStore = function() {
        return {
            toasts: [],
            init() {
                window.addEventListener('toast', (e) => {
                    const { type = 'success', message = '' } = e.detail || {};
                    this.push(message, type);
                });
                // Optionally show flash messages if present
                const flashSuccess = @json(session('success'));
                const flashError = @json(session('error'));
                if (flashSuccess) this.push(flashSuccess, 'success');
                if (flashError) this.push(flashError, 'error');
            },
            push(message, type) {
                const id = Date.now() + Math.random();
                const toast = { id, message, type, show: true };
                this.toasts.push(toast);
                setTimeout(() => { toast.show = false; }, 3500);
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 4000);
            },
            remove(id) {
                const toast = this.toasts.find(t => t.id === id);
                if (toast) {
                    toast.show = false;
                    setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
                }
            }
        };
    }
</script>