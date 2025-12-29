@extends('layaout')

@section('title', 'Cadeaux & Invitations')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-gift"></i> Gestion des Cadeaux & Invitations</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Cadeaux & Invitations</div>
            </div>
        </div>

        <div class="section-body">
            <!-- Onglets avec compteurs -->
            <div class="card mb-4">
                <div class="card-body py-3">
                    <ul class="nav nav-pills" id="action-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-action="" href="#">
                                Tous <span class="badge badge-white ml-1" id="total-count">{{ $cadeaux->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-action="accepté" href="#">
                                Acceptés <span class="badge badge-success ml-1"
                                    id="accepte-count">{{ $cadeaux->where('action_prise', 'accepté')->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-action="refusé" href="#">
                                Refusés <span class="badge badge-danger ml-1"
                                    id="refuse-count">{{ $cadeaux->where('action_prise', 'refusé')->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-action="en_attente" href="#">
                                En attente <span class="badge badge-warning ml-1"
                                    id="attente-count">{{ $cadeaux->where('action_prise', 'en_attente')->count() }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Liste des Cadeaux & Invitations</h4>

                    {{-- CORRECTION ICI : Une seule ouverture @can --}}
                    <div class="card-header-action">
                        @can('créer des cadeaux et invitations')
                            <a href="{{ route('cadeau-invitations.create') }}" class="btn btn-primary btn-icon icon-left mr-2">
                                <i class="fas fa-plus"></i> Nouveau
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-lg-4">
                            <label>Recherche</label>
                            <input type="text" id="search-input" class="form-control"
                                placeholder="Nom, cadeau, description...">
                        </div>
                        <div class="col-lg-3">
                            <label>Action prise</label>
                            <select id="action-filter" class="form-control select2">
                                <option value="">Toutes les actions</option>
                                <option value="accepté">Accepté</option>
                                <option value="refusé">Refusé</option>
                                <option value="en_attente">En attente</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label>Date</label>
                            <input type="date" id="date-filter" class="form-control">
                        </div>
                        <div class="col-lg-2 text-right" style="padding-top: 30px;">
                            <button type="button" id="reset-filters" class="btn btn-outline-secondary btn-icon icon-left">
                                <i class="fas fa-redo"></i> Réinitialiser
                            </button>
                        </div>
                    </div>

                    <!-- Tableau avec largeur fixe et scroll -->
                    <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                        <table class="table table-striped table-hover" id="cadeaux-table"
                            style="width: 100%; min-width: 1100px;">
                            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th width="150">Nom</th>
                                    <th width="100">Date</th>
                                    <th width="150">Cadeau/Hospitalité</th>
                                    <th width="120">Valeur</th>
                                    <th width="120">Action</th>
                                    <th width="200">Description</th>
                                    <th width="150">Responsable</th>
                                    <th width="180" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cadeaux as $cadeau)
                                    <tr>
                                        <td>
                                            <strong class="text-dark">{{ $cadeau->nom }}</strong>
                                            @if($cadeau->document)
                                                <br><small class="text-primary"><i class="fas fa-file"></i> Document joint</small>
                                            @endif
                                        </td>
                                        <td>{{ $cadeau->date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ Str::limit($cadeau->cadeau_hospitalite, 30) }}</td>
                                        <td>
                                            <strong class="text-success">{{ $cadeau->valeurs_formatted }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $cadeau->action_prise_color }}">
                                                {{ $cadeau->action_prise_formatted }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($cadeau->description, 50) ?: '-' }}</td>
                                        <td>
                                            <small>{{ $cadeau->responsable->nom ?? 'Inconnu' }}
                                                {{ $cadeau->responsable->prenom ?? 'Inconnu' }}</small><br>
                                            <small class="text-muted">{{ $cadeau->responsable->email ?? '-' }}</small>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="btn-group btn-group-sm shadow-sm" role="group">

                                                {{-- Bouton VOIR --}}
                                                @can('voir les cadeaux et invitations')
                                                    <a href="{{ route('cadeau-invitations.show', $cadeau) }}"
                                                        class="btn btn-info btn-sm" title="Voir" data-toggle="tooltip"
                                                        style="padding: 6px 8px; margin: 1px;">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan

                                                {{-- Bouton MODIFIER --}}
                                                @can('modifier des cadeaux et invitations')
                                                    <a href="{{ route('cadeau-invitations.edit', $cadeau) }}"
                                                        class="btn btn-primary btn-sm" title="Modifier" data-toggle="tooltip"
                                                        style="padding: 6px 8px; margin: 1px;">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                {{-- Boutons PDF et DOC (Requiert permission de voir) --}}
                                                @can('voir les cadeaux et invitations')
                                                    {{-- PDF --}}
                                                    <a href="{{ route('cadeau-invitations.pdf', ['cadeauInvitation' => $cadeau->id]) }}"
                                                        class="btn btn-success btn-sm" title="Télécharger le PDF" target="_blank"
                                                        data-toggle="tooltip" style="padding: 6px 8px; margin: 1px;">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>

                                                    {{-- Document Joint --}}
                                                    @if($cadeau->document)
                                                        <a href="{{ route('cadeau-invitations.download', $cadeau) }}"
                                                            class="btn btn-success btn-sm" title="Télécharger la pièce jointe"
                                                            data-toggle="tooltip" style="padding: 6px 8px; margin: 1px;">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endif
                                                @endcan

                                                {{-- Bouton SUPPRIMER --}}
                                                @can('supprimer des cadeaux et invitations')
                                                    <form action="{{ route('cadeau-invitations.destroy', $cadeau) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm confirm-delete"
                                                            title="Supprimer" data-toggle="tooltip"
                                                            style="padding: 6px 8px; margin: 1px;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
    <style>
        .card-header-action .btn {
            margin-left: 8px;
        }

        #action-tabs .nav-link {
            font-weight: 600;
        }

        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .badge-white {
            background: white;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        /* Styles pour le tableau scrollable */
        .table-responsive {
            border: 1px solid #e3e6f0;
            border-radius: 8px;
        }

        /* En-tête fixe */
        .table thead th {
            background: #343a40;
            color: white;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Amélioration des boutons */
        .btn-group .btn-sm {
            border-radius: 4px !important;
            transition: all 0.2s ease;
        }

        .btn-group .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>

    <script>
    $(document).ready(function () {
        // Initialisation Select2
        $('.select2').select2({
            placeholder: "Sélectionner...",
            allowClear: true,
            width: '100%'
        });

        var table = $('#cadeaux-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
                search: "Rechercher :",
                lengthMenu: "Afficher _MENU_ éléments",
                info: "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
                paginate: {
                    first: "Premier",
                    last: "Dernier",
                    next: "Suivant",
                    previous: "Précédent"
                }
            },
            responsive: false,
            pageLength: 25,
            order: [[1, 'desc']],
            dom: '<"top"lf>rt<"bottom"ip><"clear">',
            scrollX: true,
            scrollY: false,
            columnDefs: [
                {
                    orderable: false,
                    targets: [7]
                },
                {
                    width: "150px",
                    targets: 0
                },
                {
                    width: "100px",
                    targets: 1
                },
                {
                    width: "150px",
                    targets: 2
                },
                {
                    width: "120px",
                    targets: [3, 4]
                },
                {
                    width: "200px",
                    targets: 5
                },
                {
                    width: "150px",
                    targets: 6
                },
                {
                    width: "180px",
                    targets: 7
                },
                // NOUVEAU : Ajouter un data-attribute pour faciliter le filtrage
                {
                    targets: 4,
                    render: function(data, type, row) {
                        if (type === 'filter' || type === 'sort') {
                            // Retourne seulement le texte du statut pour le filtrage
                            var match = data.match(/<span[^>]*>([^<]*)<\/span>/);
                            return match ? match[1].trim() : data;
                        }
                        return data;
                    }
                }
            ]
        });

        // Filtres
        $('#search-input').on('keyup', function () {
            table.search(this.value).draw();
            updateCounts();
        });

        $('#action-filter').on('change', function () {
            var val = $(this).val();
            if (val) {
                // Recherche exacte dans la colonne 4
                table.column(4).search(val, true, false).draw();
            } else {
                table.column(4).search('').draw();
            }
            updateCounts();
        });

        $('#date-filter').on('change', function () {
            var val = $(this).val();
            if (val) {
                // Formate la date pour la recherche
                var formattedDate = val.split('-').reverse().join('/');
                table.column(1).search(formattedDate).draw();
            } else {
                table.column(1).search('').draw();
            }
            updateCounts();
        });

        // Filtre par onglet
        $('#action-tabs .nav-link').on('click', function (e) {
            e.preventDefault();
            $('#action-tabs .nav-link').removeClass('active');
            $(this).addClass('active');
            var action = $(this).data('action');
            $('#action-filter').val(action).trigger('change');
        });

        // Réinitialiser
        $('#reset-filters').on('click', function () {
            $('#search-input, #date-filter').val('');
            $('#action-filter').val('').trigger('change');
            $('#action-tabs .nav-link').removeClass('active');
            $('#action-tabs .nav-link[data-action=""]').addClass('active');
            table.search('').columns().search('').draw();
            updateCounts();
        });

        // Mise à jour des compteurs
        function updateCounts() {
            var data = table.rows({ search: 'applied' }).data();
            var total = table.rows({ search: 'applied' }).count();
            var accepte = 0;
            var refuse = 0;
            var attente = 0;

            // Parcourir les données filtrées
            $.each(data, function(index, row) {
                var cellText = row[4]; // Colonne d'action

                // Utiliser une logique plus robuste pour détecter le statut
                if (cellText.includes('badge-success')) {
                    accepte++;
                } else if (cellText.includes('badge-danger')) {
                    refuse++;
                } else if (cellText.includes('badge-warning')) {
                    attente++;
                }
            });

            $('#total-count').text(total);
            $('#accepte-count').text(accepte);
            $('#refuse-count').text(refuse);
            $('#attente-count').text(attente);
        }

        // Confirmation suppression
        $(document).on('click', '.confirm-delete', function (e) {
            e.preventDefault();
            const form = $(this).closest('form');

            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Le cadeau sera supprimé définitivement !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Initialisation des compteurs
        updateCounts();
    });
</script>
@endpush
