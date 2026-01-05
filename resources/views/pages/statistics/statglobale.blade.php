@extends('layaout')

@section('title', 'Statistiques Globales - Admin')

@section('content')
<section class="section">
    <!-- En-tête avec filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card gradient-card">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="text-white mb-2">
                                <i class="fas fa-chart-line"></i> Statistiques Globales
                            </h4>
                            <p class="text-white mb-0 opacity-75">Tableau de bord administrateur</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <button class="btn btn-light rounded-pill mr-2" onclick="refreshStats()">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                            <button class="btn btn-success rounded-pill" onclick="exportStats()">
                                <i class="fas fa-download"></i> Exporter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h5><i class="fas fa-filter"></i> Filtres</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Période</label>
                            <select class="form-control" id="filtre-periode">
                                <option value="jour">Aujourd'hui</option>
                                <option value="semaine">Cette semaine</option>
                                <option value="mois" selected>Ce mois</option>
                                <option value="annee">Cette année</option>
                                <option value="personnalise">Personnalisée</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="filtre-dates" style="display: none;">
                            <label>Date de début</label>
                            <input type="date" class="form-control" id="date-debut">
                        </div>
                        <div class="col-md-3" id="filtre-dates-fin" style="display: none;">
                            <label>Date de fin</label>
                            <input type="date" class="form-control" id="date-fin">
                        </div>
                        <div class="col-md-3">
                            <label>Employé</label>
                            <select class="form-control" id="filtre-employe">
                                <option value="">Tous les employés</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary btn-block" onclick="applyFilters()">
                                <i class="fas fa-search"></i> Appliquer
                            </button>
                        </div>
                    </div>
                    <div class="row mt-3" id="filtre-info" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i>
                                <strong>Filtres actifs:</strong> <span id="filtre-details"></span>
                                <button class="btn btn-sm btn-light float-right" onclick="resetFilters()">
                                    <i class="fas fa-times"></i> Réinitialiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cartes statistiques globales -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Employés</h4>
                    </div>
                    <div class="card-body">
                        <h3 id="stat-employes"><i class="fas fa-spinner fa-spin"></i></h3>
                        <small class="text-muted" id="stat-employes-actifs">--</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-success">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Heures Totales</h4>
                    </div>
                    <div class="card-body">
                        <h3 id="stat-heures"><i class="fas fa-spinner fa-spin"></i></h3>
                        <small class="text-muted" id="stat-moyenne">--</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-info">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Dossiers</h4>
                    </div>
                    <div class="card-body">
                        <h3 id="stat-dossiers"><i class="fas fa-spinner fa-spin"></i></h3>
                        <small class="text-muted" id="stat-dossiers-actifs">--</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-warning">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Congés</h4>
                    </div>
                    <div class="card-body">
                        <h3 id="stat-conges"><i class="fas fa-spinner fa-spin"></i></h3>
                        <small class="text-muted" id="stat-conges-cours">--</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classements -->
    <div class="row">
        <!-- Classement par heures -->
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-trophy text-warning"></i> Top Employés - Heures Travaillées</h4>
                    <small class="text-muted">Classement selon la période sélectionnée</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Employé</th>
                                    <th class="text-center">Heures</th>
                                    <th class="text-center">Dossiers</th>
                                </tr>
                            </thead>
                            <tbody id="classement-heures">
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classement par congés -->
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-plane-departure text-danger"></i> Top Employés - Congés</h4>
                    <small class="text-muted">Plus grand nombre de congés demandés</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Employé</th>
                                    <th class="text-center">Congés</th>
                                    <th class="text-center">Jours</th>
                                </tr>
                            </thead>
                            <tbody id="classement-conges">
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques principaux -->
    <div class="row">
        <!-- Évolution des heures -->
        <div class="col-lg-8">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-area text-primary"></i> Évolution des Heures</h4>
                    <small class="text-muted">Tendance sur la période</small>
                </div>
                <div class="card-body">
                    <canvas id="chartEvolution" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Heures par jour de la semaine -->
        <div class="col-lg-4">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-calendar-week text-info"></i> Par Jour</h4>
                    <small class="text-muted">Distribution hebdomadaire</small>
                </div>
                <div class="card-body">
                    <canvas id="chartJoursSemaine" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques secondaires -->
    <div class="row">
        <!-- Répartition par dossier -->
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-bar text-success"></i> Top 10 Dossiers</h4>
                    <small class="text-muted">Par nombre d'heures</small>
                </div>
                <div class="card-body">
                    <canvas id="chartDossiers" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Congés par type -->
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-pie text-warning"></i> Congés par Type</h4>
                    <small class="text-muted">Répartition globale</small>
                </div>
                <div class="card-body">
                    <canvas id="chartConges" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance mensuelle et taux de validation -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-calendar-alt text-primary"></i> Performance Mensuelle</h4>
                    <small class="text-muted">Évolution mois par mois</small>
                </div>
                <div class="card-body">
                    <canvas id="chartMensuel" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-check-circle text-success"></i> Taux de Validation</h4>
                    <small class="text-muted">Statut des saisies</small>
                </div>
                <div class="card-body">
                    <canvas id="chartValidation" height="200"></canvas>
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

.gradient-card {
    background: linear-gradient(135deg, #244584 0%, #164676 100%);
    border: none;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

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

/* Badges de classement */
.badge-rang-1 {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: white;
    font-weight: bold;
}

.badge-rang-2 {
    background: linear-gradient(135deg, #C0C0C0 0%, #808080 100%);
    color: white;
    font-weight: bold;
}

.badge-rang-3 {
    background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%);
    color: white;
    font-weight: bold;
}

/* Table hover */
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

/* Animation */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.fa-spinner {
    animation: pulse 1.5s ease-in-out infinite;
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let charts = {};
let currentFilters = {
    periode: 'mois',
    user_id: null,
    date_debut: null,
    date_fin: null
};

// Configuration Chart.js
Chart.defaults.font.family = "'Nunito', sans-serif";
Chart.defaults.plugins.legend.labels.usePointStyle = true;

$(document).ready(function() {
    loadEmployes();
    loadStats();

    // Gestion du filtre période
    $('#filtre-periode').change(function() {
        const periode = $(this).val();
        if (periode === 'personnalise') {
            $('#filtre-dates, #filtre-dates-fin').show();
        } else {
            $('#filtre-dates, #filtre-dates-fin').hide();
        }
    });
});

function loadEmployes() {
    $.ajax({
        url: '{{ route("admin.stats.employes") }}',
        method: 'GET',
        success: function(data) {
            const select = $('#filtre-employe');
            select.find('option:not(:first)').remove();

            data.forEach(emp => {
                select.append(`<option value="${emp.id}">${emp.nom_complet}</option>`);
            });
        }
    });
}

function applyFilters() {
    currentFilters = {
        periode: $('#filtre-periode').val(),
        user_id: $('#filtre-employe').val() || null,
        date_debut: $('#date-debut').val() || null,
        date_fin: $('#date-fin').val() || null
    };

    loadStats();
    updateFilterInfo();
}

function resetFilters() {
    $('#filtre-periode').val('mois');
    $('#filtre-employe').val('');
    $('#date-debut, #date-fin').val('');
    $('#filtre-dates, #filtre-dates-fin').hide();
    $('#filtre-info').hide();

    currentFilters = {
        periode: 'mois',
        user_id: null,
        date_debut: null,
        date_fin: null
    };

    loadStats();
}

function updateFilterInfo() {
    const periode = currentFilters.periode;
    const employe = $('#filtre-employe option:selected').text();
    const hasFilters = currentFilters.user_id || periode !== 'mois';

    if (hasFilters) {
        let details = [];

        if (periode === 'jour') details.push('Aujourd\'hui');
        else if (periode === 'semaine') details.push('Cette semaine');
        else if (periode === 'mois') details.push('Ce mois');
        else if (periode === 'annee') details.push('Cette année');
        else if (periode === 'personnalise') {
            details.push(`Du ${currentFilters.date_debut} au ${currentFilters.date_fin}`);
        }

        if (currentFilters.user_id) {
            details.push(`Employé: ${employe}`);
        }

        $('#filtre-details').text(details.join(' • '));
        $('#filtre-info').show();
    } else {
        $('#filtre-info').hide();
    }
}

function refreshStats() {
    loadStats();
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Statistiques actualisées',
        showConfirmButton: false,
        timer: 2000
    });
}

function loadStats() {
    $.ajax({
        url: '{{ route("admin.stats.data") }}',
        method: 'GET',
        data: currentFilters,
        success: function(response) {
            console.log('Stats data:', response);
            const stats = response.stats;

            updateGlobalStats(stats.totaux);
            updateClassementHeures(stats.classement_employes);
            updateClassementConges(stats.classement_conges);
            updateChartEvolution(stats.evolution_heures);
            updateChartDossiers(stats.repartition_dossiers);
            updateChartConges(stats.statistiques_conges);
            updateChartMensuel(stats.performance_mensuelle);
            updateChartValidation(stats.taux_validation);
            updateChartJoursSemaine(stats.heures_par_jour_semaine);
        },
        error: function(xhr) {
            console.error('Erreur:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Impossible de charger les statistiques'
            });
        }
    });
}

function updateGlobalStats(totaux) {
    $('#stat-employes').text(totaux.total_employes);
    $('#stat-employes-actifs').text(totaux.employes_actifs + ' actifs');

    $('#stat-heures').text(totaux.total_heures + 'h');
    $('#stat-moyenne').text(totaux.moyenne_heures_employe ?
        'Moyenne: ' + totaux.moyenne_heures_employe + 'h/employé' : '');

    $('#stat-dossiers').text(totaux.total_dossiers);
    $('#stat-dossiers-actifs').text(totaux.dossiers_actifs + ' actifs');

    $('#stat-conges').text(totaux.total_conges);
    $('#stat-conges-cours').text(totaux.conges_en_cours + ' en cours');
}

function updateClassementHeures(data) {
    const tbody = $('#classement-heures');

    if (!data || data.length === 0) {
        tbody.html('<tr><td colspan="4" class="text-center py-3 text-muted">Aucune donnée</td></tr>');
        return;
    }

    let html = '';
    data.forEach((emp, index) => {
        let badgeClass = '';
        if (emp.rang === 1) badgeClass = 'badge-rang-1';
        else if (emp.rang === 2) badgeClass = 'badge-rang-2';
        else if (emp.rang === 3) badgeClass = 'badge-rang-3';

        html += `
            <tr onclick="viewEmployeDetails(${emp.id})" style="cursor: pointer;">
                <td>
                    <span class="badge badge-pill ${badgeClass || 'badge-secondary'}">${emp.rang}</span>
                </td>
                <td>
                    <strong>${emp.nom_complet}</strong><br>
                    <small class="text-muted">${emp.email}</small>
                </td>
                <td class="text-center">
                    <strong class="text-primary">${emp.total_heures}h</strong>
                </td>
                <td class="text-center">
                    <span class="badge badge-info">${emp.nombre_dossiers}</span>
                </td>
            </tr>
        `;
    });

    tbody.html(html);
}

function updateClassementConges(data) {
    const tbody = $('#classement-conges');

    if (!data || data.length === 0) {
        tbody.html('<tr><td colspan="4" class="text-center py-3 text-muted">Aucune donnée</td></tr>');
        return;
    }

    let html = '';
    data.forEach((emp, index) => {
        let badgeClass = '';
        if (emp.rang === 1) badgeClass = 'badge-rang-1';
        else if (emp.rang === 2) badgeClass = 'badge-rang-2';
        else if (emp.rang === 3) badgeClass = 'badge-rang-3';

        html += `
            <tr onclick="viewEmployeDetails(${emp.id})" style="cursor: pointer;">
                <td>
                    <span class="badge badge-pill ${badgeClass || 'badge-secondary'}">${emp.rang}</span>
                </td>
                <td>
                    <strong>${emp.nom_complet}</strong><br>
                    <small class="text-muted">${emp.email}</small>
                </td>
                <td class="text-center">
                    <strong class="text-warning">${emp.nombre_conges}</strong>
                </td>
                <td class="text-center">
                    <span class="badge badge-warning">${emp.total_jours}j</span>
                </td>
            </tr>
        `;
    });

    tbody.html(html);
}

function updateChartEvolution(data) {
    destroyChart('chartEvolution');

    const ctx = document.getElementById('chartEvolution');
    charts.evolution = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Heures',
                data: data.heures,
                borderColor: '#6777ef',
                backgroundColor: 'rgba(103, 119, 239, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => context.parsed.y + ' heures'
                    }
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function updateChartDossiers(data) {
    destroyChart('chartDossiers');

    const ctx = document.getElementById('chartDossiers');
    charts.dossiers = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.dossiers,
            datasets: [{
                label: 'Heures',
                data: data.heures,
                backgroundColor: '#47c363',
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
}

function updateChartConges(data) {
    destroyChart('chartConges');

    const ctx = document.getElementById('chartConges');
    const colors = ['#6777ef', '#ffa426', '#47c363', '#fc544b'];

    charts.conges = new Chart(ctx, {
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
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

function updateChartMensuel(data) {
    destroyChart('chartMensuel');

    const ctx = document.getElementById('chartMensuel');
    charts.mensuel = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Heures',
                data: data.heures,
                backgroundColor: '#3abaf4',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function updateChartValidation(data) {
    destroyChart('chartValidation');

    const ctx = document.getElementById('chartValidation');
    const colors = {
        'soumis': '#3abaf4',
        'valide': '#47c363',
        'refuse': '#fc544b'
    };

    const bgColors = data.statuts.map(s => colors[s] || '#95a5a6');

    charts.validation = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.statuts.map(s => s.toUpperCase()),
            datasets: [{
                data: data.counts,
                backgroundColor: bgColors,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const percentage = data.pourcentages[context.dataIndex];
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

function updateChartJoursSemaine(data) {
    destroyChart('chartJoursSemaine');

    const ctx = document.getElementById('chartJoursSemaine');
    charts.joursSemaine = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: data.jours,
            datasets: [{
                label: 'Heures',
                data: data.heures,
                backgroundColor: 'rgba(58, 186, 244, 0.2)',
                borderColor: '#3abaf4',
                borderWidth: 2,
                pointBackgroundColor: '#3abaf4'
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    beginAtZero: true
                }
            }
        }
    });
}

function destroyChart(chartId) {
    const chartKey = chartId.replace('chart', '').toLowerCase();
    if (charts[chartKey]) {
        charts[chartKey].destroy();
        delete charts[chartKey];
    }
}

function viewEmployeDetails(userId) {
    $.ajax({
        url: `/admin/statistiques/employe/${userId}`,
        method: 'GET',
        success: function(data) {
            Swal.fire({
                title: '<strong>' + data.user.nom_complet + '</strong>',
                html: `
                    <div class="text-left">
                        <p><strong>Email:</strong> ${data.user.email}</p>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h4 class="text-primary">${data.heures_totales}h</h4>
                                        <small>Heures totales</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h4 class="text-success">${data.heures_mois}h</h4>
                                        <small>Ce mois</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h4 class="text-info">${data.heures_annee}h</h4>
                                        <small>Cette année</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h4 class="text-warning">${data.nombre_dossiers}</h4>
                                        <small>Dossiers</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h4 class="text-danger">${data.nombre_conges_annee} congés (${data.jours_conges_annee}j)</h4>
                                        <small>Congés cette année</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                width: 600,
                showCloseButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-filter"></i> Filtrer sur cet employé',
                cancelButtonText: 'Fermer',
                confirmButtonColor: '#6777ef',
                cancelButtonColor: '#95a5a6'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#filtre-employe').val(userId);
                    applyFilters();
                }
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Impossible de charger les détails de l\'employé'
            });
        }
    });
}

function exportStats() {
    Swal.fire({
        title: 'Exporter les statistiques',
        html: `
            <div class="form-group">
                <label>Format d'export</label>
                <select class="form-control" id="export-type">
                    <option value="excel">Excel (.xlsx)</option>
                    <option value="pdf">PDF</option>
                    <option value="csv">CSV</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-download"></i> Télécharger',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#47c363',
        preConfirm: () => {
            return $('#export-type').val();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const exportType = result.value;

            // Préparer les données pour l'export
            const params = new URLSearchParams({
                ...currentFilters,
                type: exportType
            });

            // Simuler le téléchargement (à implémenter côté serveur)
            $.ajax({
                url: '/admin/statistiques/export?' + params.toString(),
                method: 'GET',
                success: function(response) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: response.message || 'Export en cours de développement',
                        showConfirmButton: false,
                        timer: 3000
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible d\'exporter les statistiques'
                    });
                }
            });
        }
    });
}
</script>
@endpush
