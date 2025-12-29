@extends('layaout')

@section('title', 'Statistiques Conflits d\'Intérêts')

@section('content')
<section class="section">
    <div class="section-body">

        <!-- Hero -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-hero bg-primary text-white">
                    <div class="card-body text-center py-5">
                        <h1 class="display-4 mb-3">Statistiques Conflits d'Intérêts</h1>
                        <p class="lead">Suivi annuel des déclarations et traitement par les responsables</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-header"><h4>Filtres Années</h4></div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label>Année de début</label>
                        <select id="year-start" class="form-control">
                            @foreach(range(now()->year, now()->year - 10) as $y)
                                <option value="{{ $y }}" {{ $y == now()->year - 4 ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label>Année de fin</label>
                        <select id="year-end" class="form-control">
                            @foreach(range(now()->year, now()->year - 10) as $y)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button id="apply-filters" class="btn btn-warning btn-block">Actualiser</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning"><i class="fas fa-file-alt"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Total Déclarations</h4></div>
                        <div class="card-body RFS" id="kpi-total">0</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger"><i class="fas fa-fire"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Actifs / En cours</h4></div>
                        <div class="card-body" id="kpi-actifs">0</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success"><i class="fas fa-check-double"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Taux de traitement</h4></div>
                        <div class="card-body" id="kpi-taux">0%</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-users"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Responsables actifs</h4></div>
                        <div class="card-body" id="kpi-responsables">{{ count($stats['top_responsables']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Évolution Annuelle des Conflits d'Intérêts</h4>
                        <button id="export-pdf" class="btn btn-dark btn-sm">Exporter PDF</button>
                    </div>
                    <div class="card-body">
                        <canvas id="evolutionChart" height="120"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h4>Top Responsables (nombre de dossiers)</h4></div>
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
        // Évolution annuelle
        const ctx1 = document.getElementById('evolutionChart').getContext('2d');
        if (evolutionChart) evolutionChart.destroy();
        evolutionChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: currentData.labels,
                datasets: [
                    { label: 'Total déclarations', data: currentData.total_par_annee, borderColor: '#ffc107', backgroundColor: 'rgba(255,193,7,0.15)', tension: 0.4, fill: true },
                    { label: 'Actifs / En cours',  data: currentData.actifs_par_annee, borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,0.15)', tension: 0.4, fill: true },
                    { label: 'Traités',            data: currentData.traites_par_annee, borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,0.15)', tension: 0.4, fill: true }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Top responsables
        const ctx2 = document.getElementById('topResponsablesChart').getContext('2d');
        if (topChart) topChart.destroy();
        topChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: Object.keys(currentData.top_responsables),
                datasets: [{
                    label: 'Nombre de dossiers',
                    data: Object.values(currentData.top_responsables),
                    backgroundColor: 'rgba(0,123,255,0.8)',
                    borderColor: '#007bff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ctx.raw + ' dossier(s)' } }
                },
                scales: { y: { beginAtZero: true } }
            }
        });

        // KPI
        document.getElementById('kpi-total').textContent = currentData.total_interets.toLocaleString();
        document.getElementById('kpi-actifs').textContent = currentData.total_actifs.toLocaleString();
        document.getElementById('kpi-taux').textContent = currentData.taux_actifs;
    }

    initCharts();

    // Filtres
    document.getElementById('apply-filters').addEventListener('click', () => {
        const start = document.getElementById('year-start').value;
        const end = document.getElementById('year-end').value;

        axios.post('{{ route("stats.interets.update") }}', {
            start_year: start,
            end_year: end
        }).then(r => {
            currentData = r.data;
            initCharts();
        }).catch(() => Swal.fire('Erreur', 'Impossible de charger les données', 'error'));
    });

    // Export PDF
    document.getElementById('export-pdf').addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('l', 'mm', 'a4');
        pdf.setFontSize(20);
        pdf.text('Statistiques Conflits d\'Intérêts - ' + new Date().getFullYear(), 148, 20, { align: 'center' });

        const cards = document.querySelectorAll('.card');
        let y = 35;
        for (let card of cards) {
            const canvas = await html2canvas(card, { scale: 2 });
            const img = canvas.toDataURL('image/png');
            const w = 280;
            const h = canvas.height * w / canvas.width;
            if (y + h > 190) { pdf.addPage(); y = 20; }
            pdf.addImage(img, 'PNG', 15, y, w, h);
            y += h + 10;
        }
        pdf.save('stats-interets-' + new Date().toISOString().slice(0,10) + '.pdf');
    });
});
</script>
@endpush