document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('deleteModal');
    if (!modal) return;

    const triggers = document.querySelectorAll('[data-delete-modal-trigger]');
    const closeButtons = modal.querySelectorAll('[data-delete-modal-close]');

    function openModal() {
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            openModal();
        });
    });

    closeButtons.forEach(function (btn) {
        btn.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
