@extends('layaout')

@section('title', 'Rapport Annuel - Cadeaux & Invitations')

@section('content')
<section class="section">
    <div class="section-body">
        <!-- En-tête du Rapport -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="report-header bg-gradient-gift text-white rounded-lg p-4 animate__animated animate__fadeIn">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="report-icon bg-white text-dark rounded-circle p-3 me-3">
                                    <i class="fas fa-gift fa-2x"></i>
                                </div>
                                <div>
                                    <h1 class="h2 mb-1">Rapport Annuel Cadeaux & Invitations</h1>
                                    <p class="mb-0 opacity-75">Analyse {{ date('Y') }} - Éthique et Conformité</p>
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
                                <h2 class="mb-0" id="total-declarations">{{ $stats['total_declarations'] }}</h2>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up"></i> {{ $stats['evolution_percentage'] }}% vs {{ date('Y')-1 }}
                                </small>
                            </div>
                            <div class="metric-icon bg-warning">
                                <i class="fas fa-gifts"></i>
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
                                <h6 class="text-muted mb-2">Valeur Totale</h6>
                                <h2 class="mb-0" id="total-valeur">{{ number_format($stats['total_valeurs']/1000, 1) }}K</h2>
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
                                <h6 class="text-muted mb-2">Taux d'Acceptation</h6>
                                <h2 class="mb-0" id="taux-acceptation">{{ $stats['taux_acceptation'] }}%</h2>
                                <small class="text-success">{{ $stats['acceptes'] }} acceptés</small>
                            </div>
                            <div class="metric-icon bg-primary">
                                <i class="fas fa-check-circle"></i>
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
                                <h6 class="text-muted mb-2">Valeur Moyenne</h6>
                                <h2 class="mb-0" id="valeur-moyenne">{{ number_format($stats['valeur_moyenne'], 0) }}</h2>
                                <small class="text-primary">FCFA par cadeau</small>
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
                            <i class="fas fa-chart-line text-warning me-2"></i>
                            Évolution Mensuelle
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                <i class="fas fa-filter"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" data-metric="nombre">Nombre</a>
                                <a class="dropdown-item" href="#" data-metric="valeur">Valeur</a>
                                <a class="dropdown-item" href="#" data-metric="acceptes">Acceptés</a>
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

            <!-- Répartition par Action -->
            <div class="col-xl-4 col-lg-5">
                <div class="card animate__animated animate__fadeInRight">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie text-success me-2"></i>
                            Décisions Prises
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="actionDistributionChart"></canvas>
                        </div>
                        <div class="legend-container mt-3" id="action-legend"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analyse des Valeurs -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar text-info me-2"></i>
                            Analyse des Valeurs
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="value-card text-center p-4">
                                    <div class="value-icon mb-3">
                                        <i class="fas fa-tag fa-3x text-info"></i>
                                    </div>
                                    <h4 class="mb-2">{{ number_format($stats['max_valeur']/1000, 1) }}K</h4>
                                    <p class="text-muted mb-0">Valeur Maximale</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="value-card text-center p-4">
                                    <div class="value-icon mb-3">
                                        <i class="fas fa-balance-scale fa-3x text-success"></i>
                                    </div>
                                    <h4 class="mb-2">{{ number_format($stats['mediane_valeurs'], 0) }}</h4>
                                    <p class="text-muted mb-0">Valeur Médiane</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ ($stats['mediane_valeurs']/$stats['max_valeur'])*100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="value-card text-center p-4">
                                    <div class="value-icon mb-3">
                                        <i class="fas fa-chart-area fa-3x text-warning"></i>
                                    </div>
                                    <h4 class="mb-2">{{ number_format($stats['total_valeurs']/1000, 1) }}K</h4>
                                    <p class="text-muted mb-0">Valeur Totale</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: {{ ($stats['total_valeurs']/($stats['max_valeur']*10))*100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="value-card text-center p-4">
                                    <div class="value-icon mb-3">
                                        <i class="fas fa-percentage fa-3x text-danger"></i>
                                    </div>
                                    <h4 class="mb-2">{{ $stats['taux_refus'] }}%</h4>
                                    <p class="text-muted mb-0">Taux de Refus</p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-danger" style="width: {{ $stats['taux_refus'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Cadeaux & Performance -->
        <div class="row mb-4">
            <!-- Top Cadeaux par Valeur -->
            <div class="col-xl-6">
                <div class="card animate__animated animate__fadeInLeft">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-crown text-warning me-2"></i>
                            Top 5 par Valeur
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Désignation</th>
                                        <th class="text-end">Valeur</th>
                                        <th class="text-center">Type</th>
                                        <th class="text-center">Décision</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['top_cadeaux'] as $cadeau)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="gift-avatar bg-warning text-white rounded-circle me-3">
                                                    {{ substr($cadeau['nom'], 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ Str::limit($cadeau['nom'], 25) }}</div>
                                                    <small class="text-muted">{{ $cadeau['date_formatted'] ?? 'Date non spécifiée' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            {{ number_format($cadeau['valeurs'], 0) }} FCFA
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info bg-opacity-10 text-light">
                                                {{ $cadeau['cadeau_hospitalite'] ?? 'Non spécifié' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $actionColor = match($cadeau['action_prise'] ?? '') {
                                                    'accepté' => 'success',
                                                    'refusé' => 'danger',
                                                    'en_attente' => 'warning',
                                                    default => 'secondary'
                                                };
                                                $actionText = match($cadeau['action_prise'] ?? '') {
                                                    'accepté' => 'Accepté',
                                                    'refusé' => 'Refusé',
                                                    'en_attente' => 'En attente',
                                                    default => 'Non défini'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $actionColor }} bg-opacity-10 text-{{ $actionColor }}">
                                                {{ $actionText }}
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
                            <i class="fas fa-user-tie text-primary me-2"></i>
                            Éthique des Responsables
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($stats['top_responsables'] as $responsable)
                            <div class="col-md-6 mb-3">
                                <div class="ethics-card">
                                    <div class="d-flex align-items-center">
                                        <div class="ethics-avatar bg-gradient-primary text-white rounded-circle me-3">
                                            {{ substr($responsable['name'], 0, 2) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $responsable['name'] }}</h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ $responsable['declaration_count'] }} déclarations</small>
                                                <div>
                                                    <span class="badge bg-success">{{ $responsable['accepte_count'] }}</span>
                                                    <span class="badge bg-danger ms-1">{{ $responsable['refuse_count'] }}</span>
                                                </div>
                                            </div>
                                            <div class="progress mt-2" style="height: 4px;">
                                                <div class="progress-bar bg-primary" style="width: {{ ($responsable['total_valeurs']/$stats['max_responsable_valeur'])*100 }}%"></div>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                {{ number_format($responsable['total_valeurs'], 0) }} FCFA
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

        <!-- Analyse par Type -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-area text-success me-2"></i>
                            Analyse par Type
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($stats['types_distribution'] as $type)
                            <div class="col-md-4 mb-3">
                                <div class="type-card text-center p-4">
                                    <div class="type-icon mb-3">
                                        <i class="fas {{ $type['icon'] }} fa-2x" style="color: {{ $type['color'] }}"></i>
                                    </div>
                                    <h3 class="mb-2">{{ $type['count'] }}</h3>
                                    <h6 class="text-muted mb-3">{{ $type['label'] }}</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">{{ number_format($type['total_valeurs'], 0) }} FCFA</small>
                                        <span class="badge" style="background-color: {{ $type['color'] }}20; color: {{ $type['color'] }}">
                                            {{ $type['percentage'] }}%
                                        </span>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar" style="width: {{ $type['percentage'] }}%; background-color: {{ $type['color'] }}"></div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analyse des Décisions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-balance-scale text-danger me-2"></i>
                            Analyse des Décisions Éthiques
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="decision-card text-center p-4 border-start border-4 border-success">
                                    <h2 class="text-success mb-2">{{ $stats['acceptes'] }}</h2>
                                    <h6 class="text-muted mb-3">Acceptés</h6>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">{{ number_format($stats['valeur_acceptes']/1000, 1) }}K FCFA</small>
                                        <small class="text-success">{{ $stats['taux_acceptation'] }}%</small>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $stats['taux_acceptation'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="decision-card text-center p-4 border-start border-4 border-danger">
                                    <h2 class="text-danger mb-2">{{ $stats['refuses'] }}</h2>
                                    <h6 class="text-muted mb-3">Refusés</h6>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">{{ number_format($stats['valeur_refuses']/1000, 1) }}K FCFA</small>
                                        <small class="text-danger">{{ $stats['taux_refus'] }}%</small>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-danger" style="width: {{ $stats['taux_refus'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="decision-card text-center p-4 border-start border-4 border-warning">
                                    <h2 class="text-warning mb-2">{{ $stats['en_attente'] }}</h2>
                                    <h6 class="text-muted mb-3">En Attente</h6>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">{{ number_format($stats['valeur_en_attente']/1000, 1) }}K FCFA</small>
                                        <small class="text-warning">{{ $stats['taux_en_attente'] }}%</small>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: {{ $stats['taux_en_attente'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="decision-card text-center p-4 border-start border-4 border-secondary">
                                    <h2 class="text-secondary mb-2">{{ $stats['non_defini'] }}</h2>
                                    <h6 class="text-muted mb-3">Non Défini</h6>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">{{ number_format($stats['valeur_non_defini']/1000, 1) }}K FCFA</small>
                                        <small class="text-secondary">{{ $stats['taux_non_defini'] }}%</small>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-secondary" style="width: {{ $stats['taux_non_defini'] }}%"></div>
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
                            Tendance sur 3 Ans
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
                            Synthèse Éthique
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-calendar-plus fa-2x text-primary"></i>
                                    </div>
                                    <h4>{{ $stats['declarations_par_mois'] }}</h4>
                                    <p class="text-muted mb-0">/mois en moyenne</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-handshake fa-2x text-success"></i>
                                    </div>
                                    <h4>{{ $stats['taux_conformite'] }}%</h4>
                                    <p class="text-muted mb-0">Taux Conformité</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-file-contract fa-2x text-warning"></i>
                                    </div>
                                    <h4>{{ $stats['avec_document'] }}</h4>
                                    <p class="text-muted mb-0">Avec Documents</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-users fa-2x text-info"></i>
                                    </div>
                                    <h4>{{ count($stats['top_responsables']) }}</h4>
                                    <p class="text-muted mb-0">Responsables Actifs</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-shield-alt fa-2x text-danger"></i>
                                    </div>
                                    <h4>{{ $stats['taux_refus'] }}%</h4>
                                    <p class="text-muted mb-0">Taux Vigilance</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="summary-item text-center p-3">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-trend-up fa-2x text-indigo"></i>
                                    </div>
                                    <h4>{{ $stats['croissance_mensuelle'] }}%</h4>
                                    <p class="text-muted mb-0">Croissance</p>
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
    /* Thème Cadeaux */
    .bg-gradient-gift {
        background: linear-gradient(135deg, #2044a7 0%, #7766e4 100%) !important;
    }

    .bg-warning { background-color: #f59e0b !important; }
    .bg-success { background-color: #10b981 !important; }
    .bg-primary { background-color: #3b82f6 !important; }
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

    /* Avatars Cadeaux */
    .gift-avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
    }

    /* Cartes Éthique */
    .ethics-card {
        padding: 1rem;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .ethics-card:hover {
        background: #f1f5f9;
        border-color: #f59e0b;
    }

    .ethics-avatar {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
    }

    /* Cartes Valeurs */
    .value-card {
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .value-card:hover {
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .value-icon {
        opacity: 0.8;
    }

    /* Cartes Type */
    .type-card {
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .type-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .type-icon {
        opacity: 0.9;
    }

    /* Cartes Décision */
    .decision-card {
        border-radius: 12px;
        border-left: 4px solid;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .decision-card:hover {
        background: #ffffff;
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

    /* Badges */
    .badge {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 500;
    }

    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
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
                    label: 'Nombre de Déclarations',
                    data: stats.monthly_data.counts,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#f59e0b',
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

        // Graphique des actions
        const actionCtx = document.getElementById('actionDistributionChart').getContext('2d');
        const actionChart = new Chart(actionCtx, {
            type: 'doughnut',
            data: {
                labels: stats.action_distribution.labels,
                datasets: [{
                    data: stats.action_distribution.data,
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b', '#6b7280'],
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

        // Légende pour les actions
        const actionLegend = document.getElementById('action-legend');
        const actionColors = ['#10b981', '#ef4444', '#f59e0b', '#6b7280'];

        stats.action_distribution.labels.forEach((label, index) => {
            const div = document.createElement('div');
            div.className = 'legend-item';
            div.innerHTML = `
                <span class="legend-color" style="background-color: ${actionColors[index]}"></span>
                <span>${label}: ${stats.action_distribution.data[index]}</span>
            `;
            actionLegend.appendChild(div);
        });

        // Graphique tendance annuelle
        const trendCtx = document.getElementById('yearlyTrendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: stats.yearly_trend.labels,
                datasets: [{
                    label: 'Total Déclarations',
                    data: stats.yearly_trend.totals,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }, {
                    label: 'Valeur Totale (K FCFA)',
                    data: stats.yearly_trend.valeurs,
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
                case 'valeur':
                    data = stats.monthly_data.valeurs;
                    label = 'Valeur Totale (FCFA)';
                    color = '#10b981';
                    break;
                case 'acceptes':
                    data = stats.monthly_data.acceptes;
                    label = 'Déclarations Acceptées';
                    color = '#3b82f6';
                    break;
                default:
                    data = stats.monthly_data.counts;
                    label = 'Nombre de Déclarations';
                    color = '#f59e0b';
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
        animateCounter('#total-declarations', stats.total_declarations);
        animateCounter('#total-valeur', stats.total_valeurs/1000, 1);
        animateCounter('#taux-acceptation', stats.taux_acceptation, 1);
        animateCounter('#valeur-moyenne', stats.valeur_moyenne, 0);

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
