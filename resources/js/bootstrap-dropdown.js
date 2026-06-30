import { Dropdown } from 'bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(trigger) {
        new Dropdown(trigger);
    });
});
