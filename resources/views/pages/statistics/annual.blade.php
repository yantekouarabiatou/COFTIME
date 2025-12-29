@extends('layaout')

@section('title', 'Statistiques Annuelles')

@section('content')
<section class="section">
    <div class="section-body">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="hero-section bg-gradient-primary text-white rounded-lg p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h1 class="h2 mb-2"><i class="fas fa-chart-line mr-2"></i>Statistiques Annuelles</h1>
                            <p class="mb-0 opacity-75">Analyse comparative sur plusieurs années</p>
                        </div>
                        <div class="col-md-6">
                            <div class="text-right">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-light active" data-years="1">1 an</button>
                                    <button type="button" class="btn btn-outline-light" data-years="3">3 ans</button>
                                    <button type="button" class="btn btn-outline-light" data-years="5">5 ans</button>
                                    <button type="button" class="btn btn-outline-light" data-years="all">Tout</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres Année -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-calendar-alt"></i> Filtres Temporels</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Année de début</label>
                                    <select class="form-control" id="year-start">
                                        @for($i = date('Y'); $i >= date('Y') - 10; $i--)
                                            <option value="{{ $i }}" {{ $i == date('Y') - 2 ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Année de fin</label>
                                    <select class="form-control" id="year-end">
                                        @for($i = date('Y'); $i >= date('Y') - 10; $i--)
                                            <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Type de données</label>
                                    <select class="form-control" id="data-type">
                                        <option value="count">Nombre d'occurrences</option>
                                        <option value="amount">Montants (FCFA)</option>
                                        <option value="both">Les deux</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Groupement</label>
                                    <select class="form-control" id="group-by">
                                        <option value="year">Par année</option>
                                        <option value="quarter">Par trimestre</option>
                                        <option value="month">Par mois</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <button class="btn btn-primary" id="apply-year-filters">
                                    <i class="fas fa-chart-bar"></i> Générer les statistiques
                                </button>
                                <button class="btn btn-secondary" id="export-stats">
                                    <i class="fas fa-download"></i> Exporter PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Annuels -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-gradient-primary">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Année en cours</h4>
                        </div>
                        <div class="card-body">
                            {{ date('Y') }}
                        </div>
                        <div class="card-footer">
                            <small>Total activités: {{ $annualStats['current_year']['total_activities'] }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-gradient-success">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Évolution</h4>
                        </div>
                        <div class="card-body">
                            {{ $annualStats['evolution_percentage'] }}%
                        </div>
                        <div class="card-footer">
                            <small>vs année précédente</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-gradient-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Moyenne annuelle</h4>
                        </div>
                        <div class="card-body">
                            {{ $annualStats['yearly_average'] }}
                        </div>
                        <div class="card-footer">
                            <small>activités par an</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-gradient-warning">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Meilleure année</h4>
                        </div>
                        <div class="card-body">
                            {{ $annualStats['best_year']['year'] ?? 'N/A' }}
                        </div>
                        <div class="card-footer">
                            <small>{{ $annualStats['best_year']['count'] ?? 0 }} activités</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques Annuels -->
        <div class="row">
            <!-- Évolution Annuelle -->
            <div class="col-lg-8">
                <div class="card chart-card-fixed">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-bar"></i> Évolution Annuelle des Activités</h4>
                        <div class="card-header-action">
                            <div class="dropdown">
                                <a href="#" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">Options</a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" data-chart-type="line">Ligne</a>
                                    <a class="dropdown-item" href="#" data-chart-type="bar">Barres</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#" data-stack="true">Empiler</a>
                                    <a class="dropdown-item" href="#" data-stack="false">Séparer</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body chart-body-fixed">
                        <canvas id="annualEvolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Répartition par Type -->
            <div class="col-lg-4">
                <div class="card chart-card-fixed">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-pie"></i> Répartition {{ date('Y') }}</h4>
                    </div>
                    <div class="card-body chart-body-fixed d-flex flex-column">
                        <div class="chart-container-doughnut">
                            <canvas id="currentYearChart"></canvas>
                        </div>
                        <div class="legend-container mt-auto" id="year-legend"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau Comparatif Annuel -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-table"></i> Comparatif Annuel Détaillé</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-striped table-bordered" id="annual-comparison-table">
                                <thead style="position: sticky; top: 0; z-index: 10; background-color: white;">
                                    <tr class="text-center">
                                        <th rowspan="2">Année</th>
                                        <th colspan="2">Intérêts</th>
                                        <th colspan="3">Plaintes</th>
                                        <th colspan="3">Cadeaux/Invitations</th>
                                        <th colspan="3">Clients Audit</th>
                                        <th rowspan="2">Total</th>
                                        <th rowspan="2">Évolution</th>
                                    </tr>
                                    <tr class="text-center">
                                        <!-- Intérêts -->
                                        <th>Total</th>
                                        <th>Actifs</th>

                                        <!-- Plaintes -->
                                        <th>Total</th>
                                        <th>En cours</th>
                                        <th>Résolues</th>

                                        <!-- Cadeaux -->
                                        <th>Total</th>
                                        <th>Acceptés</th>
                                        <th>Refusés</th>

                                        <!-- Clients -->
                                        <th>Total</th>
                                        <th>Actifs</th>
                                        <th>En cours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($annualStats['yearly_data'] as $year => $data)
                                    <tr class="text-center">
                                        <td><strong>{{ $year }}</strong></td>

                                        <!-- Intérêts -->
                                        <td>{{ $data['interets']['total'] ?? 0 }}</td>
                                        <td class="{{ ($data['interets']['actifs'] ?? 0) > 0 ? 'text-success' : 'text-muted' }}">
                                            {{ $data['interets']['actifs'] ?? 0 }}
                                        </td>

                                        <!-- Plaintes -->
                                        <td>{{ $data['plaintes']['total'] ?? 0 }}</td>
                                        <td class="text-warning">{{ $data['plaintes']['en_cours'] ?? 0 }}</td>
                                        <td class="text-success">{{ $data['plaintes']['resolues'] ?? 0 }}</td>

                                        <!-- Cadeaux -->
                                        <td>{{ $data['cadeaux']['total'] ?? 0 }}</td>
                                        <td class="text-success">{{ $data['cadeaux']['acceptes'] ?? 0 }}</td>
                                        <td class="text-danger">{{ $data['cadeaux']['refuses'] ?? 0 }}</td>

                                        <!-- Clients -->
                                        <td>{{ $data['clients']['total'] ?? 0 }}</td>
                                        <td class="text-success">{{ $data['clients']['actifs'] ?? 0 }}</td>
                                        <td class="text-info">{{ $data['clients']['en_cours'] ?? 0 }}</td>

                                        <!-- Total & Évolution -->
                                        <td><strong>{{ $data['total'] ?? 0 }}</strong></td>
                                        <td>
                                            @php
                                                $evolution = $data['evolution'] ?? 0;
                                                $color = $evolution > 0 ? 'success' : ($evolution < 0 ? 'danger' : 'secondary');
                                                $icon = $evolution > 0 ? 'fa-arrow-up' : ($evolution < 0 ? 'fa-arrow-down' : 'fa-minus');
                                            @endphp
                                            <span class="text-{{ $color }}">
                                                <i class="fas {{ $icon }}"></i> {{ abs($evolution) }}%
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light" style="position: sticky; bottom: 0; z-index: 10; background-color: #f8f9fa !important;">
                                    <tr class="text-center">
                                        <td><strong>Moyenne</strong></td>
                                        <td>{{ number_format($annualStats['averages']['interets'], 1) }}</td>
                                        <td>{{ number_format($annualStats['averages']['interets_actifs'], 1) }}</td>
                                        <td>{{ number_format($annualStats['averages']['plaintes'], 1) }}</td>
                                        <td>{{ number_format($annualStats['averages']['plaintes_en_cours'], 1) }}</td>
                                        <td>{{ number_format($annualStats['averages']['plaintes_resolues'], 1) }}</td>
                                        <td>{{ number_format($annualStats['averages']['cadeaux'], 1) }}</td>
                                        <td>{{ number_format($annualStats['averages']['cadeaux_acceptes'], 1) }}</td>
                                        <td>{{ number_format($annualStats['averages']['cadeaux_refuses'], 1) }}</td>
                                        <td>{{ number_format($annualStats['averages']['clients'], 1) }}</td>
                                        <td>{{ number_format($annualStats['averages']['clients_actifs'], 1) }}</td>
                                        <td>{{ number_format($annualStats['averages']['clients_en_cours'], 1) }}</td>
                                        <td><strong>{{ number_format($annualStats['averages']['total'], 1) }}</strong></td>
                                        <td>-</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques par Type -->
        <div class="row mt-4">
            <!-- Intérêts Annuels -->
            <div class="col-lg-6">
                <div class="card chart-card-fixed">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-pie"></i> Intérêts - Évolution Annuelle</h4>
                    </div>
                    <div class="card-body chart-body-fixed">
                        <div class="chart-container-bar">
                            <canvas id="interetsAnnualChart"></canvas>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6 text-center">
                                <h6>Taux d'activité moyen</h6>
                                <h3 class="text-info">{{ $annualStats['interets_stats']['activity_rate'] }}%</h3>
                            </div>
                            <div class="col-6 text-center">
                                <h6>Croissance annuelle</h6>
                                <h3 class="text-success">{{ $annualStats['interets_stats']['growth'] }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plaintes Annuelles -->
            <div class="col-lg-6">
                <div class="card chart-card-fixed">
                    <div class="card-header">
                        <h4><i class="fas fa-exclamation-triangle"></i> Plaintes - Taux de Résolution</h4>
                    </div>
                    <div class="card-body chart-body-fixed">
                        <div class="chart-container-bar">
                            <canvas id="plaintesResolutionChart"></canvas>
                        </div>
                        <div class="row mt-3">
                            <div class="col-4 text-center">
                                <h6>Résolution</h6>
                                <h3 class="text-success">{{ $annualStats['plaintes_stats']['resolution_rate'] }}%</h3>
                            </div>
                            <div class="col-4 text-center">
                                <h6>Temps moyen</h6>
                                <h3 class="text-info">{{ $annualStats['plaintes_stats']['avg_days'] }} jours</h3>
                            </div>
                            <div class="col-4 text-center">
                                <h6>Tendance</h6>
                                @php
                                    $trendIcon = $annualStats['plaintes_stats']['trend'] > 0 ? 'fa-arrow-up text-danger' :
                                                ($annualStats['plaintes_stats']['trend'] < 0 ? 'fa-arrow-down text-success' : 'fa-minus text-secondary');
                                @endphp
                                <h3>
                                    <i class="fas {{ $trendIcon }}"></i>
                                    {{ abs($annualStats['plaintes_stats']['trend']) }}%
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
    .bg-gradient-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important; }
    .bg-gradient-info { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important; }
    .bg-gradient-warning { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important; }
    .bg-gradient-danger { background: linear-gradient(135deg, #ff0844 0%, #ffb199 100%) !important; }

    .card-statistic-1 {
        transition: transform 0.3s;
    }

    .card-statistic-1:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    /* Correction des hauteurs des cards graphiques */
    .chart-card-fixed {
        height: 500px;
        display: flex;
        flex-direction: column;
    }

    .chart-card-fixed .card-body {
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .chart-body-fixed {
        padding: 20px;
        height: calc(100% - 60px);
    }

    /* Conteneurs de graphiques avec hauteurs fixes */
    .chart-container-doughnut {
        position: relative;
        height: 300px;
        margin-bottom: 15px;
    }

    .chart-container-bar {
        position: relative;
        height: 250px;
        margin-bottom: 15px;
    }

    .legend-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        padding-top: 10px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        margin: 5px;
    }

    .legend-color {
        width: 15px;
        height: 15px;
        border-radius: 3px;
        margin-right: 8px;
    }

    .comparison-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    /* Scroll du tableau */
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let annualChart, currentYearChart, interetsChart, plaintesChart;

    // Données initiales injectées par Laravel
    let currentData = @json($annualStats['chart_data']);

    // === INITIALISATION DES 4 GRAPHIQUES ===
    function initCharts(data) {
        // Détruit les anciennes instances
        [annualChart, currentYearChart, interetsChart, plaintesChart].forEach(c => c?.destroy());

        // 1. Évolution annuelle (ligne)
        const ctx1 = document.getElementById('annualEvolutionChart')?.getContext('2d');
        if (ctx1) {
            annualChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label: 'Intérêts',           data: data.interets,           borderColor: '#667eea', backgroundColor: 'rgba(102,126,234,0.1)', tension: 0.4, fill: true },
                        { label: 'Plaintes',            data: data.plaintes,           borderColor: '#ff0844', backgroundColor: 'rgba(255,8,68,0.1)',   tension: 0.4, fill: true },
                        { label: 'Cadeaux/Invitations', data: data.cadeaux,            borderColor: '#4facfe', backgroundColor: 'rgba(79,172,254,0.1)', tension: 0.4, fill: true },
                        { label: 'Clients Audit',       data: data.clients,            borderColor: '#43e97b', backgroundColor: 'rgba(67,233,123,0.1)', tension: 0.4, fill: true }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // 2. Répartition année en cours (doughnut)
        const ctx2 = document.getElementById('currentYearChart')?.getContext('2d');
        if (ctx2) {
            currentYearChart = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Intérêts', 'Plaintes', 'Cadeaux', 'Clients Audit'],
                    datasets: [{
                        data: data.current_year,
                        backgroundColor: ['#667eea', '#ff0844', '#4facfe', '#43e97b'],
                        borderColor: '#fff',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const val = ctx.raw || 0;
                                    const total = ctx.dataset.data.reduce((a,b) => a + b, 0);
                                    const percent = total ? (val / total * 100).toFixed(1) : 0;
                                    return `${ctx.label}: ${val} (${percent}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 3. Intérêts annuels (bar + line)
        const ctx3 = document.getElementById('interetsAnnualChart')?.getContext('2d');
        if (ctx3) {
            interetsChart = new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label: 'Intérêts Actifs', data: data.interets_actifs || [], backgroundColor: 'rgba(102,126,234,0.8)' },
                        { label: 'Total Intérêts',  type: 'line', data: data.interets, borderColor: '#667eea', backgroundColor: 'transparent', tension: 0.3, fill: false }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
            });
        }

        // 4. Plaintes - Résolution (bar + line)
        const ctx4 = document.getElementById('plaintesResolutionChart')?.getContext('2d');
        if (ctx4) {
            plaintesChart = new Chart(ctx4, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label: 'Plaintes Résolues', data: data.plaintes_resolues || [], backgroundColor: 'rgba(67,233,123,0.8)' },
                        { label: 'Total Plaintes',    type: 'line', data: data.plaintes, borderColor: '#ff0844', backgroundColor: 'transparent', tension: 0.3, fill: false }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
            });
        }
    }

    // Chargement initial
    initCharts(currentData);

    // === BOUTONS RAPIDES : 1 an / 3 ans / 5 ans / Tout ===
    document.querySelectorAll('[data-years]').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('[data-years]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const years = this.getAttribute('data-years');
            const end = new Date().getFullYear();
            let start = end;

            if (years === 'all') {
                start = end - 10;
            } else {
                start = end - parseInt(years) + 1;
            }

            document.getElementById('year-start').value = start;
            document.getElementById('year-end').value = end;

            applyFilters();
        });
    });

    // === FILTRES MANUELS ===
    document.getElementById('apply-year-filters')?.addEventListener('click', applyFilters);

    function applyFilters() {
        const startYear = parseInt(document.getElementById('year-start')?.value);
        const endYear   = parseInt(document.getElementById('year-end')?.value);

        if (!startYear || !endYear || startYear > endYear) {
            Swal.fire('Attention', 'Vérifiez les années sélectionnées', 'warning');
            return;
        }

        Swal.fire({
            title: 'Chargement...',
            text: 'Mise à jour des statistiques',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        axios.post('{{ route("stats.annual.update") }}', {
            start_year: startYear,
            end_year: endYear,
            data_type: document.getElementById('data-type')?.value || 'count',
            group_by: document.getElementById('group-by')?.value || 'year'
        })
        .then(res => {
            currentData = res.data.chart_data;
            initCharts(currentData);
            Swal.close();
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Erreur', err.response?.data?.message || 'Impossible de charger les données', 'error');
        });
    }

    // === EXPORT PDF (version propre et sans erreur) ===
    document.getElementById('export-stats')?.addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('l', 'mm', 'a4');

        pdf.setFontSize(22);
        pdf.text('Statistiques Annuelles - COFIMA', 148, 20, { align: 'center' });

        const cards = [
            document.querySelector('.hero-section'),
            document.querySelector('#annualEvolutionChart')?.closest('.card'),
            document.querySelector('#currentYearChart')?.closest('.card'),
            document.querySelector('#interetsAnnualChart')?.closest('.card'),
            document.querySelector('#plaintesResolutionChart')?.closest('.card')
        ].filter(Boolean);

        let yPosY = 35;

        for (const card of cards) {
            try {
                const canvas = await html2canvas(card, { scale: 2, backgroundColor: '#ffffff', useCORS: true });
                const imgData = canvas.toDataURL('image/png');
                const imgWidth = 280;
                const imgHeight = canvas.height * imgWidth / canvas.width;

                if (yPosY + imgHeight > 190) {
                    pdf.addPage();
                    yPosY = 20;
                }

                pdf.addImage(imgData, 'PNG', 15, yPosY, imgWidth, imgHeight);
                yPosY += imgHeight + 12;
            } catch (e) {
                console.warn('Card non capturée :', e);
            }
        }

        pdf.save(`stats-annuelles-${new Date().toISOString().slice(0,10)}.pdf`);
    });

    // === CHANGEMENT TYPE GRAPHIQUE (ligne / barre) ===
    document.querySelectorAll('[data-chart-type]').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            const type = el.getAttribute('data-chart-type'); // 'line' ou 'bar'
            if (annualChart) {
                annualChart.config.type = type;
                annualChart.update();
            }
        });
    });

    // === MODE EMPILÉ / SÉPARÉ ===
    document.querySelectorAll('[data-stack]').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            const stacked = el.getAttribute('data-stack') === 'true';
            if (annualChart) {
                annualChart.options.scales.x.stacked = stacked;
                annualChart.options.scales.y.stacked = stacked;
                annualChart.update();
            }
        });
    });
});
</script>
@endpush
