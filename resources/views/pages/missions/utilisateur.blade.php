@extends('layaout')

@section('title', 'Détails du Personnel - ' . $user->prenom . ' ' . $user->nom)

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Détails du Personnel</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('missions.analyse') }}">Missions</a></div>
            <div class="breadcrumb-item active">{{ $user->prenom }} {{ $user->nom }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <!-- Colonne gauche : Profil + Charge -->
            <div class="col-lg-4 col-md-5">
                <!-- Carte Profil -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Profil</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <!-- Avatar avec initiales si pas de photo -->
                            <div class="avatar avatar-xl mx-auto" style="width: 120px; height: 120px; font-size: 2.5rem;">
                                @if($user->photo && file_exists(public_path('storage/' . $user->photo)))
                                    <img src="{{ asset('storage/' . $user->photo) }}" alt="Photo de {{ $user->prenom }}" class="rounded-circle img-fluid">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center h-100">
                                        {{ Str::upper(substr($user->prenom, 0, 1) . substr($user->nom, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <h4 class="mb-1">{{ $user->prenom }} {{ $user->nom }}</h4>
                        <p class="text-muted mb-4">{{ $user->poste->intitule ?? 'Poste non défini' }}</p>

                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between py-3">
                                <div><i class="fas fa-envelope mr-2 text-muted"></i> Email</div>
                                <div class="font-weight-medium">{{ $user->email ?? 'Non renseigné' }}</div>
                            </div>
                            <div class="list-group-item d-flex justify-content-between py-3">
                                <div><i class="fas fa-phone mr-2 text-muted"></i> Téléphone</div>
                                <div class="font-weight-medium">{{ $user->telephone ?? 'Non renseigné' }}</div>
                            </div>
                            <div class="list-group-item d-flex justify-content-between py-3">
                                <div><i class="fas fa-calendar-alt mr-2 text-muted"></i> Membre depuis</div>
                                <div class="font-weight-medium">{{ $user->created_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charge de travail -->
                <div class="card">
                    <div class="card-header">
                        <h4>Charge de travail (30 derniers jours)</h4>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-6">
                                <h6 class="text-muted">Heures réelles</h6>

                                            @php
                                                $heures = floor($charge['heures_reelles']);
                                                $minutes = round(($charge['heures_reelles']- $heures) * 60);
                                            @endphp
                                <h4 class="text-dark mb-0">{{ $heures }}h {{ $minutes}} min</h4>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted">Heures théoriques</h6>
                                   @php
                                                $heures = floor($charge['heures_theoriques']);
                                                $minutes = round(($charge['heures_theoriques']- $heures) * 60);
                                    @endphp
                                <h4 class="text-info mb-0">{{ $heures }}h {{ $minutes}} min</h4>
                            </div>
                        </div>

                         {{ $heures }}h {{ $minutes }}min
                        @php
                            $pourcentage = floor($charge['heures_theoriques'] ) > 0
                                ? min(100, ($charge['heures_reelles'] / $charge['heures_theoriques']) * 100)
                                : 0;
                            $color = $pourcentage > 100 ? 'danger' : ($pourcentage >= 90 ? 'warning' : 'success');
                        @endphp

                        <div class="progress mb-3" style="height: 12px;">
                            <div class="progress-bar bg-{{ $color }}"
                                 style="width: {{ $pourcentage }}%"
                                 role="progressbar">
                            </div>
                        </div>

                        <div class="text-center">
                            <h5 class="mb-1">{{ round($pourcentage, 1) }}%</h5>
                            <small class="text-{{ $charge['ecart'] >= 0 ? 'danger' : 'success' }}">
                                Écart : {{ $charge['ecart'] >= 0 ? '+' : '' }}{{ $charge['ecart'] }}h
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Missions + Graphique -->
            <div class="col-lg-8 col-md-7">
                <!-- Missions en cours -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Missions en cours</h4>
                        <span class="badge badge-lg badge-info">{{ count($missions) }} mission{{ count($missions) > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if(count($missions) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Mission</th>
                                            <th>Client</th>
                                            <th>Temps passé</th>
                                            <th>Dernière activité</th>
                                            <th>Statut</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($missions as $mission)
                                            @php
                                                $dossier = $mission['dossier'];
                                                $derniereActivite = \Carbon\Carbon::parse($mission['derniere_activite']);
                                                $joursDepuis = $derniereActivite->diffInDays(now());
                                                $statutCouleur = $joursDepuis > 30 ? 'danger' : ($joursDepuis > 7 ? 'warning' : 'success');
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $dossier->nom }}</strong><br>
                                                    <small class="text-muted">Ref: {{ $dossier->reference }}</small>
                                                </td>
                                                <td>{{ $dossier->client->nom ?? 'N/A' }}</td>
                                                 @php
                                                $heures = floor(num: $mission['total_heures']);
                                                $minutes = round(($mission['total_heures'] - $heures) * 60);
                                               @endphp


                                                <td><span class="badge badge-primary badge-pill">{{ $heures }}h {{ $minutes }}min</span></td>
                                                <td>
                                                    {{ $derniereActivite->format('d/m/Y') }}<br>
                                                    <small class="text-{{ $statutCouleur }}">{{ $joursDepuis }} jour{{ $joursDepuis > 1 ? 's' : '' }}</small>
                                                </td>
                                                <td>{!! $dossier->statut_badge !!}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('dossiers.show', $dossier->id) }}" class="btn btn-sm btn-outline-info" title="Voir dossier">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('missions.utilisateur.dossier', ['user' => $user->id, 'dossier' => $dossier->id]) }}"
                                                       class="btn btn-sm btn-outline-primary" title="Statistiques">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune mission active pour cet utilisateur.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Graphique de répartition -->
                @if(count($missions) > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Répartition du temps par mission</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="missionChart" height="280"></canvas>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($timeEntries->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <h4>Activité: {{ $dossier->nom }} ({{ $dossier->reference }})</h4>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-lg badge-info mr-3">{{ $timeEntries->count() }} entrée{{ $timeEntries->count() > 1 ? 's' : '' }}</span>
                            {{-- Barre de recherche Data Grid --}}
                            <div class="input-group" style="width: 250px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="gridSearch" class="form-control" placeholder="Rechercher...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        {{-- Conteneur du Data Grid --}}
                        <div id="activityDataGrid" class="datagrid-container">
                            {{-- Header du Grid (fixe) --}}
                            <div class="datagrid-header">
                                <div class="datagrid-row">
                                    <div class="datagrid-cell col-date" data-sort="date">
                                        Date <i class="fas fa-sort text-muted"></i>
                                    </div>
                                    <div class="datagrid-cell col-plage" data-sort="plage">
                                        Plage <i class="fas fa-sort text-muted"></i>
                                    </div>
                                    <div class="datagrid-cell col-travaux" data-sort="travaux">
                                        Travaux effectués <i class="fas fa-sort text-muted"></i>
                                    </div>
                                    <div class="datagrid-cell col-rendu" data-sort="rendu">
                                        Rendu <i class="fas fa-sort text-muted"></i>
                                    </div>
                                    <div class="datagrid-cell col-duree text-right" data-sort="duree">
                                        Durée <i class="fas fa-sort text-muted"></i>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Body du Grid (scrollable) --}}
                            <div class="datagrid-body">
                                @foreach($timeEntries as $entry)
                                    @php
                                        $heures = floor($entry->heures_reelles);
                                        $minutes = round(($entry->heures_reelles - $heures) * 60);
                                    @endphp
                                    <div class="datagrid-row" 
                                        data-date="{{ $entry->dailyEntry->jour->format('Y-m-d') }}"
                                        data-plage="{{ $entry->plage }}"
                                        data-travaux="{{ $entry->travaux }}"
                                        data-rendu="{{ $entry->rendu }}"
                                        data-duree="{{ $entry->heures_reelles }}">
                                        <div class="datagrid-cell col-date" data-value="{{ $entry->dailyEntry->jour->format('Y-m-d') }}">
                                            {{ $entry->dailyEntry->jour->format('d/m/Y') }}
                                        </div>
                                        <div class="datagrid-cell col-plage" data-value="{{ $entry->plage }}">
                                            {{ $entry->plage }}
                                        </div>
                                        <div class="datagrid-cell col-travaux" data-value="{{ $entry->travaux }}">
                                            {{ $entry->travaux }}
                                        </div>
                                        <div class="datagrid-cell col-rendu" data-value="{{ $entry->rendu }}">
                                            {{ $entry->rendu }}
                                        </div>
                                        <div class="datagrid-cell col-duree text-right" data-value="{{ $entry->heures_reelles }}">
                                            <span class="badge badge-primary badge-pill">{{ $heures }}h {{ $minutes }}min</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        {{-- Footer avec Pagination et infos --}}
                        <div class="datagrid-footer">
                            <div class="d-flex justify-content-between align-items-center p-3">
                                <div class="text-muted">
                                    Affichage de <span id="gridShowing">1-10</span> sur <span id="gridTotal">{{ $timeEntries->count() }}</span>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-secondary" id="gridPrev" disabled>
                                        <i class="fas fa-chevron-left"></i> Précédent
                                    </button>
                                    <span class="mx-2" id="gridPageIndicator">Page 1</span>
                                    <button class="btn btn-sm btn-outline-secondary" id="gridNext">
                                        Suivant <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Graphique Mission (inchangé) ---
    @if(count($missions) > 0)
    const ctx = document.getElementById('missionChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [@foreach($missions as $mission) "{{ Str::limit($mission['dossier']->nom, 25) }}", @endforeach],
            datasets: [{
                data: [@foreach($missions as $mission) {{ $mission['total_heures'] }}, @endforeach],
                backgroundColor: ['#4361ee', '#3f37c9', '#4895ef', '#4cc9f0', '#7209b7', '#b5179e', '#f72585', '#f94144', '#f3722c', '#f8961e', '#f9c74f', '#90be6d'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });
    @endif

    // --- Data Grid Logic ---
    const gridBody = document.querySelector('.datagrid-body');
    if (!gridBody) return;

    const rows = Array.from(gridBody.querySelectorAll('.datagrid-row'));
    const searchInput = document.getElementById('gridSearch');
    const headers = document.querySelectorAll('.datagrid-header .datagrid-cell[data-sort]');
    const prevBtn = document.getElementById('gridPrev');
    const nextBtn = document.getElementById('gridNext');
    const showingSpan = document.getElementById('gridShowing');
    const totalSpan = document.getElementById('gridTotal');
    const pageIndicator = document.getElementById('gridPageIndicator');

    let currentPage = 1;
    let rowsPerPage = 10;
    let filteredRows = [...rows];
    let currentSort = { column: 'date', direction: 'desc' }; // Tri par défaut

    // Fonction de tri
    function sortRows(column, direction) {
        filteredRows.sort((a, b) => {
            let valA = a.dataset[column];
            let valB = b.dataset[column];
            
            // Gestion des dates et nombres
            if (column === 'date') {
                valA = new Date(valA);
                valB = new Date(valB);
            } else if (column === 'duree') {
                valA = parseFloat(valA) || 0;
                valB = parseFloat(valB) || 0;
            } else {
                valA = valA.toLowerCase();
                valB = valB.toLowerCase();
            }
            
            if (valA < valB) return direction === 'asc' ? -1 : 1;
            if (valA > valB) return direction === 'asc' ? 1 : -1;
            return 0;
        });
    }

    // Fonction de filtrage
    function filterRows(searchTerm) {
        if (!searchTerm) {
            filteredRows = [...rows];
        } else {
            const term = searchTerm.toLowerCase();
            filteredRows = rows.filter(row => {
                return Object.values(row.dataset).some(val => 
                    String(val).toLowerCase().includes(term)
                );
            });
        }
        // Réappliquer le tri après filtrage
        sortRows(currentSort.column, currentSort.direction);
    }

    // Fonction d'affichage de la page
    function renderPage() {
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const pageRows = filteredRows.slice(start, end);
        
        // Cacher toutes les lignes
        rows.forEach(row => row.style.display = 'none');
        // Afficher celles de la page
        pageRows.forEach(row => row.style.display = 'flex');
        
        // Mise à jour UI
        const total = filteredRows.length;
        const showingStart = total === 0 ? 0 : start + 1;
        const showingEnd = Math.min(end, total);
        
        showingSpan.textContent = total === 0 ? '0' : `${showingStart}-${showingEnd}`;
        totalSpan.textContent = total;
        pageIndicator.textContent = total === 0 ? 'Page 1' : `Page ${currentPage} / ${Math.ceil(total / rowsPerPage)}`;
        
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === Math.ceil(total / rowsPerPage) || total === 0;
    }

    // Gestion du tri
    headers.forEach(header => {
        header.addEventListener('click', () => {
            const column = header.dataset.sort;
            
            // Reset des icônes
            headers.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Déterminer la direction
            let direction = 'asc';
            if (currentSort.column === column) {
                direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            }
            
            // Appliquer la classe
            header.classList.add(`sort-${direction}`);
            
            // Mettre à jour et trier
            currentSort = { column, direction };
            sortRows(column, direction);
            
            currentPage = 1;
            renderPage();
        });
    });

    // Gestion de la recherche
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterRows(e.target.value);
            currentPage = 1;
            renderPage();
        }, 300);
    });

    // Pagination
    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderPage();
        }
    });
    
    nextBtn.addEventListener('click', () => {
        const maxPage = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < maxPage) {
            currentPage++;
            renderPage();
        }
    });

    // Initialisation
    // Appliquer le tri par défaut (Date desc) et l'icône
    const defaultHeader = Array.from(headers).find(h => h.dataset.sort === 'date');
    if(defaultHeader) {
        defaultHeader.classList.add('sort-desc');
    }
    sortRows('date', 'desc');
    renderPage();
});
</script>
@endpush

@push('styles')
<style>
    .avatar img, .avatar > div {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .activity {
        display: flex;
        margin-bottom: 1.8rem;
        align-items: flex-start;
    }
    .activity-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 1rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .activity-detail {
        flex: 1;
        min-width: 0;
    }
    .activity-detail p {
        margin-bottom: 0;
        line-height: 1.4;
    }
    .badge-lg {
        font-size: 1rem;
        padding: 0.5em 1em;
    }
    /* --- Data Grid Styles --- */
    .datagrid-container {
        display: flex;
        flex-direction: column;
        height: 500px; /* Hauteur fixe avec scroll */
        border: 1px solid #e4e6fc;
    }

    .datagrid-header {
        background-color: #f8f9fc;
        border-bottom: 2px solid #e4e6fc;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .datagrid-body {
        flex: 1;
        overflow-y: auto;
        background: #fff;
    }

    .datagrid-row {
        display: flex;
        border-bottom: 1px solid #f0f2f5;
        transition: background 0.15s;
    }

    .datagrid-body .datagrid-row:hover {
        background-color: #f9fafc;
    }

    .datagrid-cell {
        padding: 12px 15px;
        font-size: 0.9rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .datagrid-header .datagrid-cell {
        padding: 12px 15px;
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }

    .datagrid-header .datagrid-cell:hover {
        background-color: #edf2f7;
    }

    .datagrid-header .datagrid-cell i {
        margin-left: 5px;
        font-size: 0.8rem;
        opacity: 0.6;
    }

    .datagrid-header .datagrid-cell.sort-asc i::before {
        content: "\f0de"; /* fa-sort-up */
        opacity: 1;
        color: #6777ef;
    }

    .datagrid-header .datagrid-cell.sort-desc i::before {
        content: "\f0dd"; /* fa-sort-down */
        opacity: 1;
        color: #6777ef;
    }

    /* Largeurs des colonnes */
    .col-date { width: 15%; min-width: 100px; }
    .col-plage { width: 15%; min-width: 120px; }
    .col-travaux { width: 35%; min-width: 200px; }
    .col-rendu { width: 25%; min-width: 150px; }
    .col-duree { width: 10%; min-width: 100px; }

    /* Responsive */
    @media (max-width: 768px) {
        .datagrid-container {
            height: auto;
            max-height: 600px;
        }
        .datagrid-row {
            flex-wrap: wrap;
        }
        .datagrid-cell {
            width: 100% !important;
            padding: 8px 15px;
        }
        .datagrid-header {
            display: none; /* On cache le header sur mobile, on affichera via pseudo-éléments */
        }
    }

    .datagrid-footer {
        border-top: 1px solid #e4e6fc;
        background-color: #fafbfe;
    }
</style>
@endpush
