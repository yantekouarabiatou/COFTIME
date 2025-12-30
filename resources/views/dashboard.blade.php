@extends('layaout')

@section('title','Tableau de bord')

@section('content')
<section class="section">
    <!-- Cartes de statistiques avec animations -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-primary">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Clients</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0" id="total-clients">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h3>
                            <span class="badge badge-pill" id="clients-percent">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-info">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Dossiers Actifs</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0" id="total-dossiers">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h3>
                            <span class="badge badge-pill" id="dossiers-percent">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-success">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Heures (Ce mois)</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0" id="total-heures">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h3>
                            <span class="badge badge-pill" id="heures-percent">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-warning">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Congés en cours</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0" id="total-conges">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h3>
                            <span class="badge badge-pill" id="conges-percent">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card gradient-card">
                <div class="card-body p-4">
                    <div class="row text-white">
                        <div class="col-md-3 text-center border-right-white">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h6 class="mb-1">Utilisateurs Actifs</h6>
                            <h4 class="font-weight-bold" id="utilisateurs-actifs">-</h4>
                        </div>
                        <div class="col-md-3 text-center border-right-white">
                            <i class="fas fa-calendar-week fa-2x mb-2"></i>
                            <h6 class="mb-1">Heures (7 jours)</h6>
                            <h4 class="font-weight-bold" id="heures-semaine">-</h4>
                        </div>
                        <div class="col-md-3 text-center border-right-white">
                            <i class="fas fa-folder-plus fa-2x mb-2"></i>
                            <h6 class="mb-1">Nouveaux Dossiers</h6>
                            <h4 class="font-weight-bold" id="nouveaux-dossiers">-</h4>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fas fa-plane-departure fa-2x mb-2"></i>
                            <h6 class="mb-1">Congés (Ce mois)</h6>
                            <h4 class="font-weight-bold" id="conges-mois">-</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique principal -->
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Évolution des Heures (30 derniers jours)</h4>
                        <small class="text-muted">Suivi quotidien du temps de travail</small>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-pill" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Actualiser
                    </button>
                </div>
                <div class="card-body">
                    <canvas id="chart30days" height="80"></canvas>
                    
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-4 text-center">
                            <div class="stats-box">
                                <i class="fas fa-clock text-primary fa-2x mb-2"></i>
                                <h4 class="mb-0" id="total-heures-30j">0</h4>
                                <p class="text-muted mb-0">Total Heures</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="stats-box">
                                <i class="fas fa-folder text-info fa-2x mb-2"></i>
                                <h4 class="mb-0" id="total-dossiers-30j">0</h4>
                                <p class="text-muted mb-0">Dossiers Actifs</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="stats-box">
                                <i class="fas fa-user-friends text-success fa-2x mb-2"></i>
                                <h4 class="mb-0" id="total-clients-30j">0</h4>
                                <p class="text-muted mb-0">Nouveaux Clients</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques secondaires -->
    <div class="row">
        <!-- Top 5 Utilisateurs -->
        <div class="col-lg-6 col-md-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-trophy text-warning"></i> Top 5 - Heures Travaillées</h4>
                    <small class="text-muted">Classement du mois en cours</small>
                </div>
                <div class="card-body">
                    <canvas id="chartTopUsers" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Dossiers -->
        <div class="col-lg-6 col-md-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-fire text-danger"></i> Dossiers les Plus Actifs</h4>
                    <small class="text-muted">Par nombre d'heures ce mois</small>
                </div>
                <div class="card-body">
                    <canvas id="chartTopDossiers" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartitions -->
    <div class="row">
        <!-- Congés par type -->
        <div class="col-lg-6 col-md-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-pie text-info"></i> Répartition des Congés</h4>
                    <small class="text-muted">Par type (mois en cours)</small>
                </div>
                <div class="card-body">
                    <canvas id="chartConges" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Dossiers par statut -->
        <div class="col-lg-6 col-md-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-tasks text-success"></i> Statut des Dossiers</h4>
                    <small class="text-muted">Vue d'ensemble globale</small>
                </div>
                <div class="card-body">
                    <canvas id="chartStatuts" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau récapitulatif -->
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-table text-primary"></i> Statistiques Détaillées</h4>
                    <small class="text-muted">Comparaison des périodes</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th><i class="fas fa-list"></i> Indicateur</th>
                                    <th class="text-center"><i class="fas fa-calendar-week"></i> Cette semaine</th>
                                    <th class="text-center"><i class="fas fa-calendar-alt"></i> Ce mois</th>
                                    <th class="text-center"><i class="fas fa-chart-line"></i> Évolution</th>
                                </tr>
                            </thead>
                            <tbody id="stats-table-body">
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                        <p class="mt-2 mb-0">Chargement des données...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Cartes stylisées */
.hover-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.card-statistic-1 {
    position: relative;
    overflow: hidden;
}

.card-statistic-1 .card-icon {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: white;
    margin-right: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.card-statistic-1 .card-wrap {
    flex: 1;
}

.card-statistic-1 .card-body h3 {
    font-size: 2rem;
    font-weight: bold;
    color: #34395e;
}

.card-statistic-1 .badge-pill {
    padding: 5px 12px;
    font-size: 0.85rem;
}

/* Carte gradient */
.gradient-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.border-right-white {
    border-right: 1px solid rgba(255,255,255,0.3);
}

/* Cartes modernes */
.modern-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
}

.modern-card .card-header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e3e6f0;
    padding: 1.25rem;
}

.modern-card .card-header h4 {
    margin-bottom: 0;
    font-weight: 600;
    color: #2c3e50;
}

/* Stats box */
.stats-box {
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.stats-box:hover {
    background-color: #f8f9fa;
    transform: scale(1.05);
}

/* Badges personnalisés */
.badge-success-gradient {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.badge-danger-gradient {
    background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
    color: white;
}

.badge-warning-gradient {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

/* Animation loading */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.fa-spinner {
    animation: pulse 1.5s ease-in-out infinite;
}

/* Table moderne */
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
}

.thead-light th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

/* Responsive */
@media (max-width: 768px) {
    .border-right-white {
        border-right: none;
        border-bottom: 1px solid rgba(255,255,255,0.3);
        margin-bottom: 15px;
        padding-bottom: 15px;
    }
    
    .card-statistic-1 .card-icon {
        width: 60px;
        height: 60px;
        font-size: 2rem;
    }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let chart30days, chartTopUsers, chartTopDossiers, chartConges, chartStatuts;

// Configuration Chart.js globale
Chart.defaults.font.family = "'Nunito', sans-serif";
Chart.defaults.plugins.legend.labels.usePointStyle = true;

// Charger les données au chargement de la page
$(document).ready(function() {
    loadDashboardData();
    
    // Actualiser toutes les 3 minutes
    setInterval(loadDashboardData, 180000);
});

function refreshDashboard() {
    loadDashboardData();
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Données actualisées avec succès',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
}

function loadDashboardData() {
    $.ajax({
        url: '{{ route("dashboard.data") }}',
        method: 'GET',
        success: function(data) {
            updateStats(data.totals, data.percentages);
            updateQuickStats(data.totals, data.weekly, data.monthly);
            updateChart30Days(data.last30days);
            updateTopUsers(data.topUsers);
            updateTopDossiers(data.topDossiers);
            updateCongesChart(data.congesParType);
            updateStatutsChart(data.dossiersParStatut);
            updateStatsTable(data);
        },
        error: function(xhr) {
            console.error('Erreur de chargement:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Erreur de chargement',
                text: 'Impossible de charger les données du dashboard',
                confirmButtonColor: '#6777ef'
            });
        }
    });
}

function updateStats(totals, percentages) {
    // Mettre à jour les totaux avec animation
    animateValue('total-clients', 0, totals.clients, 1000);
    animateValue('total-dossiers', 0, totals.dossiers_actifs, 1000);
    animateValue('total-heures', 0, Math.round(totals.heures_mois), 1000, 'h');
    animateValue('total-conges', 0, totals.conges_en_cours, 1000);

    // Mettre à jour les pourcentages
    updatePercentage('#clients-percent', percentages.clients);
    updatePercentage('#dossiers-percent', percentages.dossiers);
    updatePercentage('#heures-percent', percentages.heures);
    updatePercentage('#conges-percent', percentages.conges);
}

function updateQuickStats(totals, weekly, monthly) {
    $('#utilisateurs-actifs').text(totals.utilisateurs_actifs);
    $('#heures-semaine').html(Math.round(weekly.heures) + '<small>h</small>');
    $('#nouveaux-dossiers').text(weekly.dossiers);
    $('#conges-mois').text(monthly.conges);
}

function animateValue(id, start, end, duration, suffix = '') {
    const element = document.getElementById(id);
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(function() {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.round(current) + suffix;
    }, 16);
}

function updatePercentage(selector, value) {
    const $element = $(selector);
    const numValue = parseInt(value);
    
    $element.removeClass('badge-success-gradient badge-danger-gradient badge-warning-gradient badge-secondary');
    
    if (value === '0') {
        $element.addClass('badge-secondary');
        $element.html('<i class="fas fa-minus"></i> 0%');
    } else if (numValue > 0) {
        $element.addClass('badge-success-gradient');
        $element.html('<i class="fas fa-arrow-up"></i> ' + value + '%');
    } else {
        $element.addClass('badge-danger-gradient');
        $element.html('<i class="fas fa-arrow-down"></i> ' + value + '%');
    }
}

function updateChart30Days(data) {
    const ctx = document.getElementById('chart30days').getContext('2d');
    
    if (chart30days) chart30days.destroy();
    
    chart30days = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates,
            datasets: [
                {
                    label: 'Heures travaillées',
                    data: data.heures,
                    borderColor: '#6777ef',
                    backgroundColor: 'rgba(103, 119, 239, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Dossiers',
                    data: data.dossiers,
                    borderColor: '#3abaf4',
                    backgroundColor: 'rgba(58, 186, 244, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5
                },
                {
                    label: 'Clients',
                    data: data.clients,
                    borderColor: '#ffa426',
                    backgroundColor: 'rgba(255, 164, 38, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 15,
                        font: { size: 12, weight: '600' }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
    
    // Totaux
    $('#total-heures-30j').text(Math.round(data.heures.reduce((a, b) => a + b, 0)) + 'h');
    $('#total-dossiers-30j').text(data.dossiers.reduce((a, b) => a + b, 0));
    $('#total-clients-30j').text(data.clients.reduce((a, b) => a + b, 0));
}

function updateTopUsers(data) {
    const ctx = document.getElementById('chartTopUsers').getContext('2d');
    
    if (chartTopUsers) chartTopUsers.destroy();
    
    chartTopUsers = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.names,
            datasets: [{
                label: 'Heures travaillées',
                data: data.heures,
                backgroundColor: [
                    '#6777ef', '#3abaf4', '#ffa426', '#fc544b', '#47c363'
                ],
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => context.parsed.x + ' heures'
                    }
                }
            },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                y: { grid: { display: false } }
            }
        }
    });
}

function updateTopDossiers(data) {
    const ctx = document.getElementById('chartTopDossiers').getContext('2d');
    
    if (chartTopDossiers) chartTopDossiers.destroy();
    
    chartTopDossiers = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.names,
            datasets: [{
                label: 'Heures',
                data: data.heures,
                backgroundColor: [
                    '#fc544b', '#ffa426', '#47c363', '#3abaf4', '#6777ef'
                ],
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => context.parsed.x + ' heures'
                    }
                }
            },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                y: { grid: { display: false } }
            }
        }
    });
}

function updateCongesChart(data) {
    const ctx = document.getElementById('chartConges').getContext('2d');
    
    if (chartConges) chartConges.destroy();
    
    const colors = ['#6777ef', '#ffa426', '#47c363', '#fc544b'];
    
    chartConges = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.types,
            datasets: [{
                data: data.counts,
                backgroundColor: colors,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, font: { size: 12 } }
                }
            }
        }
    });
}

function updateStatutsChart(data) {
    const ctx = document.getElementById('chartStatuts').getContext('2d');
    
    if (chartStatuts) chartStatuts.destroy();
    
    const colors = {
        'ouvert': '#3abaf4',
        'en_cours': '#6777ef',
        'suspendu': '#ffa426',
        'cloture': '#47c363',
        'archive': '#95a5a6'
    };
    
    const bgColors = data.statuts.map(s => colors[s] || '#95a5a6');
    
    chartStatuts = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.statuts.map(s => s.replace('_', ' ').toUpperCase()),
            datasets: [{
                data: data.counts,
                backgroundColor: bgColors,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, font: { size: 12 } }
                }
            }
        }
    });
}

function updateStatsTable(data) {
    const weekly = data.weekly;
    const monthly = data.monthly;
    const percentages = data.percentages;
    
    const rows = [
        { name: 'Heures travaillées', icon: 'fa-clock', color: '#6777ef', week: Math.round(weekly.heures), month: Math.round(monthly.heures), percent: percentages.heures },
        { name: 'Dossiers', icon: 'fa-folder', color: '#3abaf4', week: weekly.dossiers, month: monthly.dossiers, percent: percentages.dossiers },
        { name: 'Clients', icon: 'fa-briefcase', color: '#47c363', week: weekly.clients, month: monthly.clients, percent: percentages.clients },
        { name: 'Congés', icon: 'fa-umbrella-beach', color: '#ffa426', week: weekly.conges, month: monthly.conges, percent: percentages.conges }
    ];
    
    let html = '';
    rows.forEach(row => {
        const isPositive = parseInt(row.percent) >= 0;
        const badgeClass = isPositive ? 'badge-success-gradient' : 'badge-danger-gradient';
        const icon = isPositive ? 'fa-arrow-up' : 'fa-arrow-down';
        
                html += `
            <tr>
                <td>
                    <i class="fas ${row.icon}" style="color: ${row.color};"></i>
                    ${row.name}
                </td>
                <td class="text-center">
                    <strong>${row.week + row.month}</strong>
                </td>
                <td class="text-center">${row.week}</td>
                <td class="text-center">${row.month}</td>
                <td class="text-center">
                    <span class="badge badge-${badgeClass}">
                        <i class="fas ${arrowIcon}"></i>
                        ${Math.abs(evolutionInt)}% du mois
                    </span>
                </td>
            </tr>
        `;
    });
    
    $('#stats-table-body').html(html);
}
</script>
@endpush