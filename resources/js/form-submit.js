document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const submitter = e.submitter;
            const buttons = form.querySelectorAll('button[type="submit"]');
            const externalButtons = form.id
                ? document.querySelectorAll('button[type="submit"][form="' + form.id + '"]')
                : [];
            const allButtons = [...buttons, ...externalButtons];

            if (submitter && submitter.name && submitter.value) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = submitter.name;
                hiddenInput.value = submitter.value;
                form.appendChild(hiddenInput);
            }

            allButtons.forEach(function(btn) {
                btn.disabled = true;
                if (btn === submitter) {
                    btn.dataset.originalHtml = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + btn.dataset.originalHtml;
                }
            });
        });
    });
});
