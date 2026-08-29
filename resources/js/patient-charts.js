import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const severityLabels = ['Minimal', 'Mild', 'Moderate', 'Moderately severe', 'Severe'];

const trendCanvas = document.getElementById('severity-trend-chart');
if (trendCanvas) {
    const data = JSON.parse(trendCanvas.dataset.chart);

    new Chart(trendCanvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                { label: 'PHQ-9 severity', data: data.phq9, borderColor: '#1f7a7d', backgroundColor: 'rgba(31,122,125,0.12)', spanGaps: true, tension: 0.3 },
                { label: 'GAD-7 severity', data: data.gad7, borderColor: '#c2417e', backgroundColor: 'rgba(194,65,126,0.12)', spanGaps: true, tension: 0.3 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    min: 0,
                    max: 4,
                    ticks: { stepSize: 1, callback: (value) => severityLabels[value] ?? value },
                },
            },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: (context) => `${context.dataset.label}: ${severityLabels[context.raw] ?? 'Not scored'}` } },
            },
        },
    });
}

const symptomsCanvas = document.getElementById('symptom-frequency-chart');
if (symptomsCanvas) {
    const data = JSON.parse(symptomsCanvas.dataset.chart);
    const labels = Object.keys(data).map((key) => key.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase()));

    new Chart(symptomsCanvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{ label: 'Times reported', data: Object.values(data), backgroundColor: '#1f7a7d' }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: { x: { ticks: { stepSize: 1, precision: 0 } } },
            plugins: { legend: { display: false } },
        },
    });
}

const moodLabels = ['Very low', 'Low', 'Neutral', 'Good', 'Great'];
document.querySelectorAll('[data-mood-trend-chart]').forEach((moodCanvas) => {
    const data = JSON.parse(moodCanvas.dataset.chart);

    new Chart(moodCanvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Mood',
                data: data.scores,
                borderColor: '#d97706',
                backgroundColor: 'rgba(251, 191, 36, 0.16)',
                pointBackgroundColor: '#f59e0b',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    min: 1,
                    max: 5,
                    ticks: { stepSize: 1, callback: (value) => moodLabels[value - 1] ?? value },
                    grid: { color: 'rgba(148, 163, 184, 0.16)' },
                },
                x: { grid: { display: false } },
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (context) => moodLabels[context.raw - 1] ?? `Mood ${context.raw}` } },
            },
        },
    });
});
