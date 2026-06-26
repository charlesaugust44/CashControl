@php
    $currentMonth = $currentMonth ?? now();
    $monthDate = \Carbon\Carbon::parse($currentMonth);
    $isCurrentMonth = $monthDate->isSameMonth(now()) && $monthDate->isSameYear(now());
    $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
    $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
    $currentMonthParam = $monthDate->format('Y-m');
@endphp

<div class="month-nav-flat">
    <a href="{{ request()->url() }}?month={{ $prevMonth }}" class="btn-arrow">
        <i class="bi bi-chevron-left"></i>
    </a>
    <a href="{{ request()->url() }}?month={{ now()->format('Y-m') }}"
       class="month-text-flat {{ $isCurrentMonth ? 'current-month' : '' }}">
        {{ $monthDate->format('F Y') }}
    </a>
    <a href="{{ request()->url() }}?month={{ $nextMonth }}" class="btn-arrow">
        <i class="bi bi-chevron-right"></i>
    </a>
</div>
