import { Modal } from 'bootstrap';

let currentRestoreForm = null;
let currentRestoreDate = null;

window.confirmRestore = function(event, date) {
    event.preventDefault();
    currentRestoreForm = event.target;
    currentRestoreDate = date;
    
    const confirmText = 'restore ' + date;
    document.getElementById('restoreConfirmText').textContent = confirmText;
    document.getElementById('restoreConfirmInput').value = '';
    document.getElementById('restoreConfirmButton').disabled = true;
    
    const modal = new Modal(document.getElementById('restoreConfirmModal'));
    modal.show();
    
    return false;
};

document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('restoreConfirmInput');
    const button = document.getElementById('restoreConfirmButton');
    
    if (input && button) {
        input.addEventListener('input', function(e) {
            const expected = 'restore ' + currentRestoreDate;
            button.disabled = (e.target.value !== expected);
        });
        
        button.addEventListener('click', function() {
            if (currentRestoreForm) {
                currentRestoreForm.submit();
            }
        });
    }
});
