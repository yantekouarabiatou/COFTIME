@extends('layaout')

@section('title', 'Modifier la Feuille de Temps')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-clock"></i> Modifier la Feuille de Temps</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('daily-entries.index') }}">Feuilles de Temps</a></div>
            <div class="breadcrumb-item"><a href="{{ route('daily-entries.show', $dailyEntry) }}">Détail</a></div>
            <div class="breadcrumb-item">Modifier</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Modification du {{ \Carbon\Carbon::parse($dailyEntry->jour)->format('d/m/Y') }}</h4>
            </div>

            <div class="card-body">
                <form action="{{ route('daily-entries.update', $dailyEntry) }}" method="POST" id="daily-form">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Collaborateur</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ $dailyEntry->user->prenom }} - {{ $dailyEntry->user->nom }} ({{ $dailyEntry->user->poste->intitule ?? '-' }})" readonly>
                                    <input type="hidden" name="user_id" value="{{ $dailyEntry->user_id }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="date" name="jour" class="form-control" value="{{ $dailyEntry->jour->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Heures théoriques <span class="text-danger">*</span></label>
                                <input type="number" step="0.25" min="0" max="24" name="heures_theoriques"
                                       class="form-control" value="{{ $dailyEntry->heures_theoriques }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Commentaire général</label>
                        <textarea name="commentaire" class="form-control" rows="2">{{ $dailyEntry->commentaire }}</textarea>
                    </div>

                    <hr>

                    <!-- Titre + bouton global nouveau dossier -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="mb-0"><i class="fas fa-tasks"></i> Activités de la journée</h5>
                        <button type="button" class="btn btn-outline-primary btn-new-dossier-global">
                            <i class="fas fa-plus"></i> Nouveau dossier
                        </button>
                    </div>

                    <div id="time-entries-container">
                        @foreach($dailyEntry->timeEntries as $index => $entry)
                        <div class="time-entry-row row mb-3">
                            <div class="col-md-3">
                                <select name="time_entries[{{ $index }}][dossier_id]" class="form-control select2 dossier-select" required>
                                    <option value="">Choisir un dossier...</option>
                                    @foreach($dossiers as $dossier)
                                        <option value="{{ $dossier->id }}"
                                            data-client="{{ $dossier->client->nom ?? 'Sans client' }}"
                                            data-reference="{{ $dossier->reference ?? '' }}"
                                            {{ $entry->dossier_id == $dossier->id ? 'selected' : '' }}>
                                            {{ $dossier->nom }} - {{ $dossier->client->nom ?? 'Sans client' }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="time_entries[{{ $index }}][id]" value="{{ $entry->id }}">
                            </div>
                            <div class="col-md-2">
                            <input type="time" name="time_entries[{{ $index }}][heure_debut]"
                                class="form-control heure-debut"
                                value="{{ $entry->heure_debut ? $entry->heure_debut->format('H:i') : '' }}"
                                required>
                        </div>
                            <div class="col-md-2">
                                <input type="time" name="time_entries[{{ $index }}][heure_fin]"
                                    class="form-control heure-fin"
                                    value="{{ $entry->heure_fin ? $entry->heure_fin->format('H:i') : '' }}"
                                    required>
                            </div>
                            <div class="col-md-1">
                                <input type="number" step="0.25" min="0.25" name="time_entries[{{ $index }}][heures_reelles]"
                                       class="form-control heures-input" value="{{ $entry->heures_reelles }}" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="time_entries[{{ $index }}][travaux]"
                                       class="form-control travaux-input" value="{{ $entry->travaux }}" placeholder="Travaux réalisés">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-row" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mb-4 text-center">
                        <button type="button" id="add-row" class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Ajouter une activité
                        </button>
                    </div>

                    <!-- Récapitulatif -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6>Récapitulatif des heures</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar bg-info" role="progressbar" id="progress-bar" style="width: 0%">
                                            <span class="progress-text">0h / 0h</span>
                                        </div>
                                        <div class="progress-bar bg-success" role="progressbar" id="progress-over" style="width: 0%"></div>
                                        <div class="progress-bar bg-danger" role="progressbar" id="progress-under" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <h4 id="total-heures">Total : <span class="text-info">0.00</span>h</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <a href="{{ route('daily-entries.show', $dailyEntry) }}" class="btn btn-secondary btn-lg mr-3">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Modal nouveau dossier (identique à create) -->
<div class="modal fade" id="newDossierModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-folder-plus"></i> Nouveau Dossier</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="new-dossier-form">
                    @csrf
                    <!-- Tous les champs identiques à ceux de la vue create -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom du dossier <span class="text-danger">*</span></label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Référence</label>
                                <input type="text" name="reference" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-control select2" required>
                                    <option value="">Choisir un client...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type de dossier</label>
                                <select name="type_dossier" class="form-control">
                                    <option value="">Sélectionner...</option>
                                    <option value="audit">Audit</option>
                                    <option value="conseil">Conseil</option>
                                    <option value="formation">Formation</option>
                                    <option value="expertise">Expertise</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date d'ouverture</label>
                                <input type="date" name="date_ouverture" class="form-control" value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date de clôture prévue</label>
                                <input type="date" name="date_cloture_prevue" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Budget (€)</label>
                                <input type="number" step="0.01" name="budget" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Frais de dossier (€)</label>
                                <input type="number" step="0.01" name="frais_dossier" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                           <div class="form-group">
                        <label>Statut <span class="text-danger">*</span></label>
                        <select name="statut" class="form-control" required>
                            <option value="soumis" {{ $dailyEntry->statut == 'soumis' ? 'selected' : '' }}>Soumis</option>
                            <option value="validé" {{ $dailyEntry->statut == 'validé' ? 'selected' : '' }}>Validé</option>
                            <option value="refusé" {{ $dailyEntry->statut == 'refusé' ? 'selected' : '' }}>Refusé</option>
                        </select>
                            </div>
                        @if($dailyEntry->statut)
                            <input type="hidden" name="statut" value="{{ $dailyEntry->statut }}">
                            <div class="form-group">
                                <label>Statut</label>
                                <input type="text" class="form-control" value="{{ ucfirst($dailyEntry->statut) }}" readonly>
                            </div>
                        @endif
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Document (fichier)</label>
                                <input type="file" name="document" class="form-control">
                                <small class="text-muted">PDF, Word, Excel, etc.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Description détaillée du dossier..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Notes internes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Notes visibles uniquement en interne..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="save-new-dossier">
                    <i class="fas fa-save"></i> Créer le dossier
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
<style>
    .progress-text { position: absolute; width: 100%; text-align: center; font-weight: bold; color: #fff; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); }
    .select2-container { width: 100% !important; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%',
        placeholder: "Choisir un dossier..."
    });

    let rowIndex = {{ $dailyEntry->timeEntries->count() }};
    const dossierOptionsHTML = $('#time-entries-container .dossier-select:first').html() || '<option value="">Choisir un dossier...</option>';

    $('.btn-new-dossier-global').on('click', function() {
        $('#newDossierModal').modal('show');
    });

    $('#save-new-dossier').on('click', function() {
        let form = $('#new-dossier-form');
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Création...');

        let formData = new FormData(form[0]);

        $.ajax({
            url: '{{ route("dossiers.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                let newOption = `<option value="${response.dossier.id}" selected>
                    ${response.dossier.nom} - ${response.client.nom}
                </option>`;
                $('.dossier-select').append(newOption).trigger('change');
                $('#newDossierModal').modal('hide');
                form[0].reset();
                Swal.fire('Succès', 'Dossier créé !', 'success');
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Erreur';
                Swal.fire('Erreur', msg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Créer le dossier');
            }
        });
    });

    $('#add-row').on('click', function() {
        let newRow = `
        <div class="time-entry-row row mb-3">
            <div class="col-md-3">
                <select name="time_entries[${rowIndex}][dossier_id]" class="form-control select2 dossier-select" required>
                    ${dossierOptionsHTML}
                </select>
            </div>
            <div class="col-md-2"><input type="time" name="time_entries[${rowIndex}][heure_debut]" class="form-control heure-debut" required></div>
            <div class="col-md-2"><input type="time" name="time_entries[${rowIndex}][heure_fin]" class="form-control heure-fin" required></div>
            <div class="col-md-1"><input type="number" step="0.25" min="0.25" name="time_entries[${rowIndex}][heures_reelles]" class="form-control heures-input" required></div>
            <div class="col-md-3"><input type="text" name="time_entries[${rowIndex}][travaux]" class="form-control travaux-input" placeholder="Travaux réalisés"></div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
            </div>
        </div>`;
        $('#time-entries-container').append(newRow);
        $('.dossier-select').last().select2({ width: '100%', placeholder: "Choisir un dossier..." });
        rowIndex++;
        updateTotal();
    });

    $(document).on('click', '.remove-row', function() {
        if ($('.time-entry-row').length > 1) {
            $(this).closest('.time-entry-row').fadeOut(300, function() { $(this).remove(); updateTotal(); });
        } else {
            Swal.fire('Attention', 'Au moins une activité requise', 'warning');
        }
    });

    $(document).on('change', '.heure-debut, .heure-fin', function() {
        let row = $(this).closest('.time-entry-row');
        let start = row.find('.heure-debut').val();
        let end = row.find('.heure-fin').val();
        if (start && end) {
            let s = new Date('1970-01-01T' + start + ':00');
            let e = new Date('1970-01-01T' + end + ':00');
            if (e < s) e.setDate(e.getDate() + 1);
            let diff = (e - s) / (1000 * 60 * 60);
            diff = Math.round(diff * 4) / 4;
            row.find('.heures-input').val(diff.toFixed(2));
            updateTotal();
        }
    });

    function updateTotal() {
        let total = 0;
        $('.heures-input').each(function() { total += parseFloat($(this).val()) || 0; });
        let theoriques = parseFloat($('input[name="heures_theoriques"]').val()) || 8;
        $('#total-heures span').text(total.toFixed(2));
        let perc = (total / theoriques) * 100;
        $('#progress-bar').css('width', Math.min(perc, 100) + '%');
        $('#progress-bar .progress-text').text(total.toFixed(2) + 'h / ' + theoriques + 'h');
        $('#progress-over').css('width', perc > 100 ? (perc - 100) + '%' : '0%');
        $('#progress-under').css('width', perc <= 100 ? (100 - perc) + '%' : '0%');
    }

    $('#daily-form').on('submit', function(e) {
        let total = 0;
        $('.heures-input').each(function() { total += parseFloat($(this).val()) || 0; });
        if (total === 0) {
            e.preventDefault();
            Swal.fire('Erreur', 'Au moins une activité requise', 'error');
        }
        if ($('.dossier-select').filter(function() { return !$(this).val(); }).length > 0) {
            e.preventDefault();
            Swal.fire('Erreur', 'Sélectionnez un dossier pour chaque activité', 'error');
        }
    });

    updateTotal();
});
</script>
@endpush
