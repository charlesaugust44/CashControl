<div>
    <aside class="app-sidebar" id="appSidebar">
        <div class="sidebar-brand">
            <i class="bi bi-cash-coin sidebar-brand__icon"></i>
            <span class="sidebar-brand__text">Cash Control</span>
            <button class="sidebar-close" onclick="toggleSidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ url('/') }}" class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 nav-item__icon"></i>
                <span class="nav-item__label">Dashboard</span>
            </a>
            <a href="{{ url('/assets') }}" class="nav-item {{ request()->is('assets*') ? 'active' : '' }}">
                <i class="bi bi-wallet2 nav-item__icon"></i>
                <span class="nav-item__label">Assets</span>
            </a>
            <a href="{{ url('/templates') }}" class="nav-item {{ request()->is('templates*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data nav-item__icon"></i>
                <span class="nav-item__label">Templates</span>
            </a>
            <a href="{{ url('/entries') }}" class="nav-item {{ request()->is('entries*') ? 'active' : '' }}">
                <i class="bi bi-journal-text nav-item__icon"></i>
                <span class="nav-item__label">Entries</span>
            </a>
        </nav>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>
</div>
<script>
function toggleSidebar() {
    document.getElementById('appSidebar').classList.toggle('is-open');
    document.getElementById('sidebarBackdrop').classList.toggle('is-open');
}
</script>
