@extends('layaout')

@section('title', 'Modifier Cadeau/Invitation')

@section('content')
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-edit"></i> Modifier le Cadeau/Invitation</h4>
                            <div class="card-header-action">
                                @can('créer des cadeaux et invitations')
                                <a href="{{ route('cadeau-invitations.index') }}" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                                @endcan
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('cadeau-invitations.update', $cadeauInvitation) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                                value="{{ old('nom', $cadeauInvitation->nom) }}" placeholder="Ex: Invitation conférence" required>
                                            @error('nom')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date <span class="text-danger">*</span></label>
                                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                                value="{{ old('date', $cadeauInvitation->date?->format('Y-m-d')) }}" required>
                                            @error('date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Cadeau/Hospitalité <span class="text-danger">*</span></label>
                                    <input type="text" name="cadeau_hospitalite" class="form-control @error('cadeau_hospitalite') is-invalid @enderror"
                                        value="{{ old('cadeau_hospitalite', $cadeauInvitation->cadeau_hospitalite) }}" placeholder="Ex: Dîner d'affaires, Cadeau promotionnel..." required>
                                    @error('cadeau_hospitalite')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Valeur (FCFA)</label>
                                            <input type="number" name="valeurs" class="form-control @error('valeurs') is-invalid @enderror"
                                                value="{{ old('valeurs', $cadeauInvitation->valeurs) }}" step="0.01" min="0" placeholder="0.00">
                                            @error('valeurs')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Action prise <span class="text-danger">*</span></label>
                                            <select name="action_prise" class="form-control select2 @error('action_prise') is-invalid @enderror" required>
                                                <option value="">Choisir une action...</option>
                                                <option value="accepté" {{ old('action_prise', $cadeauInvitation->action_prise) == 'accepté' ? 'selected' : '' }}>Accepté</option>
                                                <option value="refusé" {{ old('action_prise', $cadeauInvitation->action_prise) == 'refusé' ? 'selected' : '' }}>Refusé</option>
                                                <option value="en_attente" {{ old('action_prise', $cadeauInvitation->action_prise) == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                            </select>
                                            @error('action_prise')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                        rows="4" placeholder="Description détaillée du cadeau ou de l'invitation...">{{ old('description', $cadeauInvitation->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Responsable <span class="text-danger">*</span></label>
                                            <select name="responsable_id" class="form-control select2 @error('user_id') is-invalid @enderror" required>
                                                <option value="">Choisir un responsable...</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ old('user_id', $cadeauInvitation->responsable_id) == $user->id ? 'selected' : '' }}>
                                                        {{ $user->nom }} {{ $user->prenom }} - {{ $user->email }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('user_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Document</label>
                                            <input type="file" name="document" class="form-control @error('document') is-invalid @enderror"
                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                            <small class="text-muted">
                                                @if($cadeauInvitation->document)
                                                    <div class="text-success">
                                                        <i class="fas fa-file"></i> Document actuel :
                                                        <a href="{{ route('cadeau-invitations.download', $cadeauInvitation) }}" target="_blank" class="text-primary">
                                                            Télécharger
                                                        </a>
                                                    </div>
                                                @else
                                                    Aucun document actuellement
                                                @endif
                                                <br>PDF, Word, JPG, PNG (max 10 Mo)
                                            </small>
                                            @error('document')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations de création -->
                                <div class="card mt-4 bg-light">
                                    <div class="card-body py-3">
                                        <div class="row text-muted">
                                            <div class="col-md-6">
                                                <small>
                                                    <i class="fas fa-calendar-plus"></i> Créé le :
                                                    {{ $cadeauInvitation->created_at->format('d/m/Y à H:i') }}
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <small>
                                                    <i class="fas fa-calendar-check"></i> Modifié le :
                                                    {{ $cadeauInvitation->updated_at->format('d/m/Y à H:i') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right mt-4">
                                    @can('créer des cadeaux et invitations')
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-save"></i> Mettre à jour
                                    </button>
                                    @endcan
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

        .bg-light {
            background-color: #f8f9fa !important;
            border: 1px solid #e9ecef;
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
