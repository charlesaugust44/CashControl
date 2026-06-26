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
}

function initTheme() {
    applyTheme(getInitialTheme());
}

window.toggleTheme = toggleTheme;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}
