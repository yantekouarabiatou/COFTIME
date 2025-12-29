@extends('layaout')

@section('title', 'Nouveau Client Audit')

@section('content')
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            @can('créer des clients audit')
                            <h4><i class="fas fa-plus-circle"></i> Nouveau Client Audit</h4>
                            <div class="card-header-action">
                                <a href="{{ route('clients-audit.index') }}" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Annuler
                                </a>@endcan
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('clients-audit.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nom du Client <span class="text-danger">*</span></label>
                                            <input type="text" name="nom_client" class="form-control @error('nom_client') is-invalid @enderror"
                                                value="{{ old('nom_client') }}" placeholder="Ex: Entreprise ABC" required>
                                            @error('nom_client')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Siège Social</label>
                                            <input type="text" name="siege_social" class="form-control @error('siege_social') is-invalid @enderror"
                                                value="{{ old('siege_social') }}" placeholder="Ex: Paris, France">
                                            @error('siege_social')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Adresse</label>
                                    <textarea name="adresse" class="form-control @error('adresse') is-invalid @enderror"
                                        rows="3" placeholder="Adresse complète du client">{{ old('adresse') }}</textarea>
                                    @error('adresse')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Frais d'Audit (FCFA)</label>
                                            <input type="number" name="frais_audit" class="form-control @error('frais_audit') is-invalid @enderror"
                                                value="{{ old('frais_audit') }}" step="0.01" min="0" placeholder="0.00">
                                            @error('frais_audit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Autres Frais (FCFA)</label>
                                            <input type="number" name="frais_autres" class="form-control @error('frais_autres') is-invalid @enderror"
                                                value="{{ old('frais_autres') }}" step="0.01" min="0" placeholder="0.00">
                                            @error('frais_autres')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Document (facultatif)</label>
                                            <input type="file" name="document" class="form-control @error('document') is-invalid @enderror"
                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                            <small class="text-muted">PDF, Word, JPG, PNG (max 10 Mo)</small>
                                            @error('document')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-save"></i> Enregistrer le Client
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

@section('styles')
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            padding: 6px 12px;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialisation Select2
            $('.select2').select2({
                placeholder: "Choisir...",
                allowClear: true,
                width: '100%'
            });

            // Formatage automatique des nombres
            $('input[type="number"]').on('blur', function() {
                if (this.value) {
                    this.value = parseFloat(this.value).toFixed(2);
                }
            });
        });
    </script>
@endsection
