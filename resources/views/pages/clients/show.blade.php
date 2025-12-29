@extends('layaout')

@section('title', 'Détail clientAudit Audit - ' . ($clientAudit->nom_client ?? 'Inconnu'))

@section('content')
<section class="section">
    <div class="section-header">
        <h1>
            <i class="fas fa-building text-primary"></i>
            {{ $clientAudit->nom_client ?? 'Client sans nom' }}
        </h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('clients-audit.index') }}">Clients Audits</a></div>
            <div class="breadcrumb-item">Détail</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Fiche complète du client</h4>
                        <div class="card-header-action">
                            
                            <a href="{{ route('clients-audit.edit', $clientAudit) }}" class="btn btn-warning ml-3">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="{{ route('clients-audit.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Informations principales -->
                            <div class="col-lg-8">
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>
                                        <tr>
                                            <th width="250">Nom du client</th>
                                            <td>
                                                <strong class="text-lg">{{ $clientAudit->nom_client ?? '-' }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Siège social</th>
                                            <td>{{ $clientAudit->siege_social ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Adresse</th>
                                            <td>{{ $clientAudit->adresse ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Frais d'audit</th>
                                            <td>
                                                @if($clientAudit->frais_audit)
                                                    <strong class="text-success">{{ number_format($clientAudit->frais_audit, 2, ',', ' ') }} FCFA</strong>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Autres frais</th>
                                            <td>
                                                @if($clientAudit->frais_autres)
                                                    <strong class="text-info">{{ number_format($clientAudit->frais_autres, 2, ',', ' ') }} FCFA</strong>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Total facturé</th>
                                            <td>
                                                <strong class="text-dark h5">
                                                    {{ number_format(($clientAudit->frais_audit ?? 0) + ($client->frais_autres ?? 0), 2, ',', ' ') }} FCFA
                                                </strong>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <th>Créé par</th>
                                            <td>
                                                {{ $clientAudit->user->nom ?? 'Supprimé' }} {{$clientAudit->user->prenom }}
                                                <br><small class="text-muted">{{ $clientAudit->user->email ?? '' }}</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Date de création</th>
                                            <td>{{ $clientAudit->created_at->format('d/m/Y à H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Dernière modification</th>
                                            <td>{{ $clientAudit->updated_at->diffForHumans() }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Document joint + QR Code -->
                            <div class="col-lg-4">
                                @if($clientAudit->document)
                                    <div class="card border-left-success">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0"><i class="fas fa-paperclip"></i> Document joint</h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <a href="{{ Storage::url($clientAudit->document) }}" target="_blank">
                                                @if(Str::endsWith($clientAudit->document, ['.pdf']))
                                                    <img src="{{ asset('assets/img/pdf.png') }}" alt="PDF" class="img-fluid mb-3" style="max-height: 150px;">
                                                @else
                                                    <img src="{{ Storage::url($clientAudit->document) }}" alt="Document" class="img-fluid rounded shadow" style="max-height: 200px;">
                                                @endif
                                                <br>
                                                <span class="btn btn-primary btn-sm mt-2">
                                                    <i class="fas fa-download"></i> Télécharger
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-file-alt fa-4x mb-3 opacity-50"></i>
                                        <p>Aucun document joint</p>
                                    </div>
                                @endif

                                <!-- QR Code du lien (bonus stylé) -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6><i class="fas fa-qrcode"></i> QR Code</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ route('clients-audit.show', $clientAudit) }}" alt="QR Code" class="img-fluid">
                                        <p class="mt-2"><small>Scannez pour accéder à cette fiche</small></p>
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
