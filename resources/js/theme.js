const THEME_KEY = 'cashcontrol-theme';
const STORAGE_LIGHT = 'light';
const STORAGE_DARK = 'dark';

function getStoredTheme() {
    try {
        return localStorage.getItem(THEME_KEY);
    } catch {
        return null;
    }
}

function setStoredTheme(theme) {
    try {
        localStorage.setItem(THEME_KEY, theme);
    } catch {}
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
}

function getInitialTheme() {
    const stored = getStoredTheme();
    if (stored === STORAGE_LIGHT || stored === STORAGE_DARK) {
        return stored;
    }
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return STORAGE_DARK;
    }
    return STORAGE_LIGHT;
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || STORAGE_LIGHT;
    const next = current === STORAGE_DARK ? STORAGE_LIGHT : STORAGE_DARK;
    applyTheme(next);
    setStoredTheme(next);
    document.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: next } }));
}

function initTheme() {
    applyTheme(getInitialTheme());
}

window.toggleTheme = toggleTheme;

function toggleOptionsDropdown() {
    const menu = document.getElementById('optionsMenu');
    if (menu) {
        menu.classList.toggle('show');
    }
}

window.toggleOptionsDropdown = toggleOptionsDropdown;

function toggleNotificationsDropdown() {
    const menu = document.getElementById('notificationsMenu');
    if (menu) {
        menu.classList.toggle('show');
    }
}

window.toggleNotificationsDropdown = toggleNotificationsDropdown;

function switchNotificationTab(tab) {
    document.querySelectorAll('.notifications-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.notifications-panel').forEach(p => p.classList.remove('active'));

    document.querySelector(`.notifications-tab[data-tab="${tab}"]`).classList.add('active');
    document.getElementById(`${tab}-panel`).classList.add('active');
}

window.switchNotificationTab = switchNotificationTab;

document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.options-dropdown');
    const menu = document.getElementById('optionsMenu');
    if (dropdown && menu && !dropdown.contains(event.target)) {
        menu.classList.remove('show');
    }

    const notifDropdown = document.querySelector('.notifications-dropdown');
    const notifMenu = document.getElementById('notificationsMenu');
    if (notifDropdown && notifMenu && !notifDropdown.contains(event.target)) {
        notifMenu.classList.remove('show');
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}
