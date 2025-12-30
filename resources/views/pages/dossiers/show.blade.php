@extends('layaout')

@section('title', 'Dossier - ' . $dossier->nom)

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-folder-open"></i> Dossier: {{ $dossier->nom }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">Dossiers</a></div>
            <div class="breadcrumb-item active">{{ $dossier->reference }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Informations du Dossier</h4>
                        <div class="card-header-action">
                            <a href="{{ route('dossiers.edit', $dossier) }}" class="btn btn-icon icon-left btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="{{ route('dossiers.index') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="button" class="btn btn-icon icon-left btn-danger" data-toggle="modal" data-target="#deleteModal">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Colonne de gauche -->
                            <div class="col-md-8">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h2 class="mb-0">{{ $dossier->nom }}</h2>
                                                <div class="mt-2">
                                                    {!! $dossier->type_dossier_badge !!}
                                                    {!! $dossier->statut_badge !!}
                                                    @if($dossier->en_retard)
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-exclamation-triangle"></i> En retard
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <h4 class="text-primary">{{ $dossier->reference }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations client -->
                                <div class="card card-primary mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-user"></i> Informations Client</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Client:</strong>
                                                    <a href="{{ route('clients.show', $dossier->client) }}">
                                                        {{ $dossier->client->nom }}
                                                    </a>
                                                </p>
                                                @if($dossier->client->contact_principal)
                                                <p><strong>Contact:</strong> {{ $dossier->client->contact_principal }}</p>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if($dossier->client->telephone)
                                                <p><strong>Téléphone:</strong>
                                                    <a href="tel:{{ $dossier->client->telephone }}">{{ $dossier->client->telephone }}</a>
                                                </p>
                                                @endif
                                                @if($dossier->client->email)
                                                <p><strong>Email:</strong>
                                                    <a href="mailto:{{ $dossier->client->email }}">{{ $dossier->client->email }}</a>
                                                </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dates et durée -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <div class="info-box-icon bg-info">
                                                <i class="fas fa-calendar-plus"></i>
                                            </div>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Date d'ouverture</span>
                                                <span class="info-box-number">{{ $dossier->date_ouverture->format('d/m/Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <div class="info-box-icon bg-warning">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Clôture prévue</span>
                                                <span class="info-box-number">
                                                    @if($dossier->date_cloture_prevue)
                                                        {{ $dossier->date_cloture_prevue->format('d/m/Y') }}
                                                    @else
                                                        <span class="text-muted">Non définie</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <div class="info-box-icon bg-success">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Durée</span>
                                                <span class="info-box-number">{{ $dossier->duree }} jours</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Budget et frais -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card card-success">
                                            <div class="card-header">
                                                <h4><i class="fas fa-money-bill-wave"></i> Budget</h4>
                                            </div>
                                            <div class="card-body">
                                                <h3 class="text-success">{{ $dossier->budget_formate }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card card-info">
                                            <div class="card-header">
                                                <h4><i class="fas fa-receipt"></i> Frais de dossier</h4>
                                            </div>
                                            <div class="card-body">
                                                <h3 class="text-info">{{ $dossier->frais_dossier_formate }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                @if($dossier->description)
                                <div class="card card-secondary mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-align-left"></i> Description</h4>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $dossier->description }}</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Notes -->
                                @if($dossier->notes)
                                <div class="card card-warning mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-sticky-note"></i> Notes</h4>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $dossier->notes }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Colonne de droite -->
                            <div class="col-md-4">
                                <!-- Document attaché -->
                                @if($dossier->document)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-file"></i> Document</h4>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                        </div>
                                        <p class="mb-2">{{ $dossier->document_name }}</p>
                                        <a href="{{ $dossier->document_url }}"
                                           class="btn btn-primary btn-sm"
                                           target="_blank"
                                           download>
                                            <i class="fas fa-download"></i> Télécharger
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <!-- Informations techniques -->
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-info-circle"></i> Informations Techniques</h4>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <i class="fas fa-calendar-alt mr-2"></i>
                                                <strong>Créé le:</strong>
                                                {{ $dossier->created_at->format('d/m/Y H:i') }}
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-sync mr-2"></i>
                                                <strong>Modifié le:</strong>
                                                {{ $dossier->updated_at->format('d/m/Y H:i') }}
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-hashtag mr-2"></i>
                                                <strong>ID:</strong> {{ $dossier->id }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Actions rapides -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-bolt"></i> Actions Rapides</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group">
                                            <a href="{{ route('dossiers.edit', $dossier) }}" class="list-group-item list-group-item-action">
                                                <i class="fas fa-edit mr-2"></i> Modifier le dossier
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" onclick="window.print()">
                                                <i class="fas fa-print mr-2"></i> Imprimer cette page
                                            </a>
                                            <a href="{{ route('clients.show', $dossier->client) }}" class="list-group-item list-group-item-action">
                                                <i class="fas fa-external-link-alt mr-2"></i> Voir le client
                                            </a>
                                            <a href="{{ route('dossiers.create') }}" class="list-group-item list-group-item-action">
                                                <i class="fas fa-plus mr-2"></i> Créer un autre dossier
                                            </a>
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

<!-- Modal de suppression -->
<div class="modal fade" tabindex="-1" role="dialog" id="deleteModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation de suppression</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Supprimer le dossier <strong>{{ $dossier->nom }}</strong> ?</p>
                <p class="text-danger">Cette action supprimera également tous les documents associés.</p>
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
@endsection

@push('styles')
<style>
    .info-box {
        min-height: 90px;
    }
    .info-box-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        font-size: 30px;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    .list-group-item:first-child {
        border-top: none;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Gestion de l'impression
        function printPage() {
            window.print();
        }
    });
</script>
@endpush
