@extends('layaout')

@section('title', 'Détail Cadeau/Invitation - ' . ($cadeauInvitation->nom ?? 'Inconnu'))

@section('content')
<section class="section">
    <div class="section-header">
        <h1>
            <i class="fas fa-gift text-danger"></i>
            {{ $cadeauInvitation->nom ?? 'Cadeau/Invitation sans nom' }}
        </h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            @can('voir des cadeaux et invitations')
            <div class="breadcrumb-item"><a href="{{ route('cadeau-invitations.index') }}">Cadeaux & Invitations</a></div>
            @endcan
            <div class="breadcrumb-item">Détail</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary shadow-lg">
                    <div class="card-header bg-gradient-danger text-white">
                        <h4><i class="fas fa-gift"></i> Fiche Cadeau / Hospitalité</h4>
                        <div class="card-header-action">
                            <!-- Badge Action Prise -->
                            @php
                                $actionColor = match($cadeauInvitation->action_prise) {
                                    'accepté'  => 'success',
                                    'refusé'   => 'danger',
                                    'déclaré'  => 'info',
                                    default    => 'warning'
                                };
                            @endphp
                            <span class="badge badge-lg badge-{{ $actionColor }} mr-3">
                                {{ ucfirst($cadeauInvitation->action_prise ?? 'Non défini') }}
                            </span>
                            @can('modifier des cadeaux et invitations')
                            <a href="{{ route('cadeau-invitations.edit', $cadeauInvitation) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            @endcan
                            @can('voir des cadeaux et invitations')
                            <a href="{{ route('cadeau-invitations.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Colonne principale : Informations -->
                            <div class="col-lg-8">
                                <table class="table table-bordered table-striped table-hover mb-4">
                                    <tbody>
                                        <tr>
                                            <th width="250">Bénéficiaire</th>
                                            <td>
                                                <strong class="text-lg text-danger">{{ $cadeauInvitation->nom ?? '-' }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Date de l'événement</th>
                                            <td>
                                                <i class="fas fa-calendar-alt text-primary"></i>
                                                {{ $cadeauInvitation->date?->format('d/m/Y') ?? '<em class="text-muted">Non renseignée</em>' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Type</th>
                                            <td>
                                                <span class="badge badge-info badge-pill">
                                                    {{ $cadeauInvitation->cadeau_hospitalite ?? 'Non précisé' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Valeur estimée</th>
                                            <td>
                                                <strong class="text-success h5">
                                                    {{ $cadeauInvitation->valeurs_formatted ?? '0 FCFA' }}
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Action prise</th>
                                            <td>
                                                <h5>
                                                    <span class="badge badge-lg badge-{{ $actionColor }}">
                                                        {{ ucfirst($cadeauInvitation->action_prise ?? 'En attente') }}
                                                    </span>
                                                </h5>
                                            </td>
                                        </tr>
                                        @if($cadeauInvitation->description)
                                        <tr>
                                            <th>Description</th>
                                            <td>
                                                <p class="text-muted mb-0">{{ $cadeauInvitation->description }}</p>
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>Responsable du traitement</th>
                                            <td>
                                                @if($cadeauInvitation->responsable)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-md mr-3">
                                                            @if($cadeauInvitation->responsable->avatar)
                                                                <img src="{{ asset('storage/avatars/' . $cadeauInvitation->responsable->avatar) }}" class="rounded-circle">
                                                            @else
                                                                <div class="avatar-title bg-primary rounded-circle text-white justify-content-center">
                                                                    {{ strtoupper(substr($cadeauInvitation->responsable->prenom, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <strong>{{ $cadeauInvitation->responsable->prenom }} {{ $cadeauInvitation->responsable->nom }}</strong><br>
                                                            <small class="text-muted">{{ $cadeauInvitation->responsable->email }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <em class="text-muted">Aucun responsable assigné</em>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Créé par</th>
                                            <td>
                                                {{ $cadeauInvitation->user->prenom ?? 'Supprimé' }}
                                                {{ $cadeauInvitation->user->nom ?? '' }}
                                                <br><small class="text-muted">{{ $cadeauInvitation->user->email ?? '' }}</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Date de création</th>
                                            <td><i class="far fa-clock"></i> {{ $cadeauInvitation->created_at->format('d/m/Y à H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Dernière modification</th>
                                            <td>{{ $cadeauInvitation->updated_at->diffForHumans() }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Colonne droite : Document + QR Code -->
                            <div class="col-lg-4">

                                <!-- Document joint -->
                                <div class="card border-left-primary shadow-sm mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-paperclip"></i> Document joint</h5>
                                    </div>
                                    <div class="card-body text-center py-4">
                                        @if($cadeauInvitation->document)
                                            <a href="{{ route('cadeau-invitations.download', $cadeauInvitation) }}" target="_blank">
                                                @if(Str::endsWith(strtolower($cadeauInvitation->document), '.pdf'))
                                                    <img src="{{ asset('assets/img/pdf.png') }}" alt="PDF" class="img-fluid mb-3" style="max-height: 140px;">
                                                @else
                                                    <img src="{{ Storage::url($cadeauInvitation->document) }}" alt="Document" class="img-fluid rounded shadow mb-3" style="max-height: 180px;">
                                                @endif
                                                <br>
                                                <span class="btn btn-primary btn-sm">
                                                    <i class="fas fa-download"></i> Télécharger le document
                                                </span>
                                            </a>
                                        @else
                                            <div class="text-center text-muted py-5">
                                                <i class="fas fa-file-alt fa-4x mb-3 opacity-50"></i>
                                                <p class="mb-0">Aucun document joint</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- QR Code -->
                                <div class="card border-left-info shadow-sm">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-qrcode"></i> Accès rapide</h6>
                                    </div>
                                    <div class="card-body text-center py-4">
                                        <img
                                            src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ route('cadeau-invitations.show', $cadeauInvitation) }}"
                                            alt="QR Code"
                                            class="img-fluid border rounded shadow-sm p-2 bg-white">
                                        <p class="mt-3 text-muted"><small>Scannez pour ouvrir cette fiche</small></p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Footer métadonnées -->
                    <div class="card-footer bg-light">
                        <div class="row text-center text-md-left text-muted small">
                            <div class="col-md-4">
                                <i class="fas fa-calendar-plus"></i> Créé le {{ $cadeauInvitation->created_at->format('d/m/Y à H:i') }}
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-sync-alt"></i> Modifié {{ $cadeauInvitation->updated_at->diffForHumans() }}
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-user"></i> Par {{ $cadeauInvitation->user->prenom ?? 'Système' }} {{ $cadeauInvitation->user->nom ?? '' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
