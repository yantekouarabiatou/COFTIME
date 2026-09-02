@extends('layaout')

@section('title', 'Liste de Temps')

@section('content')
<section class="section">

    <div class="section-header">
        <h1><i class="fas fa-list-alt"></i> Liste de Temps</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">Liste de Temps</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-filter mr-1 text-primary"></i> Sélection</h4>
                    </div>

                    <div class="card-body">
                        <form method="GET" action="{{ route('daily-entries.liste') }}" id="filterForm">
                            <div class="row align-items-end g-2">

                                <div class="col-md-4">
                                    <label class="form-label small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-user mr-1"></i>Collaborateur
                                    </label>
                                    <select name="user_id" id="userSelect" class="form-control">
                                        <option value="">- Choisir un collaborateur -</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}"
                                                {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                                {{ $u->prenom }} {{ $u->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-calendar mr-1"></i>Du
                                    </label>
                                    <input type="date" name="date_debut" class="form-control"
                                           value="{{ request('date_debut', now()->startOfMonth()->format('Y-m-d')) }}"
                                           max="{{ now()->format('Y-m-d') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-calendar mr-1"></i>Au
                                    </label>
                                    <input type="date" name="date_fin" class="form-control"
                                           value="{{ request('date_fin', now()->format('Y-m-d')) }}"
                                           max="{{ now()->format('Y-m-d') }}">
                                </div>

                                <div class="col-md-2 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Afficher
                                    </button>
                                </div>
                            </div>
                        </form>

                        @can('exporter les feuilles de temps')
                            @if($selectedUser)
                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <a href="{{ route('daily-entries.export-user-period', array_merge(['user' => $selectedUser->id], request()->only(['date_debut','date_fin']), ['format' => 'excel'])) }}"
                                       class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-file-excel mr-1"></i> Exporter Excel
                                    </a>
                                    <a href="{{ route('daily-entries.export-user-period', array_merge(['user' => $selectedUser->id], request()->only(['date_debut','date_fin']), ['format' => 'pdf'])) }}"
                                       class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-file-pdf mr-1"></i> Exporter PDF
                                    </a>
                                </div>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                @if($selectedUser)
                    @include('pages.rapports.partials.single-user', [
                        'entries' => $entries,
                        'user'    => $selectedUser,
                    ])
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Choisissez un collaborateur et une période</h5>
                            <p class="text-muted mb-0">
                                Sélectionnez un collaborateur et une période ci-dessus, puis cliquez sur
                                <strong>Afficher</strong> pour consulter et exporter ses feuilles de temps.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</section>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#userSelect').select2({
            placeholder: '- Choisir un collaborateur -',
            allowClear: true,
            width: '100%',
            language: { noResults: () => 'Aucun résultat', searching: () => 'Recherche…' }
        });
    });
</script>
@endpush
