@extends('layaout')

@section('title','Tableau de bord')

@section('content')
<section class="section">
    <!-- Cartes de statistiques -->
      <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card">
                <div class="card-statistic-4">
                    <div class="align-items-center justify-content-between">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                <div class="card-content">
                                    <h5 class="font-15">Plaintes</h5>
                                    <h2 class="mb-3 font-18" id="total-plaintes">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </h2>
                                    <p class="mb-0">
                                        <span id="plaintes-percent">--</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                <div class="banner-img">
                                    <i class="fas fa-exclamation-triangle" style="font-size: 80px; color: rgba(255, 193, 7, 0.8); opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card">
                <div class="card-statistic-4">
                    <div class="align-items-center justify-content-between">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                <div class="card-content">
                                    <h5 class="font-15">Clients Audits</h5>
                                    <h2 class="mb-3 font-18" id="total-rca">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </h2>
                                    <p class="mb-0">
                                        <span id="rca-percent">--</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                <div class="banner-img">
                                    <i class="fas fa-clipboard-check" style="font-size: 80px; color: rgba(23, 162, 184, 0.8); opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card">
                <div class="card-statistic-4">
                    <div class="align-items-center justify-content-between">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                <div class="card-content">
                                    <h5 class="font-15">Cadeaux (RCI)</h5>
                                    <h2 class="mb-3 font-18" id="total-cadeaux">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </h2>
                                    <p class="mb-0">
                                        <span id="cadeaux-percent">--</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                <div class="banner-img">
                                    <i class="fas fa-gift" style="font-size: 80px; color: rgba(40, 167, 69, 0.8); opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card">
                <div class="card-statistic-4">
                    <div class="align-items-center justify-content-between">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                <div class="card-content">
                                    <h5 class="font-15">Conflits d'Intérêt</h5>
                                    <h2 class="mb-3 font-18" id="total-conflits">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </h2>
                                    <p class="mb-0">
                                        <span id="conflits-percent">--</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                <div class="banner-img">
                                    <i class="fas fa-balance-scale" style="font-size: 80px; color: rgba(220, 53, 69, 0.8); opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
          <div class="card">
              <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                      <div class="row">
                          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                              <div class="card-content">
                                  <h5 class="font-15">Indépendance</h5>
                                  <h2 class="mb-3 font-18" id="total-indep">
                                      <i class="fas fa-spinner fa-spin"></i>
                                  </h2>
                                  <p class="mb-0">
                                      <span id="indep-percent">--</span>
                                  </p>
                              </div>
                          </div>
                          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                              <div class="banner-img">
                                  <i class="fas fa-user-check" style="font-size: 80px; color: rgba(148, 9, 127, 0.8); opacity: 0.5;"></i>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
        </div>

    </div>

    <!-- Graphique principal -->
    <div class="row">
        <div class="col-12 col-sm-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-line"></i> Évolution des Registres (30 derniers jours)</h4>
                    <div class="card-header-action">
                        <button class="btn btn-primary" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <canvas id="chart30days" height="100"></canvas>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-2 col-md-6 col-sm-6 col-xs-6 text-center">
                            <div class="list-inline-item">
                                <i class="fas fa-circle" style="color: rgba(255, 193, 7, 0.8);"></i>
                                <h5 class="m-b-0" id="monthly-plaintes">0</h5>
                                <p class="text-muted font-14 m-b-0">Plaintes (30 jours)</p>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-6 col-xs-6 text-center">
                            <div class="list-inline-item">
                                <i class="fas fa-circle" style="color: rgba(23, 162, 184, 0.8);"></i>
                                <h5 class="m-b-0" id="monthly-rca">0</h5>
                                <p class="text-muted font-14 m-b-0">RCA (30 jours)</p>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-6 col-xs-6 text-center">
                            <div class="list-inline-item">
                                <i class="fas fa-circle" style="color: rgba(40, 167, 69, 0.8);"></i>
                                <h5 class="m-b-0" id="monthly-cadeaux">0</h5>
                                <p class="text-muted font-14 m-b-0">Cadeaux (30 jours)</p>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-6 col-xs-6 text-center">
                            <div class="list-inline-item">
                                <i class="fas fa-circle" style="color: rgba(220, 53, 69, 0.8);"></i>
                                <h5 class="m-b-0" id="monthly-conflits">0</h5>
                                <p class="text-muted font-14 m-b-0">Conflits (30 jours)</p>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-6 col-xs-6 text-center">
                          <div class="list-inline-item">
                              <i class="fas fa-circle" style="color: rgba(148, 9, 127, 0.8);"></i>
                              <h5 class="m-b-0" id="monthly-indep">0</h5>
                              <p class="text-muted font-14 m-b-0">Indépendance (30 jours)</p>
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
        <div class="col-12 col-sm-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-users"></i> Top 5 Utilisateurs Actifs</h4>
                </div>
                <div class="card-body">
                    <canvas id="chartTopUsers" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Répartition par type -->
        <div class="col-12 col-sm-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-pie"></i> Répartition des Registres</h4>
                </div>
                <div class="card-body">
                    <canvas id="chartRepartition" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques détaillées -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-info-circle"></i> Statistiques Détaillées</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Type de Registre</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Cette semaine</th>
                                    <th class="text-center">Ce mois</th>
                                    <th class="text-center">Évolution</th>
                                </tr>
                            </thead>
                            <tbody id="stats-table-body">
                                <tr>
                                    <td colspan="5" class="text-center">
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
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let chart30days, chartTopUsers, chartRepartition;

// Charger les données au chargement de la page
$(document).ready(function() {
    loadDashboardData();
    
    // Actualiser toutes les 2 minutes
    setInterval(loadDashboardData, 120000);
});

function refreshDashboard() {
    loadDashboardData();
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Données actualisées',
        showConfirmButton: false,
        timer: 2000
    });
}

function loadDashboardData() {
    $.ajax({
        url: '{{ route("dashboard.data") }}',
        method: 'GET',
        success: function(data) {
            updateStats(data.totals, data.percentages);
            updateChart30Days(data.last30days);
            updateTopUsers(data.topUsers);
            updateRepartition(data.totals);
            updateStatsTable(data);
        },
        error: function(xhr) {
            console.error('Erreur de chargement:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Impossible de charger les données'
            });
        }
    });
}

function updateStats(totals, percentages) {
    // Mettre à jour les totaux
    $('#total-plaintes').html(totals.plaintes);
    $('#total-rca').html(totals.rca);
    $('#total-cadeaux').html(totals.cadeaux);
    $('#total-conflits').html(totals.conflits);
    $('#total-indep').html(totals.independances);

    // Mettre à jour les pourcentages avec les couleurs appropriées
    updatePercentage('#plaintes-percent', percentages.plaintes);
    updatePercentage('#rca-percent', percentages.rca);
    updatePercentage('#cadeaux-percent', percentages.cadeaux);
    updatePercentage('#conflits-percent', percentages.conflits);
    updatePercentage('#indep-percent', percentages.independances);
}

function updatePercentage(selector, value) {
    const $element = $(selector);
    const isPositive = value.startsWith('+');
    const isZero = value === '0';
    const isNew = value === 'Nouveau';
    
    // Appliquer la couleur appropriée
    $element.removeClass('col-green col-orange col-red');
    
    if (isNew) {
        $element.addClass('col-green');
        $element.html('<i class="fas fa-star"></i> Nouveau');
    } else if (isZero) {
        $element.addClass('col-orange');
        $element.html('0%');
    } else if (isPositive) {
        $element.addClass('col-green');
        $element.html(value + '%');
    } else {
        $element.addClass('col-red');
        $element.html(value + '%');
    }
}

function updateChart30Days(data) {
    const ctx = document.getElementById('chart30days').getContext('2d');
    
    if (chart30days) {
        chart30days.destroy();
    }
    
    chart30days = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates,
            datasets: [
                {
                    label: 'Plaintes',
                    data: data.plaintes,
                    borderColor: 'rgba(255, 193, 7, 0.8)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Clients Audits',
                    data: data.rca,
                    borderColor: 'rgba(23, 162, 184, 0.8)',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Cadeaux',
                    data: data.cadeaux,
                    borderColor: 'rgba(40, 167, 69, 0.8)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Conflits',
                    data: data.conflits,
                    borderColor: 'rgba(220, 53, 69, 0.8)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Indépendance',
                    data: data.independances,
                    borderColor: 'rgba(148, 9, 127, 0.8)',
                    backgroundColor: 'rgba(148, 9, 127, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Calculer les totaux des 30 derniers jours
    $('#monthly-plaintes').text(data.plaintes.reduce((a, b) => a + b, 0));
    $('#monthly-rca').text(data.rca.reduce((a, b) => a + b, 0));
    $('#monthly-cadeaux').text(data.cadeaux.reduce((a, b) => a + b, 0));
    $('#monthly-conflits').text(data.conflits.reduce((a, b) => a + b, 0));
    $('#monthly-indep').text(data.independances.reduce((a, b) => a + b, 0));
}

function updateTopUsers(data) {
    const ctx = document.getElementById('chartTopUsers').getContext('2d');
    
    if (chartTopUsers) {
        chartTopUsers.destroy();
    }
    
    chartTopUsers = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.names,
            datasets: [{
                label: 'Nombre d\'enregistrements',
                data: data.counts,
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(148, 9, 127, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

function updateRepartition(totals) {
    const ctx = document.getElementById('chartRepartition').getContext('2d');
    
    if (chartRepartition) {
        chartRepartition.destroy();
    }
    
    chartRepartition = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Plaintes', 'Clients Audits', 'Cadeaux', 'Conflits d\'Intérêt', 'Indépendance'],
            datasets: [{
                data: [totals.plaintes, totals.rca, totals.cadeaux, totals.conflits, totals.independances],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(148, 9, 127, 0.8)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}

function updateStatsTable(data) {
    const totals = data.totals;
    const weekly = data.weekly;
    const monthly = data.monthly;
    
    const rows = [
        { name: 'Plaintes', icon: 'fa-exclamation-triangle', color: 'rgba(255, 193, 7, 0.8)', total: totals.plaintes, week: weekly.plaintes, month: monthly.plaintes },
        { name: 'Clients Audits (RCA)', icon: 'fa-clipboard-check', color: 'rgba(23, 162, 184, 0.8)', total: totals.rca, week: weekly.rca, month: monthly.rca },
        { name: 'Cadeaux (RCI)', icon: 'fa-gift', color: 'rgba(40, 167, 69, 0.8)', total: totals.cadeaux, week: weekly.cadeaux, month: monthly.cadeaux },
        { name: 'Conflits d\'Intérêt', icon: 'fa-balance-scale', color: 'rgba(220, 53, 69, 0.8)', total: totals.conflits, week: weekly.conflits, month: monthly.conflits },
        { name: 'Indépendance', icon: 'fa-user-check', color: 'rgba(148, 9, 127, 0.8)', total: totals.independances, week: weekly.independances, month: monthly.independances }
    ];
    
    let html = '';
    rows.forEach(row => {
        // Calculer l'évolution en pourcentage (semaine par rapport au total du mois)
        const evolution = row.month > 0 ? ((row.week / row.month) * 100).toFixed(0) : '0';
        const evolutionInt = parseInt(evolution);
        
        html += `
            <tr>
                <td><i class="fas ${row.icon}" style="color: ${row.color};"></i> ${row.name}</td>
                <td class="text-center"><strong>${row.total}</strong></td>
                <td class="text-center">${row.week}</td>
                <td class="text-center">${row.month}</td>
                <td class="text-center">
                    <span class="badge badge-${evolutionInt >= 50 ? 'success' : evolutionInt > 0 ? 'warning' : 'secondary'}">
                        ${evolution}% du mois
                    </span>
                </td>
            </tr>
        `;
    });
    
    $('#stats-table-body').html(html);
}
</script>
@endpush