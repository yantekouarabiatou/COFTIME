<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">{{ $user->full_name }} - Total : {{ $entries->sum('heures_totales') }}h (Théorique : {{ $entries->sum('heures_theoriques') }}h)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Théorique</th>
                        <th>Réel</th>
                        <th>Écart</th>
                        <th>Dossier / Activité</th>
                        <th>Heures</th>
                        <th>Travaux</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                        @foreach($entry->timeEntries as $te)
                            <tr>
                                <td><strong>{{ $entry->jour->format('d/m/Y') }}</strong></td>
                                <td>{{ $entry->heures_theoriques }}h</td>
                                <td>{{ $entry->heures_totales }}h</td>
                                <td>
                                    @php $ecart = $entry->heures_totales - $entry->heures_theoriques @endphp
                                    <span class="{{ $ecart >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $ecart >= 0 ? '+' : '' }}{{ $ecart }}h
                                    </span>
                                </td>
                                <td>{{ $te->dossier->nom ?? '-' }} <small class="text-muted">({{ $te->dossier->client->nom ?? '-' }})</small></td>
                                <td><strong>{{ $te->heures }}h</strong></td>
                                <td>{{ $te->travaux ?? '-' }}</td>
                            </tr>
                        @endforeach
                        @if($entry->timeEntries->isEmpty())
                            <tr class="table-warning">
                                <td>{{ $entry->jour->format('d/m/Y') }}</td>
                                <td>{{ $entry->heures_theoriques }}h</td>
                                <td>0h</td>
                                <td class="text-danger">-{{ $entry->heures_theoriques }}h</td>
                                <td colspan="3"><em>Aucune activité saisie</em></td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
