@php
    $currentMonth = $currentMonth ?? now();
    $monthDate = \Carbon\Carbon::parse($currentMonth);
    $isCurrentMonth = $monthDate->isSameMonth(now()) && $monthDate->isSameYear(now());
    $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
    $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
    $currentMonthParam = $monthDate->format('Y-m');
    
    $monthClosureService = app(\App\Services\MonthClosureService::class);
    $isMonthClosed = $monthClosureService->isMonthClosed($monthDate->year, $monthDate->month);

    $baseQueryParams = collect(request()->query())->except('month')->toArray();
    $baseUrl = request()->url();
    $buildUrl = function(string $month) use ($baseUrl, $baseQueryParams) {
        $params = array_merge($baseQueryParams, ['month' => $month]);
        return $baseUrl . '?' . http_build_query($params);
    };
@endphp

<div class="month-nav-flat">
    <a href="{{ $buildUrl($prevMonth) }}" class="btn-arrow">
        <i class="bi bi-chevron-left"></i>
    </a>
    <a href="{{ $buildUrl(now()->format('Y-m')) }}"
       class="month-text-flat {{ $isCurrentMonth ? 'current-month' : '' }}">
        {{ $monthDate->translatedFormat('F Y') }}
        @if($isMonthClosed)
            <i class="bi bi-lock-fill month-lock closed"></i>
        @else
            <i class="bi bi-unlock-fill month-lock open"></i>
        @endif
    </a>
    <a href="{{ $buildUrl($nextMonth) }}" class="btn-arrow">
        <i class="bi bi-chevron-right"></i>
    </a>
</div>
