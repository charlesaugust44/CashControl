import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

const colors = {
    primary: isDark ? '#6380f0' : '#4361ee',
    success: isDark ? '#34d399' : '#10b981',
    danger: isDark ? '#f87171' : '#ef4444',
    text: isDark ? '#9ca3b8' : '#6b7280',
    grid: isDark ? '#2a2d3e' : '#e5e7eb',
    surface: isDark ? '#1a1d2e' : '#ffffff',
};

function initBalanceHistory(canvasId, data) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: data.label,
                data: data.balances,
                borderColor: colors.primary,
                backgroundColor: colors.primary + '1a',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: colors.primary,
                pointBorderColor: colors.surface,
                pointBorderWidth: 2,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#2a2d3e' : '#111827',
                    titleColor: '#f1f3f8',
                    bodyColor: '#f1f3f8',
                    borderColor: colors.grid,
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                }
            },
            scales: {
                x: {
                    grid: { color: colors.grid, drawBorder: false },
                    ticks: { color: colors.text, font: { size: 11 } },
                },
                y: {
                    grid: { color: colors.grid, drawBorder: false },
                    ticks: { color: colors.text, font: { size: 11 } },
                }
            }
        }
    });
}

function initIncomeVsExpenses(canvasId, data) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: data.incomeLabel,
                    data: data.income,
                    backgroundColor: colors.success + 'cc',
                    borderColor: colors.success,
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.6,
                },
                {
                    label: data.expenseLabel,
                    data: data.expense,
                    backgroundColor: colors.danger + 'cc',
                    borderColor: colors.danger,
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: colors.text,
                        usePointStyle: true,
                        pointStyle: 'rectRounded',
                        font: { size: 11 },
                        padding: 16,
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? '#2a2d3e' : '#111827',
                    titleColor: '#f1f3f8',
                    bodyColor: '#f1f3f8',
                    borderColor: colors.grid,
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: colors.text, font: { size: 11 } },
                },
                y: {
                    grid: { color: colors.grid, drawBorder: false },
                    ticks: { color: colors.text, font: { size: 11 } },
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const historyEl = document.getElementById('balanceHistoryChart');
    if (historyEl) {
        const data = JSON.parse(historyEl.dataset.chart);
        initBalanceHistory('balanceHistoryChart', data);
    }

    const breakdownEl = document.getElementById('incomeVsExpensesChart');
    if (breakdownEl) {
        const data = JSON.parse(breakdownEl.dataset.chart);
        initIncomeVsExpenses('incomeVsExpensesChart', data);
    }
});

document.addEventListener('theme-changed', () => {
    Chart.instances = {};
    const historyEl = document.getElementById('balanceHistoryChart');
    if (historyEl) {
        const data = JSON.parse(historyEl.dataset.chart);
        initBalanceHistory('balanceHistoryChart', data);
    }

    const breakdownEl = document.getElementById('incomeVsExpensesChart');
    if (breakdownEl) {
        const data = JSON.parse(breakdownEl.dataset.chart);
        initIncomeVsExpenses('incomeVsExpensesChart', data);
    }
});
