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

    // La période affichée reflète la date du premier et du dernier
    // enregistrement réel (et non la plage demandée dans le filtre).
    $periodStart = $entries->isNotEmpty() ? \Carbon\Carbon::parse($entries->min('jour')) : $debut;
    $periodEnd   = $entries->isNotEmpty() ? \Carbon\Carbon::parse($entries->max('jour')) : $fin;

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
        table.detail tbody tr:nth-child(even) td { background: #eef2f9; }
        table.detail tbody tr.no-entries td { font-style: italic; color: #555; }

        .col-date    { width: 8%;  text-align: center; white-space: nowrap; }
        .col-jour    { width: 7%;  text-align: center; }
        .col-dossier { width: 24%; }
        .col-horaire { width: 12%; }
        .col-heures  { width: 12%; text-align: center; }
        .col-statut  { width: 9%;  text-align: center; font-weight: bold; }
        .col-comment { width: 28%; }

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

    {{-- Tableau : une ligne par enregistrement --}}
    <table class="detail">
        <thead>
            <tr>
                <th class="col-date">Date</th>
                <th class="col-jour">Jour</th>
                <th class="col-dossier">Dossier(s) / Client(s)</th>
                <th class="col-horaire">Horaire(s)</th>
                <th class="col-heures">Th. / Réel</th>
                <th class="col-statut">Statut</th>
                <th class="col-comment">Commentaire</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                @php
                    $dossiers = $entry->timeEntries->map(function ($te) {
                        $nom = $te->dossier?->nom ?? 'Sans dossier';
                        return $te->dossier?->client?->nom ? "{$nom} ({$te->dossier->client->nom})" : $nom;
                    })->implode('; ');

                    $horaires = $entry->timeEntries->map(function ($te) {
                        $d = $te->heure_debut ? \Carbon\Carbon::parse($te->heure_debut)->format('H:i') : '-';
                        $f = $te->heure_fin ? \Carbon\Carbon::parse($te->heure_fin)->format('H:i') : '-';
                        return "{$d}-{$f}";
                    })->implode('; ');
                @endphp
                <tr class="{{ $entry->timeEntries->isEmpty() ? 'no-entries' : '' }}">
                    <td class="col-date">{{ $entry->jour->format('d/m/Y') }}</td>
                    <td class="col-jour">{{ ucfirst($entry->jour->translatedFormat('l')) }}</td>
                    <td class="col-dossier">{{ $dossiers ?: 'Aucune activité saisie' }}</td>
                    <td class="col-horaire">{{ $horaires ?: '-' }}</td>
                    <td class="col-heures">{{ fmtH($entry->heures_theoriques) }} / {{ fmtH($entry->heures_reelles) }}</td>
                    <td class="col-statut">{{ ucfirst($entry->statut) }}</td>
                    <td class="col-comment">{{ $entry->commentaire ?: ($entry->motif_refus ? 'Refus : ' . $entry->motif_refus : '-') }}</td>
                </tr>
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
