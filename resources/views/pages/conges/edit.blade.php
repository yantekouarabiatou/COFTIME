@extends('layaout')

@section('title', 'Modifier un Congé')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-calendar-edit"></i> Modifier le Congé</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('conges.index') }}">Congés</a></div>
            <div class="breadcrumb-item active">Modification</div>
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
                        <form action="{{ route('conges.update', $conge) }}" method="POST" id="conge-form">
                            @csrf
                            @method('PUT')

                            {{-- Type de congé --}}
                            <div class="form-group">
                                <label>Type de congé <span class="text-danger">*</span></label>
                                <select name="type_conge"
                                        class="form-control select2 @error('type_conge') is-invalid @enderror"
                                        required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="MALADIE"
                                        {{ old('type_conge', $conge->type_conge) == 'MALADIE' ? 'selected' : '' }}>
                                        Maladie
                                    </option>
                                    <option value="MATERNITE"
                                        {{ old('type_conge', $conge->type_conge) == 'MATERNITE' ? 'selected' : '' }}>
                                        Maternité
                                    </option>
                                    <option value="REMUNERE"
                                        {{ old('type_conge', $conge->type_conge) == 'REMUNERE' ? 'selected' : '' }}>
                                        Rémunéré
                                    </option>
                                    <option value="NON REMUNERE"
                                        {{ old('type_conge', $conge->type_conge) == 'NON REMUNERE' ? 'selected' : '' }}>
                                        Non rémunéré
                                    </option>
                                </select>
                                @error('type_conge')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Dates --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de début <span class="text-danger">*</span></label>
                                        <input type="date"
                                               name="date_debut"
                                               class="form-control @error('date_debut') is-invalid @enderror"
                                               value="{{ old('date_debut', $conge->date_debut->format('Y-m-d')) }}"
                                               required>
                                        @error('date_debut')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de fin <span class="text-danger">*</span></label>
                                        <input type="date"
                                               name="date_fin"
                                               class="form-control @error('date_fin') is-invalid @enderror"
                                               value="{{ old('date_fin', $conge->date_fin->format('Y-m-d')) }}"
                                               required>
                                        @error('date_fin')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Bouton --}}
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Mettre à jour le Congé
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
