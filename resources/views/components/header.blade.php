<header class="app-header">
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    <h1 class="app-header__title">{{ $pageTitle ?? 'Cash Control' }}</h1>
    <div class="app-header__spacer"></div>
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle theme">
        <i class="bi bi-sun-fill"></i>
        <i class="bi bi-moon-fill"></i>
    </button>
</header>
