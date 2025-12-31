@extends('layaout')

@section('title', 'Détail du Congé')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-calendar-check"></i> Détail du Congé</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item">
                <a href="{{ route('conges.index') }}">Congés</a>
            </div>
            <div class="breadcrumb-item active">Détails</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-8 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Informations du congé</h4>
                        <div class="card-header-action">
                            <a href="{{ route('conges.index') }}" class="btn btn-icon btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Type de congé :</strong><br>
                                <span class="badge badge-info">
                                    {{ ucfirst(strtolower($conge->type_conge)) }}
                                </span>
                            </div>

                            <div class="col-md-6">
                                <strong>Durée :</strong><br>
                                {{ \Carbon\Carbon::parse($conge->date_debut)
                                    ->diffInDays(\Carbon\Carbon::parse($conge->date_fin)) + 1 }}
                                jour(s)
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Date de début :</strong><br>
                                {{ \Carbon\Carbon::parse($conge->date_debut)->format('d/m/Y') }}
                            </div>

                            <div class="col-md-6">
                                <strong>Date de fin :</strong><br>
                                {{ \Carbon\Carbon::parse($conge->date_fin)->format('d/m/Y') }}
                            </div>
                        </div>

                        <hr>

                        <div class="text-right">
                            <a href="{{ route('conges.edit', $conge) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
