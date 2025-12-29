@extends('layaout')

@section('title', 'Rapport Annuel - Indépendances')

@section('content')
<section class="section">
    <div class="section-body">
        <!-- En-tête du Rapport -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="report-header bg-gradient-indigo text-white rounded-lg p-4 animate__animated animate__fadeIn">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="report-icon bg-white text-indigo rounded-circle p-3 me-3">
                                    <i class="fas fa-balance-scale fa-2x"></i>
                                </div>
                                <div>
                                    <h1 class="h2 mb-1">Rapport Annuel d'Indépendance</h1>
                                    <p class="mb-0 opacity-75">Analyse des déclarations d'indépendance {{ date('Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Métriques Principales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card animate__animated animate__fadeInUp" data-wow-delay="0.1s">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Déclarations</h6>
                                <h2 class="mb-0" id="total-count">{{ $stats['total_declarations'] }}</h2>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up"></i> {{ $stats['evolution_percentage'] }}% vs 2024
                                </small>
                            </div>
                            <div class="metric-icon bg-primary">
                                <i class="fas fa-file-contract"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card metric-card animate__animated animate__fadeInUp" data-wow-delay="0.2s">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Frais Totaux</h6>
                                <h2 class="mb-0" id="total-fees">{{ number_format($stats['total_frais']/1000000, 1) }}M</h2>
                                <small class="text-muted">FCFA</small>
                            </div>
                            <div class="metric-icon bg-success">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card metric-card animate__animated animate__fadeInUp" data-wow-delay="0.3s">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Honoraires</h6>
                                <h2 class="mb-0" id="total-honoraires">{{ number_format($stats['total_honoraires']/1000000, 1) }}M</h2>
                                <small class="text-info">{{ $stats['honoraires_avg'] }}K en moyenne</small>
                            </div>
                            <div class="metric-icon bg-warning">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card metric-card animate__animated animate__fadeInUp" data-wow-delay="0.4s">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Expérience Moyenne</h6>
                                <h2 class="mb-0" id="avg-experience">{{ $stats['avg_experience'] }}</h2>
                                <small class="text-dark">années</small>
                            </div>
                            <div class="metric-icon bg-info">
                                <i class="fas fa-user-tie"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques Principaux -->
        <div class="row mb-4">
            <!-- Évolution Mensuelle -->
            <div class="col-xl-8 col-lg-7">
                <div class="card animate__animated animate__fadeInLeft">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Évolution Mensuelle des Déclarations
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                <i class="fas fa-filter"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" data-metric="count">Nombre</a>
                                <a class="dropdown-item" href="#" data-metric="frais">Frais</a>
                                <a class="dropdown-item" href="#" data-metric="honoraires">Honoraires</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="monthlyEvolutionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Répartition par Type -->
            <div class="col-xl-4 col-lg-5">
                <div class="card animate__animated animate__fadeInRight">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie text-success me-2"></i>
                            Répartition par Type d'Entité
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="entityTypeChart"></canvas>
                        </div>
                        <div class="legend-container mt-3" id="entity-legend"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analyse Financière -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar text-warning me-2"></i>
                            Analyse Financière Comparative
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="financial-card text-center p-4">
                                    <div class="financial-icon mb-3">
                                        <i class="fas fa-search-dollar fa-3x text-info"></i>
                                    </div>
                                    <h4 class="mb-2">{{ number_format($stats['avg_frais_audit']/1000, 1) }}K</h4>
                                    <p class="text-muted mb-0">Frais d'Audit Moyens</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: {{ ($stats['avg_frais_audit']/$stats['max_frais_audit'])*100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="financial-card text-center p-4">
                                    <div class="financial-icon mb-3">
                                        <i class="fas fa-calculator fa-3x text-success"></i>
                                    </div>
                                    <h4 class="mb-2">{{ number_format($stats['avg_frais_non_audit']/1000, 1) }}K</h4>
                                    <p class="text-muted mb-0">Frais Non-Audit Moyens</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ ($stats['avg_frais_non_audit']/$stats['max_frais_non_audit'])*100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="financial-card text-center p-4">
                                    <div class="financial-icon mb-3">
                                        <i class="fas fa-percentage fa-3x text-info"></i>
                                    </div>
                                    <h4 class="mb-2">{{ $stats['ratio_frais_non_audit'] }}%</h4>
                                    <p class="text-muted mb-0">Ratio Frais Non-Audit</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: {{ $stats['ratio_frais_non_audit'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Clients & Distribution -->
        <div class="row mb-4">
            <!-- Top Clients -->
            <div class="col-xl-6">
                <div class="card animate__animated animate__fadeInLeft">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-crown text-warning me-2"></i>
                            Top 5 Clients par Volume
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Client</th>
                                        <th class="text-end">Frais Totaux</th>
                                        <th class="text-end">Honoraires</th>
                                        <th class="text-center">Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['top_clients'] as $client)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="client-avatar bg-primary text-white rounded-circle me-3">
                                                    {{ substr($client['nom_client'], 0, 2) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ Str::limit($client['nom_client'], 25) }}</div>
                                                    <small class="text-muted">{{ $client['siege_social'] ?? 'Non spécifié' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($client['total_frais']/1000, 1) }}K
                                        </td>
                                        <td class="text-end text-success">
                                            {{ number_format($client['total_honoraires']/1000, 1) }}K
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                {{ $client['type_entite'] }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribution d'Expérience -->
            <div class="col-xl-6">
                <div class="card animate__animated animate__fadeInRight">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-area text-info me-2"></i>
                            Distribution d'Expérience
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="experienceDistributionChart"></canvas>
                        </div>
                        <div class="row mt-3 text-center">
                            <div class="col-4">
                                <div class="stat-box">
                                    <h4 class="text-primary mb-1">{{ $stats['exp_0_5'] }}%</h4>
                                    <small class="text-muted">0-5 ans</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-box">
                                    <h4 class="text-success mb-1">{{ $stats['exp_5_10'] }}%</h4>
                                    <small class="text-muted">5-10 ans</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-box">
                                    <h4 class="text-warning mb-1">{{ $stats['exp_10_plus'] }}%</h4>
                                    <small class="text-muted">10+ ans</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions d'Indépendance -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-question-circle text-danger me-2"></i>
                            Analyse des Questions d'Indépendance
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="questionsChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-column justify-content-center h-100">
                                    <div class="mb-4">
                                        <h3 class="text-primary mb-1">{{ $stats['questions_oui'] }}</h3>
                                        <p class="text-muted mb-0">Déclarations avec questions</p>
                                        <div class="progress mt-2" style="height: 8px;">
                                            <div class="progress-bar bg-primary"
                                                 style="width: {{ ($stats['questions_oui']/$stats['total_declarations'])*100 }}%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-success mb-1">{{ $stats['questions_non'] }}</h3>
                                        <p class="text-muted mb-0">Déclarations sans questions</p>
                                        <div class="progress mt-2" style="height: 8px;">
                                            <div class="progress-bar bg-success"
                                                 style="width: {{ ($stats['questions_non']/$stats['total_declarations'])*100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Équipes & Responsables -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users text-info me-2"></i>
                            Performance des Équipes d'Audit
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($stats['top_responsables'] as $responsable)
                            <div class="col-md-4 mb-3">
                                <div class="team-card">
                                    <div class="d-flex align-items-center">
                                        <div class="team-avatar bg-gradient-dark text-white rounded-circle me-3">
                                            {{ substr($responsable['name'], 0, 2) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $responsable['name'] }}</h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ $responsable['count'] }} missions</small>
                                                <span class="badge bg-primary">{{ number_format($responsable['total_frais']/1000, 1) }}K</span>
                                            </div>
                                            <div class="progress mt-2" style="height: 4px;">
                                                <div class="progress-bar bg-primary" style="width: {{ ($responsable['count']/$stats['max_mission_count'])*100 }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Synthèse Annuelle -->
        <div class="row">
            <div class="col-12">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-check text-success me-2"></i>
                            Synthèse Annuelle
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-calendar-alt fa-2x text-info"></i>
                                    </div>
                                    <h4>{{ $stats['declarations_per_month'] }}</h4>
                                    <p class="text-muted mb-0">Déclarations / mois</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-chart-pie fa-2x text-success"></i>
                                    </div>
                                    <h4>{{ $stats['clients_par_type']['SA'] ?? 0 }}</h4>
                                    <p class="text-muted mb-0">Sociétés Anonymes</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-building fa-2x text-warning"></i>
                                    </div>
                                    <h4>{{ $stats['clients_par_type']['SARL'] ?? 0 }}</h4>
                                    <p class="text-muted mb-0">SARL</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-balance-scale-right fa-2x text-info"></i>
                                    </div>
                                    <h4>{{ $stats['compliance_rate'] }}%</h4>
                                    <p class="text-muted mb-0">Taux de Conformité</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="report-footer bg-light rounded p-3 text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Rapport généré le {{ now()->format('d/m/Y à H:i') }} |
                        Données couvrant la période du {{ $stats['date_range']['start'] }} au {{ $stats['date_range']['end'] }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Thème Indigo Professionnel */
    .bg-gradient-indigo {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
    }

    .bg-primary { background-color: #6366f1 !important; }
    .bg-success { background-color: #10b981 !important; }
    .bg-warning { background-color: #f59e0b !important; }
    .bg-info { background-color: #06b6d4 !important; }
    .bg-danger { background-color: #ef4444 !important; }

    .text-indigo { color: #6366f1 !important; }

    /* Cards Animées */
    .metric-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .metric-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    /* Graphiques */
    .chart-container {
        position: relative;
    }

    /* Avatars Clients */
    .client-avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.875rem;
    }

    /* Cartes d'Équipe */
    .team-card {
        padding: 1rem;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .team-card:hover {
        background: #f1f5f9;
        border-color: #6366f1;
    }

    .team-avatar {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
    }

    /* Cartes Financières */
    .financial-card {
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .financial-card:hover {
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .financial-icon {
        opacity: 0.8;
    }

    /* Synthèse */
    .summary-item {
        border-right: 1px solid #e2e8f0;
    }

    .summary-item:last-child {
        border-right: none;
    }

    .summary-icon {
        opacity: 0.7;
    }

    /* Animations */
    .animate__animated {
        animation-duration: 0.8s;
    }

    /* Scrollbar personnalisée */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    /* Légende */
    .legend-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        margin-right: 8px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/wow.js@1.2.2/dist/wow.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"></script>
<script>
    $(document).ready(function() {
        // Initialiser les animations
        new WOW().init();

        // Données du contrôleur
        const stats = @json($stats);

        // Graphique d'évolution mensuelle
        const monthlyCtx = document.getElementById('monthlyEvolutionChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: stats.monthly_data.labels,
                datasets: [{
                    label: 'Déclarations',
                    data: stats.monthly_data.counts,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [3, 3]
                        },
                        ticks: {
                            callback: function(value) {
                                return value;
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Graphique des types d'entité
        const entityCtx = document.getElementById('entityTypeChart').getContext('2d');
        const entityChart = new Chart(entityCtx, {
            type: 'doughnut',
            data: {
                labels: stats.entity_types.labels,
                datasets: [{
                    data: stats.entity_types.data,
                    backgroundColor: [
                        '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label;
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 2000
                }
            }
        });

        // Légende pour les types d'entité
        const entityLegend = document.getElementById('entity-legend');
        stats.entity_types.labels.forEach((label, index) => {
            const color = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'][index];
            const div = document.createElement('div');
            div.className = 'legend-item';
            div.innerHTML = `
                <span class="legend-color" style="background-color: ${color}"></span>
                <span>${label} (${stats.entity_types.data[index]})</span>
            `;
            entityLegend.appendChild(div);
        });

        // Graphique de distribution d'expérience
        const expCtx = document.getElementById('experienceDistributionChart').getContext('2d');
        const expChart = new Chart(expCtx, {
            type: 'bar',
            data: {
                labels: ['0-5 ans', '5-10 ans', '10+ ans'],
                datasets: [{
                    data: [stats.exp_0_5, stats.exp_5_10, stats.exp_10_plus],
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)'
                    ],
                    borderColor: [
                        '#6366f1',
                        '#10b981',
                        '#f59e0b'
                    ],
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Graphique des questions d'indépendance
        const questionsCtx = document.getElementById('questionsChart').getContext('2d');
        const questionsChart = new Chart(questionsCtx, {
            type: 'polarArea',
            data: {
                labels: ['Avec Questions', 'Sans Questions'],
                datasets: [{
                    data: [stats.questions_oui, stats.questions_non],
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.7)',
                        'rgba(16, 185, 129, 0.7)'
                    ],
                    borderColor: [
                        '#6366f1',
                        '#10b981'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                },
                scales: {
                    r: {
                        ticks: {
                            display: false
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 2000
                }
            }
        });

        // Changer la métrique de l'évolution mensuelle
        $('[data-metric]').click(function(e) {
            e.preventDefault();
            const metric = $(this).data('metric');

            let data, label, color;
            switch(metric) {
                case 'frais':
                    data = stats.monthly_data.frais;
                    label = 'Frais (FCFA)';
                    color = '#10b981';
                    break;
                case 'honoraires':
                    data = stats.monthly_data.honoraires;
                    label = 'Honoraires (FCFA)';
                    color = '#f59e0b';
                    break;
                default:
                    data = stats.monthly_data.counts;
                    label = 'Déclarations';
                    color = '#6366f1';
            }

            monthlyChart.data.datasets[0].data = data;
            monthlyChart.data.datasets[0].label = label;
            monthlyChart.data.datasets[0].borderColor = color;
            monthlyChart.data.datasets[0].backgroundColor = color.replace(')', ', 0.1)').replace('rgb', 'rgba');
            monthlyChart.update();

            toastr.info(`Affichage: ${label}`);
        });

        // Imprimer le rapport
        $('#print-report').click(function() {
            window.print();
        });

        // Exporter PDF
        $('#export-pdf').click(function() {
            toastr.info('Export PDF en cours de développement...');
            // Ici vous intégreriez une bibliothèque comme jsPDF ou html2pdf
        });

        // Animation des compteurs
        animateCounter('#total-count', stats.total_declarations);
        animateCounter('#total-fees', stats.total_frais/1000000, 1);
        animateCounter('#total-honoraires', stats.total_honoraires/1000000, 1);
        animateCounter('#avg-experience', stats.avg_experience, 1);

        function animateCounter(selector, target, decimals = 0) {
            const element = $(selector);
            const start = 0;
            const duration = 2000;
            const startTime = performance.now();

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                const current = start + (target - start) * progress;
                element.text(decimals > 0 ? current.toFixed(decimals) : Math.round(current));

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }

            requestAnimationFrame(update);
        }
    });
</script>
@endpush
