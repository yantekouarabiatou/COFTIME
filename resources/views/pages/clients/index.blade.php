@extends('layaout')

@section('title', 'Clients Audit')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-building"></i> Gestion des Clients Audit</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Clients Audit</div>
            </div>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4>Liste des Clients Audit</h4>
                    <div class="card-header-action">
                        @can('créer des clients audit')
                            <a href="{{ route('clients-audit.create') }}" class="btn btn-primary btn-icon icon-left mr-2">
                                <i class="fas fa-plus"></i> Nouveau Client
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <label>Recherche</label>
                            <input type="text" id="search-input" class="form-control"
                                placeholder="Nom, adresse, siège social...">
                        </div>

                        <div class="col-lg-2 text-right" style="padding-top: 30px;">
                            <button type="button" id="reset-filters" class="btn btn-outline-secondary btn-icon icon-left">
                                <i class="fas fa-redo"></i> Réinitialiser
                            </button>
                        </div>
                    </div>

                    <!-- Tableau avec largeur fixe et scroll -->
                    <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                        <table class="table table-striped table-hover" id="clients-table"
                            style="width: 100%; min-width: 1200px;">
                            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th width="200">Nom du Client</th>
                                    <th width="200">Adresse</th>
                                    <th width="150">Siège Social</th>
                                    <th width="120">Frais Audit</th>
                                    <th width="120">Autres Frais</th>
                                    <th width="120">Total</th>
                                    <th width="180" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clients as $client)
                                    <tr>
                                        <td>
                                            <strong class="text-dark">{{ $client->nom_client }}</strong>
                                            @if($client->document)
                                                <br><small class="text-primary"><i class="fas fa-file"></i> Document joint</small>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($client->adresse, 30) ?: '-' }}</td>
                                        <td>{{ $client->siege_social ?: '-' }}</td>
                                        <td>{{ $client->frais_audit ? number_format($client->frais_audit, 2, ',', ' ') . ' FCFA' : '-' }}
                                        </td>
                                        <td>{{ $client->frais_autres ? number_format($client->frais_autres, 2, ',', ' ') . ' FCFA' : '-' }}
                                        </td>
                                        <td>
                                            <strong class="text-dark">{{ number_format($client->total_frais, 2, ',', ' ') }}
                                                FCFA</strong>
                                        </td>


                                        <td class="text-center align-middle">
                                            <div class="btn-group btn-group-sm shadow-sm" role="group">

                                                {{-- Bouton VOIR --}}
                                                @can('voir les clients audit')
                                                    <a href="{{ route('clients-audit.show', $client) }}" class="btn btn-info btn-sm"
                                                        title="Voir" data-toggle="tooltip" style="padding: 6px 8px; margin: 1px;">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan

                                                {{-- Bouton MODIFIER --}}
                                                @can('modifier des clients audit')
                                                    <a href="{{ route('clients-audit.edit', $client) }}"
                                                        class="btn btn-primary btn-sm" title="Modifier" data-toggle="tooltip"
                                                        style="padding: 6px 8px; margin: 1px;">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                {{-- Bouton PDF (Nécessite permission de voir) --}}
                                                @can('voir les clients audit')
                                                    <a href="{{ route('clients-audits.pdf', ['clientAudit' => $client->id]) }}"
                                                        class="btn btn-success btn-sm" title="Télécharger la fiche PDF"
                                                        target="_blank" data-toggle="tooltip"
                                                        style="padding: 6px 8px; margin: 1px;">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>

                                                    {{-- Bouton TÉLÉCHARGER DOCUMENT (Si existe) --}}
                                                    @if($client->document)
                                                        <a href="{{ route('clients-audit.download', $client) }}"
                                                            class="btn btn-success btn-sm" title="Télécharger la pièce jointe"
                                                            data-toggle="tooltip" style="padding: 6px 8px; margin: 1px;">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endif
                                                @endcan

                                                {{-- Bouton SUPPRIMER --}}
                                                @can('supprimer des clients audit')
                                                    <form action="{{ route('clients-audit.destroy', $client) }}" method="POST"
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

        #statut-tabs .nav-link {
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

        /* Style pour DataTables */
        .dataTables_wrapper {
            position: relative;
        }

        .dataTables_length,
        .dataTables_filter {
            margin-bottom: 15px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .table-responsive {
                max-height: 400px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "Sélectionner...",
                allowClear: true,
                width: '100%'
            });

            var table = $('#clients-table').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
                pageLength: 25,
                order: [[0, 'asc']],
                responsive: false,
                scrollX: true,
                dom: '<"top"lf>rt<"bottom"ip><"clear">',
                searching: false, // on désactive la recherche native
                columnDefs: [
                    { orderable: false, targets: [6] },
                    { searchable: false, targets: [4, 5, 6] } // on désactive la recherche sur statut, user, actions
                ]
            });

            // FILTRE PERSONNALISÉ (comme dans les plaintes, mais adapté)
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable !== document.getElementById('clients-table')) return true;

                var row = settings.aoData[dataIndex].nTr;
                var rowStatut = row.getAttribute('data-statut') || '';
                var rowUserId = row.getAttribute('data-user-id') || '';

                var searchText = $('#search-input').val().toLowerCase().trim();
                var statutFilter = $('#statut-filter').val();
                var userFilter = $('#user-filter').val();

                // Recherche globale
                if (searchText) {
                    var rowText = (row.textContent || row.innerText || '').toLowerCase();
                    if (!rowText.includes(searchText)) return false;
                }

                // Filtre statut
                if (statutFilter && rowStatut !== statutFilter) return false;

                // Filtre utilisateur
                if (userFilter && rowUserId !== userFilter) return false;

                return true;
            });

            // Appliquer les filtres
            function applyFilters() {
                table.draw();
                updateCounts();
            }

            $('#search-input').on('keyup', applyFilters);
            $('#statut-filter, #user-filter').on('change', applyFilters);

            // Onglets
            $('#statut-tabs .nav-link').on('click', function (e) {
                e.preventDefault();
                $('#statut-tabs .nav-link').removeClass('active');
                $(this).addClass('active');
                var statut = $(this).data('statut') || '';
                $('#statut-filter').val(statut).trigger('change');
                applyFilters();
            });

            // Réinitialiser
            $('#reset-filters').on('click', function () {
                $('#search-input').val('');
                $('#statut-filter, #user-filter').val('').trigger('change');
                applyFilters();
            });

            // Mise à jour des compteurs (comme dans les plaintes)
            function updateCounts() {
                var visible = table.rows({ search: 'applied' }).count();
                var data = table.rows({ search: 'applied' }).data();

                var actif = 0, enCours = 0, inactif = 0;
                data.each(function (row) {
                    var tr = row[0].closest('tr');
                    var statut = tr.getAttribute('data-statut');
                    if (statut === 'actif') actif++;
                    else if (statut === 'en_cours') enCours++;
                    else if (statut === 'inactif') inactif++;
                });

                $('#total-count').text(visible);
                $('#actif-count').text(actif);
                $('#en-cours-count').text(enCours);
                $('#inactif-count').text(inactif);
            }

            // Suppression avec confirmation
            $(document).on('click', '.confirm-delete', function (e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Ce client audit sera supprimé définitivement !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });

            // Initialiser
            updateCounts();
        });
    </script>
@endpush
