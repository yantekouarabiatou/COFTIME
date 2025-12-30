@extends('layaout')

@section('title', 'Gestion des Congés')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-calendar-check"></i> Gestion des Congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </div>
            <div class="breadcrumb-item">Congés</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">

                    <!-- Header -->
                    <div class="card-header">
                        <h4>Liste des congés</h4>
                        <div class="card-header-action">
                            <a href="{{ route('conges.create') }}" class="btn btn-primary btn-icon icon-left">
                                <i class="fas fa-plus"></i> Nouveau congé
                            </a>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body">

                        <!-- Filtres -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Type de congé</label>
                                    <select id="type-filter" class="form-control select2">
                                        <option value="">Tous les types</option>
                                        <option value="MALADIE">Maladie</option>
                                        <option value="MATERNITE">Maternité</option>
                                        <option value="REMUNERE">Rémunéré</option>
                                        <option value="NON REMUNERE">Non rémunéré</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Recherche</label>
                                    <input type="text" id="search-input" class="form-control"
                                           placeholder="Utilisateur, type de congé...">
                                </div>
                            </div>

                            <div class="col-md-3 text-right" style="padding-top: 30px;">
                                <button type="button" id="reset-filters"
                                        class="btn btn-outline-secondary btn-icon icon-left">
                                    <i class="fas fa-redo"></i> Réinitialiser
                                </button>
                            </div>
                        </div>

                        <!-- Tableau -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="conges-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        @role('admin')
                                        <th>Utilisateur</th>
                                        @endrole
                                        <th>Type</th>
                                        <th>Date début</th>
                                        <th>Date fin</th>
                                        <th>Durée (jours)</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conges as $conge)
                                        <tr data-type="{{ $conge->type_conge }}">
                                            <td>{{ $loop->iteration }}</td>

                                            @role('admin')
                                            <td>
                                                {{ $conge->user->prenom ?? '' }}
                                                {{ $conge->user->nom ?? '' }}
                                            </td>
                                            @endrole

                                            <td>
                                                <span class="badge badge-info">
                                                    {{ ucfirst(strtolower($conge->type_conge)) }}
                                                </span>
                                            </td>

                                            <td>{{ \Carbon\Carbon::parse($conge->date_debut)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($conge->date_fin)->format('d/m/Y') }}</td>

                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($conge->date_debut)
                                                    ->diffInDays(\Carbon\Carbon::parse($conge->date_fin)) + 1 }}
                                            </td>

                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{{ route('conges.show', $conge) }}"
                                                    class="btn btn-info btn-sm" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('conges.edit', $conge) }}"
                                                    class="btn btn-primary btn-sm" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('conges.destroy', $conge) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                                class="btn btn-danger btn-sm delete-btn"
                                                                title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Statistiques -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-primary">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Total congés</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $conges->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-danger">
                                        <i class="fas fa-procedures"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Maladie</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $conges->where('type_conge', 'MALADIE')->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success">
                                        <i class="fas fa-umbrella-beach"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Rémunérés</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $conges->where('type_conge', 'REMUNERE')->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning">
                                        <i class="fas fa-ban"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Non rémunérés</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $conges->where('type_conge', 'NON REMUNERE')->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
