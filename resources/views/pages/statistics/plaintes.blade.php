@extends('layaout')

@section('title', 'Statistiques Plaintes & Assignations')

@section('content')
<section class="section">
    <div class="section-body">

        <!-- Hero -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="hero bg-primary text-white rounded-lg p-5 text-center">
                    <h1 class="display-4">Statistiques Plaintes & Assignations</h1>
                    <p class="lead">Suivi détaillé des plaintes et du travail des responsables</p>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-header"><h4>Filtres</h4></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label>Année début</label>
                        <select id="year-start" class="form-control">
                            @foreach(range(now()->year, now()->year - 10) as $y)
                                <option value="{{ $y }}" {{ $y == now()->year - 3 ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Année fin</label>
                        <select id="year-end" class="form-control">
                            @foreach(range(now()->year, now()->year - 10) as $y)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 text-right mt-4">
                        <button id="apply-filters" class="btn btn-primary">Actualiser</button>
                        <button id="export-pdf" class="btn btn-success ml-2">Exporter PDF</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Total Plaintes</h4></div>
                        <div class="card-body" id="kpi-plaintes">0</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Taux Résolution</h4></div>
                        <div class="card-body" id="kpi-resolution">0%</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info"><i class="fas fa-user-tag"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Assignation</h4></div>
                        <div class="card-body" id="kpi-assignations">0</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning"><i class="fas fa-clock"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Délai Moyen</h4></div>
                        <div class="card-body" id="kpi-delai">0 jours</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h4>Évolution Plaintes & Assignations</h4></div>
                    <div class="card-body">
                        <canvas id="evolutionChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h4>Top 5 Responsables</h4></div>
                    <div class="card-body">
                        <canvas id="topResponsablesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let evolutionChart, topChart;
    let currentData = @json($stats);

    function initCharts() {
        // Évolution
        const ctx1 = document.getElementById('evolutionChart').getContext('2d');
        evolutionChart = new Chart(ctx1, {
            type: 'line', data: {
            labels: currentData.labels,
            datasets: [
                { label: 'Plaintes totales', data: currentData.plaintes_total, borderColor: '#ff0844', backgroundColor: 'rgba(255,8,68,0.1)', tension: 0.3, fill: true },
                { label: 'Plaintes résolues', data: currentData.plaintes_resolues, borderColor: '#43e97b', backgroundColor: 'rgba(67,233,123,0.1)', tension: 0.3, fill: true },
                { label: 'Assignations', data: currentData.assignations, borderColor: '#4facfe', backgroundColor: 'rgba(79,172,254,0.1)', tension: 0.3, fill: true }
            ]
        }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });

        // Top responsables
        const ctx2 = document.getElementById('topResponsablesChart').getContext('2d');
        topChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: Object.keys(currentData.top_responsables),
                datasets: [{
                    label: 'Assignations',
                    data: Object.values(currentData.top_responsables),
                    backgroundColor: 'rgba(102,126,234,0.8)'
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // KPI
        document.getElementById('kpi-plaintes').textContent = currentData.total_plaintes.toLocaleString();
        document.getElementById('kpi-resolution').textContent = currentData.taux_resolution;
        document.getElementById('kpi-assignations').textContent = currentData.total_assignations.toLocaleString();
        document.getElementById('kpi-delai').textContent = currentData.delai_moyen_jours + ' jours';
    }

    initCharts();

    // Filtres
    document.getElementById('apply-filters').addEventListener('click', function () {
        const start = document.getElementById('year-start').value;
        const end = document.getElementById('year-end').value;

        axios.post('{{ route("stats.plaintes.update") }}', { start_year: start, end_year: end })
            .then(r => {
                currentData = r.data;
                evolutionChart.destroy();
                topChart.destroy();
                initCharts();
            });
    });

    // Export PDF
    document.getElementById('export-pdf').addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('l', 'mm', 'a4');
        pdf.setFontSize(20);
        pdf.text('Statistiques Plaintes & Assignations', 148, 15, { align: 'center' });

        const elements = document.querySelectorAll('.card');
        let y = 30;
        for (let el of elements) {
            const canvas = await html2canvas(el, { scale: 2 });
            const img = canvas.toDataURL('image/png');
            const width = 280;
            height = canvas.height * width / canvas.width;
            if (y + height > 190) { pdf.addPage(); y = 20; }
            pdf.addImage(img, 'PNG', 15, y, width, height);
            y += height + 10;
        }
        pdf.save('stats-plaintes-assignations.pdf');
    });
});
</script>
@endpush