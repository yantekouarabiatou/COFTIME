<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche Client Audit - {{ $clientAudit->nom_client }}</title>
    <style>
        /* Définition de la couleur principale de l'entreprise */
        :root {
            --primary-color: #002060; /* Bleu marine foncé institutionnel */
            --audit-color: #007bff; /* Bleu Bootstrap pour l'Audit */
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
            font-size: 24px;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header h2 {
            color: var(--audit-color);
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .reference-number {
            font-size: 14px;
            font-weight: bold;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        /* TABLEAU DES DÉTAILS */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border: 1px solid var(--audit-color);
        }

        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 11px;
            vertical-align: top;
            text-align: left;
        }

        .data-table th {
            background-color: #e9f5ff; /* Léger bleu pour l'audit */
            font-weight: bold;
            color: #333;
            width: 35%;
        }

        /* Total Section */
        .total-section {
            width: 100%;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .total-label {
            width: 50%;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            color: var(--primary-color);
            padding: 10px;
            background-color: #f0f4f7;
            border-top: 2px solid var(--audit-color);
        }

        .total-value {
            width: 50%;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            color: rgb(7, 104, 146);
            padding: 10px;
            background-color: #e6ffe6; /* Vert très clair */
            border-top: 2px solid var(--audit-color);
        }


        /* INFORMATIONS SUPPLÉMENTAIRES (MÉTA) */
        .info-section {
            margin-top: 25px;
            padding: 15px;
            border: 1px solid #ddd;
            background: #f8f9fa;
            border-left: 5px solid var(--primary-color);
            font-size: 12px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .info-row {
            display: table-row;
        }

        .info-item {
            display: table-cell;
            width: 50%;
            padding-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            color: var(--primary-color);
            font-size: 12px;
        }

        .info-value {
            font-size: 12px;
        }

        /* Pied de page */
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
            <h1>FICHE CLIENT AUDIT</h1>
            <h2>{{ $clientAudit->nom_client ?? 'Client Non Spécifié' }}</h2>
            <div class="reference-number">
                N° INTERNE : #{{ str_pad($clientAudit->id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <div class="data-section">
            <h3 style="background-color: #007bff1a; color: var(--primary-color); padding: 8px; border-left: 5px solid var(--audit-color);">
                COORDONNÉES ET IDENTIFICATION
            </h3>
            <table class="data-table">
                <tr>
                    <th>Nom du Client</th>
                    <td><span style="font-weight: bold;">{{ $clientAudit->nom_client }}</span></td>
                    <th>Siège Social</th>
                    <td>{{ $clientAudit->siege_social ?? 'Non spécifié' }}</td>
                </tr>
                <tr>
                    <th>Adresse Complète</th>
                    <td colspan="3">{{ $clientAudit->adresse ?? 'Non spécifiée' }}</td>
                </tr>
                <tr>
                    <th>Dossier géré par</th>
                    <td>{{ $clientAudit->user->nom ?? 'Système' }} {{ $clientAudit->user->prenom ?? '' }}</td>
                    <th>Document(s) joint(s)</th>
                    <td>
                        @if($clientAudit->document)
                            Oui ({{ basename($clientAudit->document) }})
                        @else
                            Non
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="data-section">
            <h3 style="background-color: #007bff1a; color: var(--primary-color); padding: 8px; border-left: 5px solid var(--audit-color);">
                STRUCTURE DES FRAIS (FCFA)
            </h3>
            <table class="data-table">
                <tr>
                    <th style="width: 50%;">Frais d'Audit Principal</th>
                    <td style="width: 50%; text-align: right;">{{ number_format($clientAudit->frais_audit ?? 0, 2, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <th>Autres Frais et Honoraires</th>
                    <td style="text-align: right;">{{ number_format($clientAudit->frais_autres ?? 0, 2, ',', ' ') }} FCFA</td>
                </tr>
                <tr style="border-top: 2px solid var(--audit-color);">
                    <th style="background-color: var(--primary-color); color: white; font-size: 14px;">TOTAL DES FRAIS</th>
                    <td style="background-color: #e6ffe6; font-size: 14px; font-weight: bold; color: green; text-align: right;">
                        {{ number_format($clientAudit->getTotalFraisAttribute(), 2, ',', ' ') }} FCFA
                    </td>
                </tr>
            </table>
        </div>


        <div class="info-section">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-item">
                        <div class="info-label">Date de création de la fiche:</div>
                        <div class="info-value">{{ $clientAudit->created_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dernière modification:</div>
                        <div class="info-value">{{ $clientAudit->updated_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}</div>
                    </div>
                </div>
            </div>
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
                Ce document est confidentiel.
            </div>
        </div>
    </div>
</body>
</html>
