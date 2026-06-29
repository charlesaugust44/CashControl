<header class="app-header">
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    <h1 class="app-header__title">{{ $pageTitle ?? 'Cash Control' }}</h1>
    <div class="app-header__spacer"></div>
    <div class="app-header__actions">
        <a href="{{ request()->fullUrlWithQuery(['lang' => 'toggle']) }}" class="header-icon-btn language-toggle" aria-label="Toggle language">
            <span class="language-toggle__text">{{ app()->getLocale() === 'pt_BR' ? 'PT' : 'EN' }}</span>
        </a>
        <button class="header-icon-btn theme-toggle" onclick="toggleTheme()" aria-label="Toggle theme">
            <i class="bi bi-sun-fill"></i>
            <i class="bi bi-moon-fill"></i>
        </button>
        @if(isset($headerOptions) && count($headerOptions) > 0)
            <div class="options-dropdown">
                <button class="header-icon-btn options-toggle" onclick="toggleOptionsDropdown()" aria-label="Options">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="options-menu" id="optionsMenu" role="menu">
                    <div class="options-menu__inner">
                        @foreach($headerOptions as $option)
                            @if(($option['type'] ?? 'link') === 'form')
                                <form action="{{ $option['action'] }}" method="{{ $option['method'] ?? 'POST' }}">
                                    @csrf
                                    @if(isset($option['method']) && $option['method'] !== 'POST')
                                        @method($option['method'])
                                    @endif
                                    <button type="submit" class="options-item{{ isset($option['variant']) && $option['variant'] === 'danger' ? ' options-item--danger' : '' }}" role="menuitem" {{ isset($option['confirm']) ? "onclick=\"return confirm('{$option['confirm']}')\"" : '' }}>
                                        @if(isset($option['icon']))
                                            <span class="options-item__icon">
                                                <i class="{{ $option['icon'] }}"></i>
                                            </span>
                                        @endif
                                        <span class="options-item__label">{{ $option['label'] }}</span>
                                    </button>
                                </form>
                            @else
                                <span class="options-item options-item--disabled" role="menuitem">
                                    @if(isset($option['icon']))
                                        <span class="options-item__icon">
                                            <i class="{{ $option['icon'] }}"></i>
                                        </span>
                                    @endif
                                    <span class="options-item__label">{{ $option['label'] }}</span>
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</header>
