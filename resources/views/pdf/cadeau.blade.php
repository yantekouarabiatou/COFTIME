<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche Cadeau/Invitation - {{ $cadeauInvitation->nom }}</title>
    <style>
        /* Définition de la couleur principale de l'entreprise */
        :root {
            --primary-color: #002060; /* Bleu marine foncé institutionnel */
            --info-color: #0c346d; /* Couleur pour les informations spécifiques */
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
            color: var(--primary-color);
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
            border: 1px solid var(--info-color);
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
            width: 30%;
        }

        /* MISE EN ÉVIDENCE DE L'ACTION PRISE */
        .action-row th {
            background-color: var(--primary-color) !important;
            color: white !important;
            font-size: 13px !important;
            text-align: center !important;
            width: 40% !important;
        }

        .action-row td {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 13px !important;
            text-align: center !important;
            width: 60% !important;
        }

        /* BLOCS DE TEXTE LONG */
        .detail-block {
            margin-top: 15px;
            border: 1px solid #ccc;
            padding: 10px;
            background: #f8f9fa;
            min-height: 80px;
        }

        .detail-block h3 {
            color: var(--primary-color);
            font-size: 14px;
            margin-bottom: 5px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 3px;
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
            <h1>REGISTRE DES CADEAUX ET INVITATIONS</h1>
            <h2>OBJET: {{ $cadeauInvitation->nom ?? 'Non spécifié' }}</h2>
            <div class="reference-number">
                N° INTERNE : #{{ str_pad($cadeauInvitation->id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <div class="data-section">
            <h3 style="background-color: #17a2b81a; color: var(--primary-color); padding: 8px; border-left: 5px solid var(--info-color);">
                INFORMATIONS SUR LE CADEAU / L'INVITATION
            </h3>
            <table class="data-table">
                <tr>
                    <th style="width: 30%">Nom de l'Activité</th>
                    <td style="width: 70%" colspan="3">{{ $cadeauInvitation->nom }}</td>
                </tr>
                <tr>
                    <th>Date de l'Activité</th>
                    <td>{{ $cadeauInvitation->date?->format('d/m/Y') ?? 'Non spécifiée' }}</td>
                    <th>Type d'Offre</th>
                    <td>{{ $cadeauInvitation->cadeau_hospitalite ?: 'Non défini' }}</td>
                </tr>
                <tr>
                    <th>Valeur Estimée (FCFA)</th>
                    <td><span style="font-weight: bold; color: green;">{{ $cadeauInvitation->valeurs_formatted }}</span></td>
                    <th>Déclaré par</th>
                    <td>{{ $cadeauInvitation->user->nom ?? 'Inconnu' }} {{ $cadeauInvitation->user->prenom ?? '' }}</td>
                </tr>
                <tr>
                    <th>Responsable Traitement</th>
                    <td>{{ $cadeauInvitation->responsable->nom ?? 'Non' }} {{ $cadeauInvitation->responsable->prenom ?? 'Assigné' }}</td>
                    <th>Document Joint</th>
                    <td>
                        @if($cadeauInvitation->document)
                            Oui ({{ basename($cadeauInvitation->document) }})
                        @else
                            Non
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="data-section">
            <div class="detail-block" style="border: 1px solid var(--primary-color);">
                <h3>Description Détaillée</h3>
                <div style="font-size: 11px;">
                    {{ $cadeauInvitation->description ?? 'Aucune description détaillée n\'est fournie.' }}
                </div>
            </div>
        </div>

        <div class="data-section">
            <table class="data-table">
                <tr class="action-row">
                    <th>Action Prise</th>
                    <td>
                        @php
                            $textColor = match($cadeauInvitation->action_prise) {
                                'accepté' => 'green',
                                'refusé' => 'red',
                                'en_attente' => '#ffc107',
                                default => 'gray'
                            };
                        @endphp
                        <span style="color: {{ $textColor }};">{{ $cadeauInvitation->action_prise_formatted }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="info-section">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-item">
                        <div class="info-label">Date de création du dossier:</div>
                        <div class="info-value">{{ $cadeauInvitation->created_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dernière mise à jour:</div>
                        <div class="info-value">{{ $cadeauInvitation->updated_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}</div>
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
