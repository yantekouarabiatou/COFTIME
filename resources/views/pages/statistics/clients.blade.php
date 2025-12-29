@extends('layaout')

@section('title', 'Rapport Annuel - Clients Audit')

@section('content')
<section class="section">
    <div class="section-body">
        <!-- En-tête du Rapport -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="report-header bg-gradient-primary text-white rounded-lg p-4 animate__animated animate__fadeIn">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="report-icon bg-white text-primary rounded-circle p-3 me-3">
                                    <i class="fas fa-building fa-2x"></i>
                                </div>
                                <div>
                                    <h1 class="h2 mb-1">Rapport Annuel des Clients Audit</h1>
                                    <p class="mb-0 opacity-75">Analyse des clients audit {{ date('Y') }}</p>
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
                                <h6 class="text-muted mb-2">Total Clients</h6>
                                <h2 class="mb-0" id="total-clients">{{ $stats['total_clients'] }}</h2>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up"></i> {{ $stats['evolution_percentage'] }}% vs {{ date('Y')-1 }}
                                </small>
                            </div>
                            <div class="metric-icon bg-primary">
                                <i class="fas fa-users"></i>
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
                                <h6 class="text-muted mb-2">Clients Actifs</h6>
                                <h2 class="mb-0" id="clients-actifs">{{ $stats['clients_actifs'] }}</h2>
                                <small class="text-success">{{ $stats['taux_actifs'] }}% du total</small>
                            </div>
                            <div class="metric-icon bg-warning">
                                <i class="fas fa-chart-line"></i>
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
                                <h6 class="text-muted mb-2">Frais Moyens</h6>
                                <h2 class="mb-0" id="avg-fees">{{ number_format($stats['frais_moyens']/1000, 1) }}K</h2>
                                <small class="text-info">par client</small>
                            </div>
                            <div class="metric-icon bg-info">
                                <i class="fas fa-calculator"></i>
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
                            Évolution Mensuelle des Clients
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                <i class="fas fa-filter"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" data-metric="nouveaux">Nouveaux Clients</a>
                                <a class="dropdown-item" href="#" data-metric="actifs">Clients Actifs</a>
                                <a class="dropdown-item" href="#" data-metric="frais">Frais Totaux</a>
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

            <!-- Répartition par Statut -->
            <div class="col-xl-4 col-lg-5">
                <div class="card animate__animated animate__fadeInRight">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie text-success me-2"></i>
                            Répartition par Statut
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="statusDistributionChart"></canvas>
                        </div>
                        <div class="legend-container mt-3" id="status-legend"></div>
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
                            Analyse Financière Détaillée
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="financial-card text-center p-4">
                                    <div class="financial-icon mb-3">
                                        <i class="fas fa-search-dollar fa-3x text-info"></i>
                                    </div>
                                    <h4 class="mb-2">{{ number_format($stats['total_frais_audit']/1000000, 1) }}M</h4>
                                    <p class="text-muted mb-0">Frais d'Audit</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-info"
                                             style="width: {{ ($stats['total_frais_audit']/$stats['total_frais'])*100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="financial-card text-center p-4">
                                    <div class="financial-icon mb-3">
                                        <i class="fas fa-file-invoice-dollar fa-3x text-success"></i>
                                    </div>
                                    <h4 class="mb-2">{{ number_format($stats['total_frais_autres']/1000000, 1) }}M</h4>
                                    <p class="text-muted mb-0">Autres Frais</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-success"
                                             style="width: {{ ($stats['total_frais_autres']/$stats['total_frais'])*100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="financial-card text-center p-4">
                                    <div class="financial-icon mb-3">
                                        <i class="fas fa-percentage fa-3x text-info"></i>
                                    </div>
                                    <h4 class="mb-2">{{ $stats['ratio_frais_autres'] }}%</h4>
                                    <p class="text-muted mb-0">Ratio Autres Frais</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: {{ $stats['ratio_frais_autres'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="financial-card text-center p-4">
                                    <div class="financial-icon mb-3">
                                        <i class="fas fa-balance-scale fa-3x text-warning"></i>
                                    </div>
                                    <h4 class="mb-2">{{ number_format($stats['frais_moyens_audit']/1000, 1) }}K</h4>
                                    <p class="text-muted mb-0">Audit Moyen</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: {{ ($stats['frais_moyens_audit']/$stats['max_frais_audit'])*100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Clients & Performance -->
        <div class="row mb-4">
            <!-- Top Clients par Frais -->
            <div class="col-xl-6">
                <div class="card animate__animated animate__fadeInLeft">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-crown text-warning me-2"></i>
                            Top 5 Clients par Volume Financier
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Client</th>
                                        <th class="text-end">Frais Audit</th>
                                        <th class="text-end">Autres Frais</th>
                                        <th class="text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['top_clients'] as $client)
                                    @php
                                        // Mapper les statuts aux classes Bootstrap
                                        $badgeClass = match($client['statut'] ?? '') {
                                            'actif' => 'badge bg-success  text-light',
                                            'en_cours' => 'badge bg-primary  text-primary',
                                            'inactif' => 'badge bg-secondary text-secondary',
                                            default => 'badge bg-warning text-warning'
                                        };

                                        $statutText = match($client['statut'] ?? '') {
                                            'actif' => 'Actif',
                                            'en_cours' => 'En cours',
                                            'inactif' => 'Inactif',
                                            default => 'Non défini'
                                        };
                                    @endphp
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
                                        <td class="text-end fw-bold text-dark">
                                            {{ number_format($client['frais_audit']/1000, 1) }}K
                                        </td>
                                        <td class="text-end text-success">
                                            {{ number_format($client['frais_autres']/1000, 1) }}K
                                        </td>
                                        <td class="text-center">
                                            <span class="{{ $badgeClass }}">
                                                {{ $statutText }}
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

            <!-- Performance des Responsables -->
            <div class="col-xl-6">
                <div class="card animate__animated animate__fadeInRight">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tie text-info me-2"></i>
                            Performance des Responsables
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($stats['top_responsables'] as $responsable)
                            <div class="col-md-6 mb-3">
                                <div class="team-card">
                                    <div class="d-flex align-items-center">
                                        <div class="team-avatar bg-gradient-primary text-white rounded-circle me-3">
                                            {{ substr($responsable['name'], 0, 2) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $responsable['name'] }}</h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ $responsable['client_count'] }} clients</small>
                                                <div>
                                                    <span class="badge bg-success text-light">{{ $responsable['actif_count'] }}</span>
                                                    <span class="badge bg-warning ms-1">{{ $responsable['en_cours_count'] }}</span>
                                                </div>
                                            </div>
                                            <div class="progress mt-2" style="height: 4px;">
                                                <div class="progress-bar bg-primary" style="width: {{ ($responsable['total_frais']/$stats['max_responsable_frais'])*100 }}%"></div>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                {{ number_format($responsable['total_frais']/1000, 1) }}K FCFA
                                            </small>
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

        <!-- Répartition Géographique -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            Répartition Géographique des Clients
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="chart-container" style="height: 300px;">
                                    <canvas id="geographicChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="location-stats">
                                    @foreach($stats['top_locations'] as $location)
                                    <div class="location-item mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $location['ville'] }}</h6>
                                                <small class="text-muted">{{ $location['count'] }} clients</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="text-primary fw-bold">{{ number_format($location['total_frais']/1000, 1) }}K</div>
                                                <small class="text-success">{{ $location['actif_count'] }} actifs</small>
                                            </div>
                                        </div>
                                        <div class="progress mt-1" style="height: 4px;">
                                            <div class="progress-bar bg-primary" style="width: {{ ($location['count']/$stats['max_location_count'])*100 }}%"></div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analyse par Statut -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card animate__animated animate__fadeInUp">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area text-primary me-2"></i>
                        Analyse par Statut des Clients
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Actifs -->
                        <div class="col-md-3 mb-3">
                            <div class="status-card text-center p-4 border-start border-4 border-success">
                                <div class="status-icon mb-3">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                                <h2 class="text-dark mb-2">{{ $stats['clients_actifs'] }}</h2>
                                <h6 class="text-muted mb-3">Actifs</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ number_format($stats['frais_actifs']/1000000, 1) }}M FCFA</small>
                                    <span class="badge bg-success bg-opacity-20 text-light">
                                        {{ $stats['taux_actifs'] }}%
                                    </span>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: {{ $stats['taux_actifs'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- En Cours -->
                        <div class="col-md-3 mb-3">
                            <div class="status-card text-center p-4 border-start border-4 border-info">
                                <div class="status-icon mb-3">
                                    <i class="fas fa-sync-alt fa-2x text-info"></i>
                                </div>
                                <h2 class="text-dark mb-2">{{ $stats['clients_en_cours'] }}</h2>
                                <h6 class="text-muted mb-3">En Cours</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ number_format($stats['frais_en_cours']/1000000, 1) }}M FCFA</small>
                                    <span class="badge bg-info bg-opacity-20 text-light">
                                        {{ $stats['taux_en_cours'] }}%
                                    </span>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $stats['taux_en_cours'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Inactifs -->
                        <div class="col-md-3 mb-3">
                            <div class="status-card text-center p-4 border-start border-4 border-secondary">
                                <div class="status-icon mb-3">
                                    <i class="fas fa-pause-circle fa-2x text-secondary"></i>
                                </div>
                                <h2 class="text-dark mb-2">{{ $stats['clients_inactifs'] }}</h2>
                                <h6 class="text-muted mb-3">Inactifs</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ number_format($stats['frais_inactifs']/1000000, 1) }}M FCFA</small>
                                    <span class="badge bg-secondary bg-opacity-20 text-light">
                                        {{ $stats['taux_inactifs'] }}%
                                    </span>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-secondary" style="width: {{ $stats['taux_inactifs'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Sans Statut -->
                        <div class="col-md-3 mb-3">
                            <div class="status-card text-center p-4 border-start border-4 border-warning">
                                <div class="status-icon mb-3">
                                    <i class="fas fa-question-circle fa-2x text-warning"></i>
                                </div>
                                <h2 class="text-dark mb-2">{{ $stats['clients_sans_statut'] }}</h2>
                                <h6 class="text-muted mb-3">Sans Statut</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ number_format($stats['frais_sans_statut']/1000000, 1) }}M FCFA</small>
                                    <span class="badge bg-warning bg-opacity-20 text-light">
                                        {{ $stats['taux_sans_statut'] }}%
                                    </span>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $stats['taux_sans_statut'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Tendance Annuelle -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Tendance Annuelle sur 3 Ans
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="yearlyTrendChart"></canvas>
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
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-user-plus fa-2x text-info"></i>
                                    </div>
                                    <h4>{{ $stats['nouveaux_clients'] }}</h4>
                                    <p class="text-muted mb-0">Nouveaux {{ date('Y') }}</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-retweet fa-2x text-success"></i>
                                    </div>
                                    <h4>{{ $stats['taux_fidelite'] }}%</h4>
                                    <p class="text-muted mb-0">Taux Fidélité</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-money-check-alt fa-2x text-warning"></i>
                                    </div>
                                    <h4>{{ $stats['ratio_frais_autres'] }}%</h4>
                                    <p class="text-muted mb-0">Autres Frais</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-city fa-2x text-info"></i>
                                    </div>
                                    <h4>{{ count($stats['top_locations']) }}</h4>
                                    <p class="text-muted mb-0">Villes Actives</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-tasks fa-2x text-danger"></i>
                                    </div>
                                    <h4>{{ $stats['clients_avec_document'] }}</h4>
                                    <p class="text-muted mb-0">Avec Documents</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-chart-bar fa-2x text-indigo"></i>
                                    </div>
                                    <h4>{{ $stats['croissance_mensuelle'] }}%</h4>
                                    <p class="text-muted mb-0">Croissance Mens.</p>
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
    /* Thème Professionnel */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
    }

    .bg-primary { background-color: #3b82f6 !important; }
    .bg-success { background-color: #10b981 !important; }
    .bg-warning { background-color: #f59e0b !important; }
    .bg-info { background-color: #06b6d4 !important; }
    .bg-danger { background-color: #ef4444 !important; }
    .bg-indigo { background-color: #6366f1 !important; }

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
        border-color: #3b82f6;
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

    /* Cartes Statut */
    .status-card {
        border-radius: 12px;
        border: 2px solid;
        transition: all 0.3s ease;
    }

    .status-card:hover {
        transform: scale(1.02);
    }

    /* Localisation */
    .location-item {
        padding: 1rem;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 4px solid #3b82f6;
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

    /* Version 1 - Design sobre */
    .border-start.border-4 {
        border-left-width: 4px !important;
    }

    .status-icon {
        opacity: 0.9;
    }

    /* Version 2 - Minimaliste */
    .hover-lift {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: #dee2e6;
    }

    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }

    /* Version 3 - Monochrome */
    .stat-card {
        transition: all 0.2s ease;
        border: 1px solid #f8f9fa;
    }

    .stat-card:hover {
        background-color: #f8f9fa;
        border-color: #e9ecef;
    }

    .bg-opacity-10 {
        opacity: 0.1;
    }

    /* Palette de couleurs harmonieuse */
    .text-success { color: #198754 !important; }
    .bg-success { background-color: #198754 !important; }

    .text-primary { color: #0d6efd !important; }
    .bg-primary { background-color: #0d6efd !important; }

    .text-warning { color: #ffc107 !important; }
    .bg-warning { background-color: #ffc107 !important; }

    .text-secondary { color: #6c757d !important; }
    .bg-secondary { background-color: #6c757d !important; }

    /* Progress bars */
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        border-radius: 10px;
    }

</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/wow.js@1.2.2/dist/wow.min.js"></script>
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
                    label: 'Nouveaux Clients',
                    data: stats.monthly_data.nouveaux,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
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
                        intersect: false
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
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Graphique des statuts
        const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: stats.status_distribution.labels,
                datasets: [{
                    data: stats.status_distribution.data,
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
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

        // Légende pour les statuts
        const statusLegend = document.getElementById('status-legend');
        const statusColors = ['#10b981', '#f59e0b', '#ef4444', '#6b7280'];

        stats.status_distribution.labels.forEach((label, index) => {
            const div = document.createElement('div');
            div.className = 'legend-item';
            div.innerHTML = `
                <span class="legend-color" style="background-color: ${statusColors[index]}"></span>
                <span>${label}: ${stats.status_distribution.data[index]}</span>
            `;
            statusLegend.appendChild(div);
        });

        // Graphique géographique
        const geoCtx = document.getElementById('geographicChart').getContext('2d');
        const geoChart = new Chart(geoCtx, {
            type: 'bar',
            data: {
                labels: stats.geographic_data.labels,
                datasets: [{
                    label: 'Nombre de Clients',
                    data: stats.geographic_data.counts,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
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
                        ticks: {
                            precision: 0
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Graphique tendance annuelle
        const trendCtx = document.getElementById('yearlyTrendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: stats.yearly_trend.labels,
                datasets: [{
                    label: 'Clients Totaux',
                    data: stats.yearly_trend.totals,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }, {
                    label: 'Clients Actifs',
                    data: stats.yearly_trend.actifs,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Changer la métrique de l'évolution mensuelle
        $('[data-metric]').click(function(e) {
            e.preventDefault();
            const metric = $(this).data('metric');

            let data, label, color;
            switch(metric) {
                case 'actifs':
                    data = stats.monthly_data.actifs;
                    label = 'Clients Actifs';
                    color = '#10b981';
                    break;
                case 'frais':
                    data = stats.monthly_data.frais_totaux;
                    label = 'Frais Totaux (FCFA)';
                    color = '#f59e0b';
                    break;
                default:
                    data = stats.monthly_data.nouveaux;
                    label = 'Nouveaux Clients';
                    color = '#3b82f6';
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
        });

        // Animation des compteurs
        animateCounter('#total-clients', stats.total_clients);
        animateCounter('#total-fees', stats.total_frais/1000000, 1);
        animateCounter('#clients-actifs', stats.clients_actifs);
        animateCounter('#avg-fees', stats.frais_moyens/1000, 1);

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
