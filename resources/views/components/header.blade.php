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

        @if(isset($notifications) && ($notifications['unread']->count() > 0 || $notifications['read']->count() > 0 || $notifications['dismissed']->count() > 0))
            <div class="notifications-dropdown">
                <button class="header-icon-btn notifications-toggle" onclick="toggleNotificationsDropdown()" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    @if($notifications['total'] > 0)
                        <span class="notifications-badge">{{ $notifications['total'] }}</span>
                    @endif
                </button>
                <div class="notifications-menu" id="notificationsMenu">
                    <div class="notifications-menu__inner">
                        <div class="notifications-tabs">
                            <button class="notifications-tab active" data-tab="unread" onclick="switchNotificationTab('unread')">
                                {{ __('dashboard.unread') }}
                                @if($notifications['unread']->count() > 0)
                                    <span class="notifications-tab__badge">{{ $notifications['unread']->count() }}</span>
                                @endif
                            </button>
                            <button class="notifications-tab" data-tab="dismissed" onclick="switchNotificationTab('dismissed')">
                                {{ __('dashboard.dismissed') }}
                            </button>
                        </div>

                        <div class="notifications-content">
                            <div class="notifications-panel active" id="unread-panel">
                                @if($notifications['unread']->count() > 0 || $notifications['read']->count() > 0)
                                    <ul class="notifications-list">
                                        @foreach($notifications['unread'] as $alert)
                                            @php $key = $alert['header_id'] . '-' . $alert['event_id']; @endphp
                                            <li class="notification-item notification-item--unread">
                                                <div class="notification-item__content">
                                                    <i class="bi bi-exclamation-triangle-fill notification-item__icon"></i>
                                                    <span class="notification-item__text">
                                                        {{ __('dashboard.unusual_alert_desc', [
                                                            'header' => $alert['header_name'],
                                                            'percent' => $alert['percent'],
                                                            'old' => $notifications['fmt']->currency($alert['previous_amount']),
                                                            'new' => $notifications['fmt']->currency($alert['current_amount']),
                                                        ]) }}
                                                    </span>
                                                </div>
                                                <div class="notification-item__actions">
                                                    <form action="{{ url('/notifications/mark-read') }}" method="POST" style="display:inline">
                                                        @csrf
                                                        <input type="hidden" name="event_id" value="{{ $alert['event_id'] }}">
                                                        <input type="hidden" name="header_id" value="{{ $alert['header_id'] }}">
                                                        <button type="submit" class="notification-item__btn" title="{{ __('entries.actions.consolidate') }}">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ url('/dashboard/dismiss') }}" method="POST" style="display:inline">
                                                        @csrf
                                                        <input type="hidden" name="event_id" value="{{ $alert['event_id'] }}">
                                                        <input type="hidden" name="header_id" value="{{ $alert['header_id'] }}">
                                                        <button type="submit" class="notification-item__btn notification-item__btn--danger" title="{{ __('dashboard.dismiss') }}">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </li>
                                        @endforeach
                                        @foreach($notifications['read'] as $alert)
                                            <li class="notification-item">
                                                <div class="notification-item__content">
                                                    <i class="bi bi-check-circle notification-item__icon notification-item__icon--read"></i>
                                                    <span class="notification-item__text">
                                                        {{ __('dashboard.unusual_alert_desc', [
                                                            'header' => $alert['header_name'],
                                                            'percent' => $alert['percent'],
                                                            'old' => $notifications['fmt']->currency($alert['previous_amount']),
                                                            'new' => $notifications['fmt']->currency($alert['current_amount']),
                                                        ]) }}
                                                    </span>
                                                </div>
                                                <div class="notification-item__actions">
                                                    <form action="{{ url('/dashboard/dismiss') }}" method="POST" style="display:inline">
                                                        @csrf
                                                        <input type="hidden" name="event_id" value="{{ $alert['event_id'] }}">
                                                        <input type="hidden" name="header_id" value="{{ $alert['header_id'] }}">
                                                        <button type="submit" class="notification-item__btn notification-item__btn--danger" title="{{ __('dashboard.dismiss') }}">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="notifications-empty">
                                        <i class="bi bi-check-circle"></i>
                                        <span>{{ __('dashboard.no_alerts') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="notifications-panel" id="dismissed-panel">
                                @if($notifications['dismissed']->count() > 0)
                                    <ul class="notifications-list">
                                        @foreach($notifications['dismissed'] as $alert)
                                            <li class="notification-item notification-item--dismissed">
                                                <div class="notification-item__content">
                                                    <i class="bi bi-x-circle notification-item__icon"></i>
                                                    <span class="notification-item__text">
                                                        {{ __('dashboard.unusual_alert_desc', [
                                                            'header' => $alert['header_name'],
                                                            'percent' => $alert['percent'],
                                                            'old' => $notifications['fmt']->currency($alert['previous_amount']),
                                                            'new' => $notifications['fmt']->currency($alert['current_amount']),
                                                        ]) }}
                                                    </span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="notifications-empty">
                                        <i class="bi bi-inbox"></i>
                                        <span>{{ __('dashboard.no_results') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <button class="header-icon-btn notifications-toggle" aria-label="Notifications">
                <i class="bi bi-bell"></i>
            </button>
        @endif

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
