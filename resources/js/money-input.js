class MoneyInput {
    constructor(input) {
        this.input = input;
        this.cents = 0;
        this._freshInput = false;
        this._parseInitialValue();
        this._attachListeners();
        this._render();
    }

    _parseInitialValue() {
        const val = this.input.value;
        if (val && !isNaN(parseFloat(val))) {
            this.cents = Math.round(parseFloat(val) * 100);
        } else {
            this.cents = 0;
        }
    }

    _render() {
        const absCents = Math.abs(this.cents);
        const dollars = Math.floor(absCents / 100);
        const cents = absCents % 100;
        this.input.value = `${dollars}.${cents.toString().padStart(2, '0')}`;
    }

    _dispatchInput() {
        this.input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    _attachListeners() {
        this.input.addEventListener('keydown', (e) => this._onKeyDown(e));
        this.input.addEventListener('focus', () => this._onFocus());
        this.input.addEventListener('paste', (e) => this._onPaste(e));
    }

    _onKeyDown(e) {
        if (['Tab', 'Enter', 'Escape', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) {
            return;
        }
        if ((e.ctrlKey || e.metaKey) && (e.key === 'a' || e.key === 'c' || e.key === 'v' || e.key === 'x')) {
            return;
        }

        e.preventDefault();

        if (e.key === 'Backspace') {
            this.cents = Math.floor(this.cents / 10);
            this._render();
            this._dispatchInput();
            return;
        }

        if (e.key === 'Delete') {
            this.cents = 0;
            this._render();
            this._dispatchInput();
            return;
        }

        const digit = parseInt(e.key);
        if (!isNaN(digit)) {
            if (this._freshInput) {
                this.cents = 0;
                this._freshInput = false;
            }
            this.cents = this.cents * 10 + digit;
            this._render();
            this._dispatchInput();
        }
    }

    _onFocus() {
        this._freshInput = true;
        setTimeout(() => this.input.select(), 0);
    }

    _onPaste(e) {
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text');
        const num = parseFloat(text.replace(/[^0-9.-]/g, ''));
        if (!isNaN(num)) {
            this.cents = Math.round(Math.abs(num) * 100);
            this._freshInput = false;
            this._render();
            this._dispatchInput();
        }
    }

    static init() {
        document.querySelectorAll('.money-input:not([data-money-initialized])').forEach(input => {
            if (!input.disabled) {
                new MoneyInput(input);
                input.setAttribute('data-money-initialized', 'true');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', MoneyInput.init);

const observer = new MutationObserver(() => MoneyInput.init());
observer.observe(document.body, { childList: true, subtree: true });
