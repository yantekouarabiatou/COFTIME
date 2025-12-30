@extends('layaout')

@section('title', 'Gestion des Dossiers')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-folder"></i> Gestion des Dossiers</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">Dossiers</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Tous les Dossiers</h4>
                        <div class="card-header-action">
                            <a href="{{ route('dossiers.create') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-plus"></i> Nouveau Dossier
                            </a>
                            <div class="dropdown d-inline">
                                <button class="btn btn-icon icon-left btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                                    <i class="fas fa-filter"></i> Filtres
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('dossiers.index') }}">Tous</a>
                                    <a class="dropdown-item" href="{{ route('dossiers.index', ['statut' => 'en_cours']) }}">En cours</a>
                                    <a class="dropdown-item" href="{{ route('dossiers.index', ['statut' => 'cloture']) }}">Clôturés</a>
                                    <a class="dropdown-item" href="{{ route('dossiers.index', ['statut' => 'en_retard']) }}">En retard</a>
                                    <div class="dropdown-divider"></div>
                                    @foreach(['audit', 'conseil', 'formation', 'expertise', 'autre'] as $type)
                                        <a class="dropdown-item" href="{{ route('dossiers.index', ['type' => $type]) }}">
                                            {{ ucfirst($type) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <form action="{{ route('dossiers.index') }}" method="GET">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control"
                                               placeholder="Rechercher..." value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-8 text-right">
                                <div class="btn-group" role="group">
                                    <a href="#" class="btn btn-success">
                                        <i class="fas fa-file-export"></i> Exporter
                                    </a>
                                    
                                </div>
                            </div>
                        </div>

                        @if($dossiers->isEmpty())
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <h2>Aucun dossier trouvé</h2>
                            <p class="lead">
                                Commencez par créer votre premier dossier.
                            </p>
                            <a href="{{ route('dossiers.create') }}" class="btn btn-primary mt-4">
                                <i class="fas fa-plus"></i> Créer un dossier
                            </a>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Référence</th>
                                        <th>Nom</th>
                                        <th>Client</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Dates</th>
                                        <th>Budget</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dossiers as $dossier)
                                    <tr class="{{ $dossier->en_retard ? 'table-danger' : '' }}">
                                        <td>
                                            <strong>{{ $dossier->reference }}</strong>
                                            @if($dossier->en_retard)
                                                <span class="badge badge-danger" title="En retard">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('dossiers.show', $dossier) }}">
                                                {{ Str::limit($dossier->nom, 30) }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('clients.show', $dossier->client) }}">
                                                {{ Str::limit($dossier->client->nom, 25) }}
                                            </a>
                                        </td>
                                        <td>{!! $dossier->type_dossier_badge !!}</td>
                                        <td>{!! $dossier->statut_badge !!}</td>
                                        <td>
                                            <small>
                                                <strong>Ouvert:</strong> {{ $dossier->date_ouverture->format('d/m/Y') }}<br>
                                                @if($dossier->date_cloture_prevue)
                                                <strong>Clôture prévue:</strong> {{ $dossier->date_cloture_prevue->format('d/m/Y') }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            @if($dossier->budget)
                                                <span class="badge badge-light">
                                                    {{ $dossier->budget_formate }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('dossiers.show', $dossier) }}"
                                                   class="btn btn-sm btn-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('dossiers.edit', $dossier) }}"
                                                   class="btn btn-sm btn-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger"
                                                        title="Supprimer"
                                                        data-toggle="modal"
                                                        data-target="#deleteModal{{ $dossier->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Modal de suppression -->
                                            <div class="modal fade" id="deleteModal{{ $dossier->id }}" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Confirmer la suppression</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Supprimer le dossier <strong>{{ $dossier->nom }}</strong> ?</p>
                                                            <p class="text-danger">Cette action est irréversible.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                            <form action="{{ route('dossiers.destroy', $dossier) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                Affichage de {{ $dossiers->firstItem() }} à {{ $dossiers->lastItem() }} sur {{ $dossiers->total() }} dossiers
                            </div>
                            <div>
                                {{ $dossiers->links() }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Dossiers</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalDossiers }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>En cours</h4>
                        </div>
                        <div class="card-body">
                            {{ $dossiersEnCours }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>En retard</h4>
                        </div>
                        <div class="card-body">
                            {{ $dossiersEnRetard }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Clôturés</h4>
                        </div>
                        <div class="card-body">
                            {{ $dossiersClotures }}
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
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.75em;
    }
    .empty-state {
        padding: 40px 0;
        text-align: center;
    }
    .empty-state-icon {
        font-size: 80px;
        color: #ddd;
        margin-bottom: 20px;
    }
</style>
@endpush
