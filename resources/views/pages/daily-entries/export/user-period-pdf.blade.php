@php
    if (!function_exists('fmtH')) {
        function fmtH($decimal) {
            if (!$decimal || $decimal <= 0) return '0h';
            $h = floor($decimal);
            $m = round(($decimal - $h) * 60);
            if ($m >= 60) { $h++; $m -= 60; }
            return $m == 0 ? "{$h}h" : "{$h}h" . str_pad($m, 2, '0', STR_PAD_LEFT);
        }
    }

    $totalTheorique = $entries->sum('heures_theoriques');
    $totalReel      = $entries->sum('heures_reelles');
    $ecart          = $totalReel - $totalTheorique;

    // La période affichée couvre systématiquement le mois entier (du 1er au
    // dernier jour), quel que soit le collaborateur, plutôt que la plage
    // exacte demandée dans le filtre.
    $monthAnchor = $entries->isNotEmpty() ? \Carbon\Carbon::parse($entries->min('jour')) : $debut;
    $periodStart = $monthAnchor->copy()->startOfMonth();
    $periodEnd   = $monthAnchor->copy()->endOfMonth();

    $periodLabel = 'du ' . $periodStart->format('d/m/Y') . ' au ' . $periodEnd->format('d/m/Y');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Feuille de temps - {{ $user->prenom }} {{ $user->nom }} - {{ $periodLabel }}</title>

    <style>
        @font-face {
            font-family: 'Helvetica';
            font-style: normal;
            font-weight: normal;
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica.ttf')) }}") format('truetype');
        }
        @font-face {
            font-family: 'Helvetica';
            font-style: normal;
            font-weight: bold;
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica-Bold.ttf')) }}") format('truetype');
        }
        @font-face {
            font-family: 'Helvetica';
            font-style: italic;
            font-weight: normal;
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica-Oblique.ttf')) }}") format('truetype');
        }
        @font-face {
            font-family: 'Helvetica';
            font-style: italic;
            font-weight: bold;
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica-BoldOblique.ttf')) }}") format('truetype');
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
            padding: 30px 40px 90px 40px;
        }

        /* -- En-tete : logo uniquement -------------------------- */
        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #244584;
        }
        .header .logo { max-width: 200px; max-height: 55px; }
        .brand-fallback { font-size: 22px; font-weight: bold; letter-spacing: 1px; color: #244584; }

        /* -- Titre : gras, italique, souligne, centre, majuscule - */
        .titre-doc {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            font-style: italic;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #244584;
            margin-top: 18px;
            margin-bottom: 6px;
        }

        .doc-meta {
            text-align: center;
            font-size: 10.5px;
            margin-bottom: 16px;
        }
        .doc-meta strong { color: #244584; }

        /* -- Tableau simple : couleurs COFIMA, une ligne par
              enregistrement --------------------------------------- */
        table.detail { width: 100%; border-collapse: collapse; }
        table.detail thead th {
            background: #244584;
            color: #fff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .2px;
            padding: 6px 5px;
            text-align: left;
            border: 1px solid #244584;
        }
        table.detail tbody td {
            padding: 5px;
            border: 1px solid #c3d1e4;
            vertical-align: top;
            font-size: 9px;
            color: #000;
        }
        table.detail tbody tr.day-even td { background: #eef2f9; }
        table.detail tbody tr.no-entries td { font-style: italic; color: #555; }

        .col-date     { width: 8%;  text-align: center; white-space: nowrap; }
        .col-heures   { width: 10%; text-align: center; }
        .col-activite { width: 22%; }
        .col-horaire  { width: 11%; text-align: center; white-space: nowrap; }
        .col-tache    { width: 24%; }
        .col-statut   { width: 8%;  text-align: center; font-weight: bold; }
        .col-comment  { width: 17%; }

        /* Sous-lignes : une activité = une ligne, groupée sous la date */
        table.detail tbody tr.day-first td { border-top: 2px solid #a9bcd8; }

        table.total-row { width: 100%; border-collapse: collapse; margin-top: 0; }
        table.total-row td {
            background: #1a3461;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
            padding: 7px 8px;
            border: 1px solid #1a3461;
        }
        table.total-row td.label { text-align: left; }
        table.total-row td.value { text-align: right; width: 22%; }
        table.total-row td.ecart { text-align: right; width: 20%; }

        /* -- Pied de page ---------------------------------------- */
        .footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            padding: 10px 40px;
            border-top: 1px solid #ccc;
            font-size: 8px;
            color: #555;
            text-align: center;
            background: #fff;
        }
    </style>
</head>
<body>

    {{-- En-tete : logo uniquement --}}
    <div class="header">
        @if(isset($logoBase64) && $logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo COFIMA" class="logo">
        @else
            <div class="brand-fallback">COFIMA</div>
        @endif
    </div>

    <div class="titre-doc">Feuille de Temps</div>
    <div class="doc-meta">
        <strong>{{ $user->prenom }} {{ $user->nom }}</strong>
        @if($user->poste?->intitule) - {{ $user->poste->intitule }} @endif
        &nbsp;-&nbsp; Période : <strong>{{ $periodLabel }}</strong>
    </div>

    {{-- Tableau : une grande ligne par jour (date), une sous-ligne par
         activité saisie ce jour-là --}}
    <table class="detail">
        <thead>
            <tr>
                <th class="col-date">Date</th>
                <th class="col-heures">Réel / Th.</th>
                <th class="col-activite">Activité</th>
                <th class="col-horaire">Horaire</th>
                <th class="col-tache">Tâche</th>
                <th class="col-statut">Statut</th>
                <th class="col-comment">Commentaire</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $i => $entry)
                @php
                    // Activité = le dossier travaillé ; Tâche = ce qui a été
                    // fait dessus (horaire + description des travaux saisis).
                    // Chaque activité forme sa propre sous-ligne, sous la
                    // grande ligne du jour (Date/Statut/Commentaire groupés).
                    $lignes  = $entry->timeEntries;
                    $rowspan = max($lignes->count(), 1);
                    $dayClass = $i % 2 === 0 ? 'day-even' : '';
                @endphp

                @if($lignes->isEmpty())
                    <tr class="day-first no-entries {{ $dayClass }}">
                        <td class="col-date">{{ $entry->jour->format('d/m/Y') }}</td>
                        <td class="col-heures">{{ fmtH($entry->heures_reelles) }} / {{ fmtH($entry->heures_theoriques) }}</td>
                        <td class="col-activite" colspan="3">Aucune activité saisie</td>
                        <td class="col-statut">{{ ucfirst($entry->statut) }}</td>
                        <td class="col-comment">{{ $entry->commentaire ?: ($entry->motif_refus ? 'Refus : ' . $entry->motif_refus : '-') }}</td>
                    </tr>
                @else
                    @foreach($lignes as $j => $te)
                        <tr class="{{ $dayClass }} {{ $j === 0 ? 'day-first' : '' }}">
                            @if($j === 0)
                                <td class="col-date" rowspan="{{ $rowspan }}">{{ $entry->jour->format('d/m/Y') }}</td>
                                <td class="col-heures" rowspan="{{ $rowspan }}">{{ fmtH($entry->heures_reelles) }} / {{ fmtH($entry->heures_theoriques) }}</td>
                            @endif
                            <td class="col-activite">
                                {{ $te->dossier?->nom ?? 'Sans dossier' }}
                                @if($te->dossier?->client?->nom)
                                    <br><span style="color:#666;">{{ $te->dossier->client->nom }}</span>
                                @endif
                            </td>
                            <td class="col-horaire">
                                {{ $te->heure_debut ? \Carbon\Carbon::parse($te->heure_debut)->format('H:i') : '-' }}
                                -
                                {{ $te->heure_fin ? \Carbon\Carbon::parse($te->heure_fin)->format('H:i') : '-' }}
                            </td>
                            <td class="col-tache">{{ $te->travaux ?: 'Aucune description' }} ({{ fmtH($te->heures_reelles) }})</td>
                            @if($j === 0)
                                <td class="col-statut" rowspan="{{ $rowspan }}">{{ ucfirst($entry->statut) }}</td>
                                <td class="col-comment" rowspan="{{ $rowspan }}">{{ $entry->commentaire ?: ($entry->motif_refus ? 'Refus : ' . $entry->motif_refus : '-') }}</td>
                            @endif
                        </tr>
                    @endforeach
                @endif
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:20px;">
                        Aucune feuille de temps enregistrée pour cette période.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($entries->isNotEmpty())
        <table class="total-row">
            <tr>
                <td class="label">TOTAL - {{ $entries->count() }} jour(s) - {{ fmtH($totalTheorique) }} prévues</td>
                <td class="value">{{ fmtH($totalReel) }} réalisées</td>
                <td class="ecart">{{ $ecart >= 0 ? 'Surplus' : 'Déficit' }} : {{ $ecart >= 0 ? '+' : '-' }}{{ fmtH(abs($ecart)) }}</td>
            </tr>
        </table>
    @endif

    {{-- Pied de page --}}
    <div class="footer">
        N° IFU 3200800611214 - RCCM RB/COT/07B 336 - C/2197 F Immeuble Luca Pacioli, Kouhounou - Cotonou, Bénin<br>
        Tél : {{ $companySetting->telephone ?? '+229 01 21 38 04 58' }} - Mobile : +229 01 90 95 19 59 / 01 95 07 09 48<br>
        Site web : {{ $companySetting->site_web ?? 'https://www.cofima.cc' }} - Email : {{ $companySetting->email ?? 'cofima@cofima.cc' }}
    </div>

</body>
</html>
