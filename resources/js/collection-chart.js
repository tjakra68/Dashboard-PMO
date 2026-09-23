import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
);

const compact = (value) => {
    const absolute = Math.abs(value);
    const [divider, suffix] =
        absolute >= 1e9
            ? [1e9, 'B']
            : absolute >= 1e6
              ? [1e6, 'M']
              : absolute >= 1e3
                ? [1e3, 'K']
                : [1, ''];

    return `${(value / divider).toFixed(2)}${suffix}`;
};

const cumulativeLabels = {
    id: 'cumulativeLabels',
    afterDatasetsDraw(chart) {
        const meta = chart.getDatasetMeta(1);

        if (!meta || meta.hidden) {
            return;
        }

        const { ctx } = chart;
        ctx.save();
        ctx.font = '600 10px Figtree, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        meta.data.forEach((point, index) => {
            const text = compact(chart.data.datasets[1].data[index]);
            const width = ctx.measureText(text).width + 8;

            ctx.fillStyle = '#ffffff';
            ctx.strokeStyle = '#e2e8f0';
            ctx.beginPath();
            ctx.roundRect(point.x - width / 2, point.y - 20, width, 16, 4);
            ctx.fill();
            ctx.stroke();

            ctx.fillStyle = '#334155';
            ctx.fillText(text, point.x, point.y - 12);
        });

        ctx.restore();
    },
};

const currentMonthMarker = {
    id: 'currentMonthMarker',
    afterDatasetsDraw(chart, _args, options) {
        if (!options.monthIndex && options.monthIndex !== 0) {
            return;
        }

        const x = chart.scales.x.getPixelForValue(options.monthIndex);
        const { ctx, chartArea } = chart;

        ctx.save();
        ctx.setLineDash([4, 4]);
        ctx.strokeStyle = '#94a3b8';
        ctx.beginPath();
        ctx.moveTo(x, chartArea.top);
        ctx.lineTo(x, chartArea.bottom);
        ctx.stroke();
        ctx.setLineDash([]);

        const text = `Collected ${compact(options.collected)}`;
        ctx.font = '600 11px Figtree, sans-serif';
        const width = ctx.measureText(text).width + 16;
        const left = Math.min(
            Math.max(x - width / 2, chartArea.left),
            chartArea.right - width,
        );

        ctx.fillStyle = '#7c3aed';
        ctx.beginPath();
        ctx.roundRect(left, chartArea.bottom - 26, width, 20, 6);
        ctx.fill();

        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(text, left + width / 2, chartArea.bottom - 16);
        ctx.restore();
    },
};

const buildGradient = (context) => {
    const { ctx, chartArea } = context.chart;

    if (!chartArea) {
        return '#60a5fa';
    }

    const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
    gradient.addColorStop(0, '#bfdbfe');
    gradient.addColorStop(1, '#2563eb');

    return gradient;
};

export const renderCollectionChart = (canvas) => {
    const payload = JSON.parse(canvas.dataset.chart);

    return new Chart(canvas, {
        data: {
            labels: payload.labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Forecast',
                    data: payload.forecast,
                    backgroundColor: buildGradient,
                    borderRadius: 6,
                    maxBarThickness: 46,
                    yAxisID: 'y',
                },
                {
                    type: 'line',
                    label: 'Cummulative Forecast',
                    data: payload.cumulativeForecast,
                    borderColor: '#f97316',
                    backgroundColor: '#f97316',
                    pointRadius: 3,
                    tension: 0.1,
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 28 } },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    align: 'end',
                    labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8 },
                },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.dataset.label}: ${compact(context.parsed.y)}`,
                    },
                },
                currentMonthMarker: {
                    monthIndex: payload.monthIndex,
                    collected: payload.collected,
                },
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    title: { display: true, text: 'Forecast' },
                    ticks: { callback: (value) => compact(value) },
                    grid: { color: '#eef2f7' },
                },
                y1: {
                    position: 'right',
                    title: { display: true, text: 'Cumulative Forecast' },
                    ticks: { callback: (value) => compact(value) },
                    grid: { display: false },
                },
            },
        },
        plugins: [cumulativeLabels, currentMonthMarker],
    });
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-collection-chart]').forEach(renderCollectionChart);
});
