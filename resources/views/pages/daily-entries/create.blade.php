@extends('layaout')

@section('title', 'Nouvelle Feuille de Temps')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-clock"></i> Nouvelle Feuille de Temps</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('daily-entries.index') }}">Feuilles de Temps</a></div>
            <div class="breadcrumb-item">Nouvelle</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Saisie du {{ now()->format('d/m/Y') }}</h4>
            </div>

            <div class="card-body">
                <form action="{{ route('daily-entries.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Collaborateur <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-control select2" required>
                                    <option value="">Choisir...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->prenom }} ({{ $user->poste->nom ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="date" name="jour" class="form-control" value="{{ old('jour', now()->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Heures théoriques <span class="text-danger">*</span></label>
                                <input type="number" step="0.25" min="0" max="24" name="heures_theoriques" class="form-control" value="{{ old('heures_theoriques', 8) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Commentaire général</label>
                        <textarea name="commentaire" class="form-control" rows="2" placeholder="Ex: Réunion clientèle, télétravail...">{{ old('commentaire') }}</textarea>
                    </div>

                    <hr>

                    <h5 class="mb-4"><i class="fas fa-tasks"></i> Activités de la journée</h5>

                    <div id="time-entries-container">
                        <!-- Ligne prototype (cachée) -->
                        <div class="time-entry-row row mb-3 d-none" id="prototype">
                            <div class="col-md-3">
                                <select name="time_entries[0][dossier_id]" class="form-control select2 dossier-select" required>
                                    <option value="">Choisir un dossier...</option>
                                    @foreach($dossiers as $dossier)
                                        <option value="{{ $dossier->id }}">{{ $dossier->nom }} - {{ $dossier->client->nom ?? 'Sans client' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="time" name="time_entries[0][heure_debut]" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <input type="time" name="time_entries[0][heure_fin]" class="form-control" required>
                            </div>
                            <div class="col-md-1">
                                <input type="number" step="0.25" min="0.25" name="time_entries[0][heures]" class="form-control heures-input" placeholder="h" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="time_entries[0][travaux]" class="form-control" placeholder="Travaux réalisés">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-row" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Première ligne visible par défaut -->
                        <div class="time-entry-row row mb-3">
                            <div class="col-md-3">
                                <select name="time_entries[0][dossier_id]" class="form-control select2 dossier-select" required>
                                    <option value="">Choisir un dossier...</option>
                                    @foreach($dossiers as $dossier)
                                        <option value="{{ $dossier->id }}">{{ $dossier->nom }} - {{ $dossier->client->nom ?? 'Sans client' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="time" name="time_entries[0][heure_debut]" class="form-control" value="09:00" required>
                            </div>
                            <div class="col-md-2">
                                <input type="time" name="time_entries[0][heure_fin]" class="form-control" value="12:00" required>
                            </div>
                            <div class="col-md-1">
                                <input type="number" step="0.25" min="0.25" name="time_entries[0][heures]" class="form-control heures-input" value="3" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="time_entries[0][travaux]" class="form-control" placeholder="Travaux réalisés">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-row" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 text-center">
                        <button type="button" id="add-row" class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Ajouter une activité
                        </button>
                    </div>

                    <!-- Récapitulatif visuel -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6>Récapitulatif des heures</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar bg-info" role="progressbar" id="progress-bar" style="width: 0%">0h / 0h</div>
                                        <div class="progress-bar bg-success" role="progressbar" id="progress-over" style="width: 0%"></div>
                                        <div class="progress-bar bg-danger" role="progressbar" id="progress-under" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <h4 id="total-heures">Total : <span class="text-primary">0.00</span>h</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <a href="{{ route('daily-entries.index') }}" class="btn btn-secondary btn-lg mr-3">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-save"></i> Enregistrer la feuille
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%',
        placeholder: "Choisir..."
    });

    let rowIndex = 1;

    $('#add-row').on('click', function() {
        let prototype = $('#prototype').clone();
        prototype.removeClass('d-none').removeAttr('id');

        // Mise à jour des names avec l'index correct
        prototype.find('input, select').each(function() {
            let name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace('[0]', '[' + rowIndex + ']'));
            }
        });

        prototype.appendTo('#time-entries-container');
        prototype.find('.select2').select2({
            width: '100%',
            placeholder: "Choisir un dossier..."
        });

        rowIndex++;
        updateTotal();
    });

    $(document).on('click', '.remove-row', function() {
        $(this).closest('.time-entry-row').remove();
        updateTotal();
    });

    $(document).on('input change', '.heures-input, input[name="heures_theoriques"]', function() {
        updateTotal();
    });

    function updateTotal() {
        let total = 0;
        $('.heures-input').each(function() {
            let val = parseFloat($(this).val()) || 0;
            total += val;
        });

        let theoriques = parseFloat($('input[name="heures_theoriques"]').val()) || 8;

        $('#total-heures span').text(total.toFixed(2));

        let percentage = (total / theoriques) * 100;

        $('#progress-bar').css('width', Math.min(percentage, 100) + '%').text(total.toFixed(2) + 'h / ' + theoriques + 'h');

        if (percentage > 100) {
            $('#progress-over').css('width', (percentage - 100) + '%');
            $('#progress-under').css('width', '0%');
        } else {
            $('#progress-over').css('width', '0%');
            $('#progress-under').css('width', (100 - percentage) + '%');
        }
    }

    // Initialisation
    updateTotal();
});
</script>
@endpush
