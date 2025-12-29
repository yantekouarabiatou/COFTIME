@extends('layaout')

@section('title', 'Détails Utilisateur')

@section('content')
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-eye"></i> Détails de l'utilisateur</h4>
                            <div class="card-header-action">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </a>
                                @can('modifier des utilisateurs')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                @endcan
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <!-- Informations principales -->
                                <div class="col-md-8">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-gradient-primary text-white py-3">
                                            <h5 class="mb-0"><i class="fas fa-user-circle mr-2"></i>Informations de l'utilisateur</h5>
                                        </div>
                                        <div class="card-body p-4">
                                            <!-- Photo et identité -->
                                            <div class="row mb-4">
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center bg-light-primary p-4 rounded">
                                                        <!-- Photo ou Avatar -->
                                                        <div class="user-avatar-large mr-4">
                                                            @if($user->photo)
                                                                <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->nom }}" class="rounded-circle">
                                                            @else
                                                                <div class="avatar-initials bg-primary text-white">
                                                                    {{ strtoupper(substr($user->nom, 0, 1) . substr($user->prenom, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        
                                                        <div class="flex-grow-1">
                                                            <h3 class="font-weight-bold text-dark mb-2">
                                                                {{ $user->nom }} {{ $user->prenom }}
                                                            </h3>
                                                            <div class="d-flex align-items-center mb-2">
                                                                @switch($user->is_active)
                                                                    @case('1')
                                                                        <span class="badge badge-success py-2 px-3 mr-2"><i class="fas fa-circle mr-1"></i> Actif</span>
                                                                        @break
                                                                    @case('0')
                                                                        <span class="badge badge-warning py-2 px-3 mr-2"><i class="fas fa-circle mr-1"></i> Inactif</span>
                                                                        @break
                                                                    @default
                                                                        <span class="badge badge-light">-</span>
                                                                @endswitch
                                                                <span class="badge badge-info py-2 px-3">
                                                                    <i class="fas fa-user-tag mr-1"></i>
                                                                    {{ ucfirst($user->role?->name ?? 'user') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Nom et Prénom -->
                                            <div class="row">
                                                <div class="col-md-6 mb-4">
                                                    <div class="info-item border-right pr-4">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="icon-circle bg-primary text-white mr-3">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <div>
                                                                <label class="text-muted small mb-0">Nom</label>
                                                                <h6 class="font-weight-bold text-dark mb-0">{{ $user->nom }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6 mb-4">
                                                    <div class="info-item">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="icon-circle bg-success text-white mr-3">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <div>
                                                                <label class="text-muted small mb-0">Prénom</label>
                                                                <h6 class="font-weight-bold text-dark mb-0">{{ $user->prenom }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Username et Email -->
                                            <div class="row mb-4">
                                                <div class="col-md-6 mb-3">
                                                    <div class="info-highlight bg-light-warning border-warning border-left-3 p-3 rounded">
                                                        <div class="d-flex align-items-center">
                                                            <div class="icon-circle bg-warning text-white mr-3">
                                                                <i class="fas fa-at"></i>
                                                            </div>
                                                            <div>
                                                                <label class="text-muted small mb-1">Nom d'utilisateur</label>
                                                                <h6 class="font-weight-bold text-dark mb-0">{{ $user->username }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <div class="info-highlight bg-light-info border-info border-left-3 p-3 rounded">
                                                        <div class="d-flex align-items-center">
                                                            <div class="icon-circle bg-info text-white mr-3">
                                                                <i class="fas fa-envelope"></i>
                                                            </div>
                                                            <div>
                                                                <label class="text-muted small mb-1">Email</label>
                                                                <h6 class="font-weight-bold text-dark mb-0">{{ $user->email }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Poste et Téléphone -->
                                            <div class="row">
                                                <div class="col-md-6 mb-4">
                                                    <div class="info-card bg-light-primary p-3 rounded text-center">
                                                        <div class="mb-2">
                                                            <i class="fas fa-briefcase fa-2x text-primary"></i>
                                                        </div>
                                                        <label class="text-muted small mb-1">Poste</label>
                                                        <h5 class="font-weight-bold text-dark mb-0">
                                                            {{ $user->poste->libelle ?? $user->poste->intitule ?? 'Non défini' }}
                                                        </h5>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6 mb-4">
                                                    <div class="info-card bg-light-success p-3 rounded text-center">
                                                        <div class="mb-2">
                                                            <i class="fas fa-phone fa-2x text-success"></i>
                                                        </div>
                                                        <label class="text-muted small mb-1">Téléphone</label>
                                                        <h5 class="font-weight-bold text-dark mb-0">
                                                            {{ $user->telephone ?? 'Non renseigné' }}
                                                        </h5>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Notes -->
                                            @if($user->notes)
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="info-section bg-light-info p-3 rounded">
                                                        <div class="d-flex align-items-start mb-2">
                                                            <div class="icon-circle bg-info text-white mr-3 mt-1">
                                                                <i class="fas fa-sticky-note"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <label class="text-muted small mb-1">Notes / Observations</label>
                                                                <p class="text-dark mb-0 line-height-2">{{ $user->notes }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations complémentaires -->
                                <div class="col-md-4">
                                    <!-- Créateur -->
                                    <div class="card shadow-sm border-0 mb-4">
                                        <div class="card-header bg-gradient-success text-white py-3">
                                            <h5 class="mb-0"><i class="fas fa-user-shield mr-2"></i>Créé par</h5>
                                        </div>
                                        <div class="card-body p-4">
                                            @if($user->creator)
                                            <div class="text-center mb-4">
                                                <div class="avatar-circle-small bg-success mx-auto mb-3">
                                                    @if($user->creator->photo)
                                                        <img src="{{ Storage::url($user->creator->photo) }}" alt="{{ $user->creator->nom }}" class="rounded-circle w-100 h-100">
                                                    @else
                                                        <div class="avatar-initials-small text-white">
                                                            {{ strtoupper(substr($user->creator->nom, 0, 1) . substr($user->creator->prenom, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <h6 class="font-weight-bold text-dark mb-1">
                                                    {{ $user->creator->nom }} {{ $user->creator->prenom }}
                                                </h6>
                                                <p class="text-muted small mb-0">{{ $user->creator->email }}</p>
                                            </div>
                                            
                                            <div class="user-stats">
                                                <div class="stat-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                                    <span class="text-muted">Poste</span>
                                                    <span class="font-weight-bold text-dark">
                                                        {{ $user->creator->poste->intitule ?? 'N/A' }}
                                                    </span>
                                                </div>
                                                <!-- <div class="stat-item d-flex justify-content-between align-items-center py-2">
                                                    <span class="text-muted">Rôle</span>
                                                    <span class="badge badge-info">{{ ucfirst($user->creator->role ?? 'user') }}</span>
                                                </div> -->
                                            </div>
                                            @else
                                            <div class="text-center text-muted">
                                                <i class="fas fa-user-slash fa-3x mb-3 opacity-50"></i>
                                                <p>Information non disponible</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Statistiques -->
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-gradient-info text-white py-3">
                                            <h5 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Statistiques</h5>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="stat-box text-center mb-3 p-3 bg-light rounded">
                                                <div class="stat-icon bg-primary text-white rounded-circle mx-auto mb-2">
                                                    <i class="fas fa-tasks"></i>
                                                </div>
                                                <h4 class="font-weight-bold text-primary mb-0">0</h4>
                                                <small class="text-muted">Tâches assignées</small>
                                            </div>

                                            <div class="stat-box text-center mb-3 p-3 bg-light rounded">
                                                <div class="stat-icon bg-success text-white rounded-circle mx-auto mb-2">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <h4 class="font-weight-bold text-success mb-0">0</h4>
                                                <small class="text-muted">Tâches complétées</small>
                                            </div>

                                            <div class="stat-box text-center p-3 bg-light rounded">
                                                <div class="stat-icon bg-warning text-white rounded-circle mx-auto mb-2">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <h4 class="font-weight-bold text-warning mb-0">0</h4>
                                                <small class="text-muted">En cours</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Métadonnées -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card bg-light border-0">
                                        <div class="card-body py-3">
                                            <div class="row text-center">
                                                <div class="col-md-4">
                                                    <div class="metadata-item">
                                                        <i class="fas fa-calendar-plus text-primary mb-2"></i>
                                                        <div class="text-muted small">Compte créé le</div>
                                                        <div class="font-weight-bold text-dark">
                                                            {{ $user->created_at->format('d/m/Y à H:i') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="metadata-item">
                                                        <i class="fas fa-calendar-check text-success mb-2"></i>
                                                        <div class="text-muted small">Dernière modification</div>
                                                        <div class="font-weight-bold text-dark">
                                                            {{ $user->updated_at->format('d/m/Y à H:i') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="metadata-item">
                                                        <i class="fas fa-sign-in-alt text-info mb-2"></i>
                                                        <div class="text-muted small">Dernière connexion</div>
                                                        <div class="font-weight-bold text-dark">
                                                            {{ $user->updated_at ? $user->updated_at->format('d/m/Y à H:i') : 'Jamais connecté' }}
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
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important;
    }
    
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .user-avatar-large {
        width: 120px;
        height: 120px;
        flex-shrink: 0;
    }
    
    .user-avatar-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-initials {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: bold;
    }
    
    .avatar-circle-small {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .avatar-initials-small {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .info-highlight {
        border-left: 4px solid !important;
    }
    
    .border-left-3 {
        border-left-width: 3px !important;
    }
    
    .info-card {
        transition: transform 0.2s ease-in-out;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
    }
    
    .bg-light-primary {
        background-color: rgba(102, 126, 234, 0.1) !important;
    }
    
    .bg-light-success {
        background-color: rgba(16, 185, 129, 0.1) !important;
    }
    
    .bg-light-warning {
        background-color: rgba(245, 158, 11, 0.1) !important;
    }
    
    .bg-light-info {
        background-color: rgba(6, 182, 212, 0.1) !important;
    }
    
    .line-height-2 {
        line-height: 1.6;
    }
    
    .metadata-item {
        padding: 0.5rem;
    }
    
    .user-stats {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.75rem;
    }
    
    .stat-item:last-child {
        border-bottom: none !important;
    }
    
    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
    
    .stat-box {
        border: 1px solid #e9ecef;
    }
    
    .opacity-50 {
        opacity: 0.5;
    }
    
    @media (max-width: 768px) {
        .border-right {
            border-right: none !important;
            padding-right: 0 !important;
            margin-bottom: 1rem;
        }
        
        .user-avatar-large {
            width: 80px;
            height: 80px;
        }
        
        .avatar-initials {
            width: 80px;
            height: 80px;
            font-size: 1.8rem;
        }
        
        .icon-circle {
            width: 35px;
            height: 35px;
            font-size: 0.9rem;
        }
    }
</style>
@endpush