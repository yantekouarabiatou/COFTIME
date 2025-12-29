@extends('layaout')

@section('title', 'Profil de ' . $user->nom)

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 bg-transparent">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
                                    <li class="breadcrumb-item active">{{ $user->nom }}</li>
                                </ol>
                            </nav>

                            <div class="mt-3 mt-md-0">
                                @if(auth()->user()->hasRole('admin|super-admin') && auth()->id() != $user->id)
                                    @if($user->is_active)
                                        <form action="{{ route('user-profile.deactivate', $user->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-warning btn-sm"
                                                    onclick="return confirm('Désactiver cet utilisateur ?')">
                                                <i class="fas fa-user-slash"></i> Désactiver
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('user-profile.activate', $user->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-user-check"></i> Activer
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm ml-2">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card card-primary shadow-sm">
                    <div class="card-body text-center py-5">
                        @if($user->photo)
                            <img src="{{ asset('storage/' . $user->photo) }}"
                                 alt="Photo de {{ $user->nom }}"
                                 class="rounded-circle mb-3 shadow"
                                 style="width: 160px; height: 160px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 shadow"
                                 style="width: 160px; height: 160px; font-size: 60px; border: 3px solid #dee2e6;">
                                <i class="fas fa-user text-muted"></i>
                            </div>
                        @endif

                        <h4 class="mb-1">{{ $user->nom }}</h4>
                        <p class="text-muted mb-2">{{ $user->poste?->intitule ?? 'Poste non défini' }}</p>

                        {{-- CORRECTION DE L'AFFICHAGE DU RÔLE ICI --}}
                        <div class="mb-3">
                            <span class="badge badge-lg {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $user->is_active ? 'ACTIF' : 'INACTIF' }}
                            </span>

                            @php
                                // Récupération du rôle principal
                                $role = $user->roles->first();

                                // Tableau de correspondance pour l'affichage
                                $roleNames = [
                                    'super-admin'            => 'Super Administrateur',
                                    'admin'                  => 'Administrateur',
                                    'responsable-conformite' => 'Responsable Conformité',
                                    'auditeur'               => 'Auditeur Interne',
                                    'gestionnaire-plaintes'  => 'Gestionnaire des Plaintes',
                                    'agent'                  => 'Agent de Traitement',
                                    'user'                   => 'Utilisateur Standard',
                                ];

                                // Définition du libellé à afficher
                                $displayRole = 'Aucun rôle';
                                if ($role) {
                                    $displayRole = $roleNames[$role->name] ?? ucwords(str_replace('-', ' ', $role->name));
                                }
                            @endphp

                            <span class="badge badge-info ml-2">
                                {{ $displayRole }}
                            </span>
                        </div>
                        {{-- FIN CORRECTION --}}

                        @if($user->photo)
                            <a href="{{ route('user-profile.download-photo', $user->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Télécharger la photo
                            </a>
                        @endif
                    </div>

                    <div class="card-footer bg-light">
                        <div class="row text-center small">
                            <div class="col border-right">
                                <div class="text-muted">Téléphone</div>
                                <strong class="d-block">{{ $user->telephone ?? '-' }}</strong>
                            </div>
                            <div class="col">
                                <div class="text-muted">Email</div>
                                <strong class="d-block">{{ $user->email }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-primary mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-user-circle mr-2"></i>Informations du compte</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-3">
                            <span class="text-muted">Nom d'utilisateur</span>
                            <span class="font-weight-bold">{{ $user->username }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-3">
                            <span class="text-muted">Créé le</span>
                            <span>{{ $user->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-3">
                            <span class="text-muted">Créé par</span>
                            <span>{{ $user->nom ?? 'Système' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-3">
                            <span class="text-muted">Dernière modification</span>
                            <span>{{ $user->updated_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="row mb-4">
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Plaintes</h6></div>
                                <div class="card-body h4">{{ $statistiques['total_plaintes'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Audits Clients</h6></div>
                                <div class="card-body h4">{{ $statistiques['total_audits'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-info">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Cadeaux/Invitations</h6></div>
                                <div class="card-body h4">{{ $statistiques['total_cadeaux'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-balance-scale"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Conflits d'Intérêt</h6></div>
                                <div class="card-body h4">{{ $statistiques['total_interets'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-success">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h7><strong>Indépendance</strong></h7></div>
                                <div class="card-body h4">{{ $statistiques['total_independances'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-dark">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Total Activités</h6></div>
                                <div class="card-body h4">{{ array_sum($statistiques) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-history mr-2"></i>Dernières activités</h4>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="activityTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="plaintes-tab" data-toggle="tab" href="#plaintes" role="tab">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Plaintes
                                    <span class="badge badge-danger ml-2">{{ $user->plaintes->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="audits-tab" data-toggle="tab" href="#audits" role="tab">
                                    <i class="fas fa-building mr-1"></i> Audits
                                    <span class="badge badge-primary ml-2">{{ $user->clientAudits->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="cadeaux-tab" data-toggle="tab" href="#cadeaux" role="tab">
                                    <i class="fas fa-gift mr-1"></i> Cadeaux
                                    <span class="badge badge-info ml-2">{{ $user->cadeauInvitations->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="interets-tab" data-toggle="tab" href="#interets" role="tab">
                                    <i class="fas fa-balance-scale mr-1"></i> Conflits
                                    <span class="badge badge-warning ml-2">{{ $user->interets->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="independances-tab" data-toggle="tab" href="#independances" role="tab">
                                    <i class="fas fa-user-shield mr-1"></i> Indépendances
                                    <span class="badge badge-success ml-2">{{ $user->independances->count() }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-4">
                            <div class="tab-pane fade show active" id="plaintes" role="tabpanel">
                                @include('profile.partials.activities-table', [
                                    'items' => $user->plaintes,
                                    'type' => 'plainte'
                                ])
                            </div>

                            <div class="tab-pane fade" id="audits" role="tabpanel">
                                @include('profile.partials.activities-table', [
                                    'items' => $user->clientAudits,
                                    'type' => 'audit'
                                ])
                            </div>

                            <div class="tab-pane fade" id="cadeaux" role="tabpanel">
                                @include('profile.partials.activities-table', [
                                    'items' => $user->cadeauInvitations,
                                    'type' => 'cadeau'
                                ])
                            </div>

                            <div class="tab-pane fade" id="interets" role="tabpanel">
                                @include('profile.partials.activities-table', [
                                    'items' => $user->interets,
                                    'type' => 'interet'
                                ])
                            </div>

                            <div class="tab-pane fade" id="independances" role="tabpanel">
                                @include('profile.partials.activities-table', [
                                    'items' => $user->independances,
                                    'type' => 'independance'
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-dark text-white">
                        <h5><i class="fas fa-user-cog mr-2"></i>Administration</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                               <a href="{{ route('user-profile.edit',$user->id) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Éditer mon profil
                                </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Activation des onglets
        $('#activityTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // Animation des cartes statistiques
        $('.card-statistic-2').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );

        // Initialisation des tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Gestion des erreurs spécifiques au modal
    @if($errors->has('current_password') || $errors->has('new_password'))
        $('#changePasswordModal').modal('show');
    @endif

    // Toggle afficher/masquer mot de passe
    $('.toggle-password').click(function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Vérification de la correspondance des mots de passe
    $('#new_password, #new_password_confirmation').on('keyup', function() {
        const password = $('#new_password').val();
        const confirmPassword = $('#new_password_confirmation').val();

        if (password && confirmPassword) {
            if (password === confirmPassword) {
                $('#passwordMatchText').addClass('d-none');
                $('#passwordMatchSuccess').removeClass('d-none');
            } else {
                $('#passwordMatchText').removeClass('d-none');
                $('#passwordMatchSuccess').addClass('d-none');
            }
        }

        checkFormValidity();
    });

    // Générer un mot de passe sécurisé
    $('#generatePassword').click(function() {
        const password = generateSecurePassword();
        $('#generatedPassword').val(password);
        $('#new_password').val(password);
        $('#new_password_confirmation').val(password);

        // Déclencher les vérifications
        $('#new_password').trigger('keyup');
        $('#new_password_confirmation').trigger('keyup');

        // Afficher le succès
        showToast('Mot de passe généré et copié!', 'success');
    });

    // Copier le mot de passe généré
    $('#copyPassword').click(function() {
        const generatedPassword = $('#generatedPassword').val();
        if (generatedPassword) {
            navigator.clipboard.writeText(generatedPassword).then(function() {
                showToast('Mot de passe copié!', 'success');
            });
        }
    });

    // Validation du formulaire
    function checkFormValidity() {
        const currentPassword = $('#current_password').val();
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#new_password_confirmation').val();
        const passwordValid = checkPasswordStrength(newPassword, true);

        const formValid = currentPassword && newPassword && confirmPassword &&
                          (newPassword === confirmPassword) && passwordValid;

        $('#submitPasswordBtn').prop('disabled', !formValid);
    }

    $('#current_password, #new_password, #new_password_confirmation').on('keyup', checkFormValidity);

function generateSecurePassword() {
    const length = 16;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]:;?><,./-=";
    let password = "";

    // Assurer au moins un caractère de chaque type
    password += "ABCDEFGHIJKLMNOPQRSTUVWXYZ".charAt(Math.floor(Math.random() * 26));
    password += "abcdefghijklmnopqrstuvwxyz".charAt(Math.floor(Math.random() * 26));
    password += "0123456789".charAt(Math.floor(Math.random() * 10));
    password += "!@#$%^&*()_+~`|}{[]:;?><,./-=".charAt(Math.floor(Math.random() * 28));

    // Remplir le reste
    for (let i = 4; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }

    // Mélanger le mot de passe
    return password.split('').sort(() => Math.random() - 0.5).join('');
}

function checkPasswordStrength(password, updateRules = false) {
    if (!password) {
        $('#passwordStrengthBar').css('width', '0%').removeClass().addClass('progress-bar');
        $('#passwordStrengthText').text('Force du mot de passe').removeClass().addClass('text-muted');
        if (updateRules) resetPasswordRules();
        return false;
    }

    let strength = 0;
    const rules = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };

    if (updateRules) {
        Object.keys(rules).forEach(rule => {
            const checkIcon = $(`#rule${capitalizeFirst(rule)}`).siblings('label').find('.fa-check-circle');
            const timesIcon = $(`#rule${capitalizeFirst(rule)}`).siblings('label').find('.fa-times-circle');
            const checkbox = $(`#rule${capitalizeFirst(rule)}`);

            if (rules[rule]) {
                checkIcon.removeClass('d-none');
                timesIcon.addClass('d-none');
                checkbox.prop('checked', true);
            } else {
                checkIcon.addClass('d-none');
                timesIcon.removeClass('d-none');
                checkbox.prop('checked', false);
            }
        });
    }

    // Calcul du score
    Object.values(rules).forEach(isValid => {
        if (isValid) strength += 20;
    });

    // Mettre à jour la barre de progression
    const progressBar = $('#passwordStrengthBar');
    const strengthText = $('#passwordStrengthText');

    progressBar.css('width', strength + '%');

    if (strength <= 40) {
        progressBar.removeClass().addClass('progress-bar bg-danger');
        strengthText.removeClass().addClass('text-danger').text('Faible');
    } else if (strength <= 60) {
        progressBar.removeClass().addClass('progress-bar bg-warning');
        strengthText.removeClass().addClass('text-warning').text('Moyen');
    } else if (strength <= 80) {
        progressBar.removeClass().addClass('progress-bar bg-info');
        strengthText.removeClass().addClass('text-info').text('Bon');
    } else {
        progressBar.removeClass().addClass('progress-bar bg-success');
        strengthText.removeClass().addClass('text-success').text('Excellent');
    }

    return strength >= 60; // Minimum 60% pour être acceptable
}

function resetPasswordRules() {
    $('.password-rules .fa-check-circle').addClass('d-none');
    $('.password-rules .fa-times-circle').removeClass('d-none');
    $('.password-rules input[type="checkbox"]').prop('checked', false);
}

function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function showToast(message, type = 'info') {
    // Utilisez votre système de toast existant ou créez-en un simple
    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: type === 'success' ? "#28a745" : type === 'error' ? "#dc3545" : "#17a2b8",
    }).showToast();
}

// Validation côté client supplémentaire
$('#changePasswordForm').on('submit', function(e) {
    const password = $('#new_password').val();
    const confirmPassword = $('#new_password_confirmation').val();

    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Les mots de passe ne correspondent pas!');
        return false;
    }

    if (!checkPasswordStrength(password)) {
        e.preventDefault();
        alert('Le mot de passe n\'est pas assez sécurisé!');
        return false;
    }

    // Désactiver le bouton pour éviter les doubles clics
    $('#submitPasswordBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mise à jour...');
});
</script>
@endsection

@section('styles')
<style>
    .card-statistic-2 {
        transition: transform 0.3s;
    }
    .card-statistic-2:hover {
        transform: translateY(-5px);
    }
    .tab-content {
        min-height: 300px;
    }
    .avatar-placeholder {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
</style>
@endsection
