@extends('layaout')

@section('title', 'Nouveau Congé')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-calendar-plus"></i> Nouveau Congé</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('conges.index') }}">Congés</a></div>
            <div class="breadcrumb-item active">Nouveau Congé</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Informations du Congé</h4>
                        <div class="card-header-action">
                            <a href="{{ route('conges.index') }}" class="btn btn-icon icon-left btn-danger">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('conges.store') }}" method="POST" id="conge-form">
                            @csrf

                            <!-- @role('admin')
                            <div class="form-group">
                                <label>Utilisateur <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-control select2 @error('user_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner un utilisateur</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->prenom }} {{ $user->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endrole -->

                            <div class="form-group">
                                <label>Type de congé <span class="text-danger">*</span></label>
                                <select name="type_conge" class="form-control select2 @error('type_conge') is-invalid @enderror" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="MALADIE" {{ old('type_conge') == 'MALADIE' ? 'selected' : '' }}>Maladie</option>
                                    <option value="MATERNITE" {{ old('type_conge') == 'MATERNITE' ? 'selected' : '' }}>Maternité</option>
                                    <option value="REMUNERE" {{ old('type_conge') == 'REMUNERE' ? 'selected' : '' }}>Rémunéré</option>
                                    <option value="NON REMUNERE" {{ old('type_conge') == 'NON REMUNERE' ? 'selected' : '' }}>Non rémunéré</option>
                                </select>
                                @error('type_conge')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de début <span class="text-danger">*</span></label>
                                        <input type="date" name="date_debut" class="form-control @error('date_debut') is-invalid @enderror"
                                               value="{{ old('date_debut') }}" required>
                                        @error('date_debut')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de fin <span class="text-danger">*</span></label>
                                        <input type="date" name="date_fin" class="form-control @error('date_fin') is-invalid @enderror"
                                               value="{{ old('date_fin') }}" required>
                                        @error('date_fin')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Enregistrer le Congé
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
<style>
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #e3e6f0;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Initialiser Select2
    $('.select2').select2({
        placeholder: "Sélectionner...",
        allowClear: true
    });

    // Validation du formulaire
    $('#conge-form').on('submit', function(e) {
        var type = $('select[name="type_conge"]').val();
        var dateDebut = $('input[name="date_debut"]').val();
        var dateFin = $('input[name="date_fin"]').val();

        if (!type || !dateDebut || !dateFin) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Champs obligatoires',
                text: 'Veuillez remplir tous les champs obligatoires (*)',
            });
            return false;
        }
    });
});
</script>
@endpush
