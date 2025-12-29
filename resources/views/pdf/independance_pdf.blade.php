<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déclaration d'Indépendance - {{ $independance->nom_client }}</title>
    <style>
        /* Définition de la couleur principale de l'entreprise */
        :root {
            --primary-color: #002060; /* Bleu marine foncé institutionnel */
        }

        /* Réinitialisation et polices */
        * {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            box-sizing: border-box;
        }

        body {
            padding: 30px;
            background: #fff;
            font-size: 12px;
        }

        /* LOGO ET EN-TÊTE */
        .logo-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-image {
            max-width: 180px;
            height: auto;
            margin-bottom: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 15px;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 22px; /* Légèrement réduit pour les longs titres */
            margin-bottom: 3px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header h2 {
            color: var(--primary-color);
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .header h3 {
            color: var(--primary-color);
            font-size: 16px;
            font-weight: bold;
            padding: 5px 0;
            background-color: #e9ecef;
            border-radius: 5px;
            text-align: center;
        }

        /* SECTIONS DE DONNÉES */
        .data-section {
            margin-bottom: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 11px;
            vertical-align: top;
            text-align: left;
        }

        .data-table th {
            background-color: #f0f4f7;
            font-weight: bold;
            color: #333;
            width: 35%;
        }

        .data-table .highlight-row th {
            background-color: var(--primary-color);
            color: white;
            font-size: 12px;
            text-align: center;
        }

        .data-table .highlight-row td {
            font-weight: bold;
            background-color: #e9ecef;
            font-size: 12px;
            text-align: center;
        }

        /* Contenu long */
        .long-text {
            border: 1px solid #ccc;
            padding: 10px;
            background: #fff;
            min-height: 50px;
            font-size: 11px;
        }

        /* FOOTER */
        .footer {
            margin-top: 40px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #7f8c8d;
            font-size: 10px;
        }

        .print-date {
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-section">
            @if(isset($logoBase64))
                <img src="{{ $logoBase64 }}" alt="Logo COFIMA" class="logo-image">
            @else
                <div style="font-size: 40px; color: var(--primary-color); font-weight: 700;">COFIMA</div>
            @endif
        </div>

        <div class="header">
            <h1>FICHE DE DÉCLARATION D'INDÉPENDANCE</h1>
            <h2>CLIENT: {{ $independance->nom_client }}</h2>
        </div>

        <div class="data-section">
            <h3>INFORMATIONS GÉNÉRALES SUR LE CLIENT</h3>
            <table class="data-table">
                <tr>
                    <th style="width: 25%">Nom du Client / Entité</th>
                    <td style="width: 25%">{{ $independance->nom_client }}</td>
                    <th style="width: 25%">Type d'Entité</th>
                    <td style="width: 25%">{{ $independance->type_entite ?: 'Non spécifié' }}</td>
                </tr>
                <tr>
                    <th>Siège Social</th>
                    <td>{{ $independance->siege_social ?: '-' }}</td>
                    <th>Adresse Complète</th>
                    <td>{{ $independance->adresse ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Années d'Expérience avec le Client</th>
                    <td>{{ $independance->nombres_annees_experiences ?: '0' }} an(s)</td>
                    <th>Déclarant</th>
                    <td>{{ $independance->user->nom ?? 'Inconnu' }} {{ $independance->user->prenom ?? '' }}</td>
                </tr>
            </table>
        </div>

        <div class="data-section">
            <h3>DÉTAILS DES FRAIS ET HONORAIRES (FCFA)</h3>
            <table class="data-table">
                <tr class="highlight-row">
                    <th>Total Frais Annuel</th>
                    <td>{{ $independance->total_frais_formatted }}</td>
                </tr>
                <tr>
                    <th>Frais d'Audit (Montant facturé)</th>
                    <td>{{ $independance->frais_audit_formatted }}</td>
                </tr>
                <tr>
                    <th>Frais de Services Hors Audit</th>
                    <td>{{ $independance->frais_non_audit_formatted }}</td>
                </tr>
                <tr>
                    <th>Honoraires d'Audit (Exercice)</th>
                    <td>{{ number_format($independance->honoraire_audit_exercice, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <th>Honoraires d'Audit (Travail)</th>
                    <td>{{ number_format($independance->honoraire_audit_travail, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>

        <div class="data-section">
            <h3>SERVICES AUTRES QUE L'AUDIT FOURNIS</h3>
            <div class="long-text">
                {{ $independance->autres_services_fournit ?: 'Aucun autre service que l\'audit déclaré.' }}
            </div>
        </div>

        <div class="data-section">
            <h3>PERSONNEL IMPLIQUÉ ET DÉCISION D'INDÉPENDANCE</h3>
            <table class="data-table">
                <tr>
                    <th style="width: 35%">Associé(s) sur la Mission</th>
                    <td style="width: 65%">{{ $independance->associes_mission_names }}</td>
                </tr>
                <tr>
                    <th>Responsable(s) de l'Audit</th>
                    <td>{{ $independance->responsable_audit_names }}</td>
                </tr>
            </table>
        </div>

        <div class="data-section">
            <h3>ÉVALUATION DU RISQUE D'INDÉPENDANCE ET ACTIONS REQUISES</h3>
            <table class="data-table">
                <tr class="highlight-row">
                    <th colspan="2" style="text-align: left;">Question d'Indépendance Soulevée</th>
                </tr>
                <div>

                </div>
                <tr>
                    <td colspan="2">
                        <div class="long-text" style="background: #fff;">
                            {{ $independance->question_independance ?: 'Aucune question particulière soulevée.' }}
                        </div>
                    </td>
                </tr>
                <tr class="highlight-row">
                    <th colspan="2" style="text-align: left;">Actions Requises pour Garantir l'Indépendance</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="long-text" style="background: #fff;">
                            {{ $independance->actions_recquise ?: 'Aucune action requise.' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <div class="print-date">
                Document généré le {{ date('d/m/Y à H:i:s') }}
            </div>
            <div style="margin-top: 5px; font-size: 9px;">
                @if(isset($companySetting))
                    {{ $companySetting->adresse ?? 'Adresse non définie' }} | Tél: {{ $companySetting->telephone ?? 'Non défini' }} | Site: {{ $companySetting->site_web ?? 'Non défini' }}
                @else
                    COFIMA BENIN
                @endif
            </div>
            <div style="font-size: 9px; margin-top: 3px;">
                Ce document est confidentiel et ne doit être communiqué qu'aux personnes autorisées.
            </div>
        </div>
    </div>
</body>
</html>
