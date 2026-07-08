@props([
    'name' => 'type',
    'value' => '',
    'error' => null,
])

@php
    $isIncome = in_array($value, ['income', 'income_with_transfer']);
    $isExpense = in_array($value, ['expense', 'expense_with_transfer']);
    $isTransfer = in_array($value, ['transfer', 'income_with_transfer', 'expense_with_transfer']);
    $id = 'type-selector-' . md5($name);
@endphp

<div class="type-selector" id="{{ $id }}" data-type-selector data-name="{{ $name }}">
    <button type="button" class="type-selector__pill type-selector__pill--income {{ $isIncome ? 'is-active' : '' }}" data-type="income">
        <i class="bi bi-arrow-down-left"></i>
        <span>{{ __('templates.types.income') }}</span>
    </button>
    <button type="button" class="type-selector__pill type-selector__pill--expense {{ $isExpense ? 'is-active' : '' }}" data-type="expense">
        <i class="bi bi-arrow-up-right"></i>
        <span>{{ __('templates.types.expense') }}</span>
    </button>
    <button type="button" class="type-selector__pill type-selector__pill--transfer {{ $isTransfer ? 'is-active' : '' }}" data-type="transfer">
        <i class="bi bi-arrow-left-right"></i>
        <span>{{ __('templates.types.transfer') }}</span>
    </button>
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-type-input>
    @if($error)
        <span class="type-selector__error">{{ $error }}</span>
    @endif
</div>

<script>
(function() {
    const container = document.getElementById('{{ $id }}');
    if (!container) return;

    const pills = container.querySelectorAll('.type-selector__pill');
    const hiddenInput = container.querySelector('[data-type-input]');

    function updateValue() {
        const incomeActive = container.querySelector('[data-type="income"]').classList.contains('is-active');
        const expenseActive = container.querySelector('[data-type="expense"]').classList.contains('is-active');
        const transferActive = container.querySelector('[data-type="transfer"]').classList.contains('is-active');

        let value = '';
        if (incomeActive && transferActive) {
            value = 'income_with_transfer';
        } else if (expenseActive && transferActive) {
            value = 'expense_with_transfer';
        } else if (incomeActive) {
            value = 'income';
        } else if (expenseActive) {
            value = 'expense';
        } else if (transferActive) {
            value = 'transfer';
        }

        hiddenInput.value = value;
        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    pills.forEach(pill => {
        pill.addEventListener('click', function() {
            const type = this.dataset.type;
            const isActive = this.classList.contains('is-active');

            if (type === 'income') {
                if (!isActive) {
                    container.querySelector('[data-type="expense"]').classList.remove('is-active');
                    this.classList.add('is-active');
                } else {
                    this.classList.remove('is-active');
                }
            } else if (type === 'expense') {
                if (!isActive) {
                    container.querySelector('[data-type="income"]').classList.remove('is-active');
                    this.classList.add('is-active');
                } else {
                    this.classList.remove('is-active');
                }
            } else if (type === 'transfer') {
                this.classList.toggle('is-active');
            }

            updateValue();
        });
    });
})();
</script>
