<!doctype html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Fiche de Plainte N°
			{{ str_pad($plainte->id, 6, '0', STR_PAD_LEFT) }}
			- COFIMA BENIN</title>
		<style>
		/* Définition de la couleur principale de l'entreprise */:root
		{
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
			max-width: 180px; /* Logo plus grand */
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

		.header h3 {
			color: var(--primary-color);
			font-size: 16px;
			font-weight: bold;
			padding: 5px 0;
			background-color: #e9ecef;
			border-radius: 5px;
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

		/* TABLEAU PRINCIPAL */
		.main-table {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 25px;
			border: 1px solid var(--primary-color);
		}

		.main-table th {
			background-color: var(--primary-color);
			color: white;
			padding: 10px 5px;
			text-align: center;
			font-size: 12px;
			font-weight: 600;
			border: 1px solid #1a1a1a;
		}

		.main-table td {
			padding: 8px;
			border: 1px solid #ddd;
			font-size: 11px;
			vertical-align: top;
			height: 120px; /* Hauteur fixe pour l'impression */
			overflow: hidden;
		}

		/* Contenu des cellules */
		.cell-content {
			padding: 2px;
			max-height: 100%;
			overflow: hidden;
		}


		/* Section des signatures */
		.signatures-table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 30px;
			border: 1px solid var(--primary-color);
		}

		.signatures-table th {
			background-color: var(--primary-color);
			color: white;
			padding: 8px;
			text-align: center;
			font-size: 12px;
			font-weight: bold;
			border: 1px solid #ddd;
		}

		.signatures-table td {
			padding: 8px;
			border: 1px solid #ddd;
			font-size: 11px;
			text-align: center;
			vertical-align: middle;
		}

		.signature-cell {
			height: 80px;
			vertical-align: middle;
			text-align: center;
		}

		.signature-image {
			max-width: 150px;
			max-height: 60px;
			object-fit: contain;
		}

		.signature-placeholder {
			color: #95a5a6;
			font-style: italic;
			font-size: 10px;
		}

		/* Informations supplémentaires */
		.info-section {
			margin-top: 25px;
			padding: 15px;
			border: 1px solid #ddd;
			background: #f8f9fa;
			border-left: 5px solid var(--primary-color);
			font-size: 12px;
		}

		.info-grid {
			/* Utilisation d'un affichage basé sur le tableau pour un meilleur contrôle dans le PDF */
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
		<div
			class="logo-section">
			@if(isset($logoBase64))
                <img src="{{ $logoBase64 }}" alt="Logo COFIMA" class="logo-image">
            @else
                <div style="font-size: 40px; color: var(--primary-color); font-weight: 700;">COFIMA</div>
            @endif
		</div>

		<div class="header">
			<h1>FICHE DE RENSEIGNEMENT ET DE TRAITEMENT DE PLAINTE</h1>
			<h2>{{ $plainte->motif_plainte ?? 'DÉTAILS DE LA PLAINTE' }}</h2>
			<div class="reference-number">
				N° INTERNE : #
				{{ str_pad($plainte->id, 6, '0', STR_PAD_LEFT) }}
				@if($plainte->Reference)
                    | RÉFÉRENCE EXTERNE :
                    {{ $plainte->Reference }}
                @endif
		</div>
		</div>

			<table class="main-table"> <thead>
				<tr>
					<th style="width: 10%">Date Enreg.</th>
					<th style="width: 15%">Motif de la Plainte</th>
					<th style="width: 20%">Nom & Requête du Client</th>
					<th style="width: 15%">Actions Prévues (Prévention)</th>
					<th style="width: 20%">Actions Entreprises (Traitement)</th>
					<th style="width: 20%">Communication et Suivi</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="vertical-align: top;">
						<div class="cell-content">
							{{
    $plainte->dates?->format('d/m/Y')
    ?? $plainte->created_at?->format('d/m/Y')
    ?? 'N/R'
                            }}
						</div>
					</td>

					<td style="vertical-align: top;">
						<div
							class="cell-content">{{ $plainte->motif_plainte ?? 'Non spécifié' }}
						</div>
					</td>

					<td style="vertical-align: top;">
						<div class="cell-content">
							<span style="font-weight: bold;">Client :
								{{ $plainte->nom_client ?? 'Non renseigné' }}
							</span>
							<div style="margin-top: 5px; font-size: 10px;">
								Requête :
								{{ $plainte->requete_client ?? 'Aucune requête spécifiée' }}
							</div>
						</div>
					</td>

					<td style="vertical-align: top;">
						<div
							class="cell-content">{{ $plainte->action_mener ?? 'À déterminer' }}
						</div>
					</td>

					<td style="vertical-align: top;">
						<div
							class="cell-content">{{ $plainte->action_entreprises ?? 'En cours de traitement' }}
						</div>
					</td>

					<td style="vertical-align: top;">
						<div
							class="cell-content">{{ $plainte->communication_personnel ?? 'Non communiqué' }}
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<h3 class="text-center" style="margin-bottom: 15px;">VALIDATION ET SUIVI</h3>
		<table class="signatures-table">
			<thead>
				<tr>
					<th style="width: 30%">Fonction</th>
					<th style="width: 30%">Nom</th>
					<th style="width: 20%">Date de signature</th>
					<th style="width: 20%">Signature</th>
				</tr>
			</thead>
			<tbody>
				@foreach($assignationsData as $data)
                    <tr>
                        <td>{{ $data['assignation']->fonction }}</td>
                        <td>
                            {{ $data['assignation']->nom_prenom ?? 'À désigner' }}
                        </td>
                        <td>
                            @if($data['assignation']->date)
                                {{ \Carbon\Carbon::parse($data['assignation']->date)->format('d/m/Y') }}
                            @else
                            ___/___/____
                        @endif
                        </td>
                            <td
                            class="signature-cell"> @if($data['signature'])
                                <img src="{{ $data['signature'] }}" alt="Signature" class="signature-image">
                            @else
                                <div
                                    class="signature-placeholder">{{ $data['assignation']->date ? 'Non signée' : 'À signer' }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach

				<!-- Ajouter des lignes vides si moins de 2 assignations -->
				@php
                    $remainingRows = max(0, 2 - count($assignationsData));
                @endphp

				@for($i = 0; $i < $remainingRows; $i++)
                    <tr>
                        <td>
                            @if(count($assignationsData) === 0 && $i === 0)
                                Associé / Responsable de la mission
                            @else
                                                Personne responsable du traitement de la plainte
                                            @endif
                        </td>
                        <td>À désigner</td>
                        <td>___/___/____</td>
                        <td class="signature-cell">
                            <div class="signature-placeholder">À signer</div>
                        </td>
                    </tr>
                @endfor
			</tbody>
		</table>

		<div class="info-section">
			<div class="info-grid">
				<div class="info-row">
					<div class="info-item">
						<div class="info-label">Statut de la plainte:</div>
						<div class="info-value">**{{ $plainte->etat_plainte ?? 'En cours' }}**</div>
					</div>
					<div class="info-item">
						<div class="info-label">Créée par:</div>
						<div class="info-value">{{ $plainte->user->nom ?? 'Utilisateur inconnu' }}</div>
					</div>
				</div>
				<div class="info-row">
					<div class="info-item">
						<div class="info-label">Date de création:</div>
						<div class="info-value">{{ $plainte->created_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}</div>
					</div>
					<div class="info-item">
						<div class="info-label">Dernière modification:</div>
						<div class="info-value">{{ $plainte->updated_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}</div>
					</div>
				</div>
			</div>

			@if($plainte->document)
                <div style="margin-top: 15px;">
                    <div class="info-label">Document(s) joint(s):</div>
                    <div
                        class="info-value">
                        {{ basename($plainte->document) }}
                        (Référence du fichier)</div>
                </div>
            @endif
		</div>

		<div class="footer">
			<div class="print-date">
				Document généré le
				{{ date('d/m/Y à H:i:s') }}
			</div>
			<div style="margin-top: 5px; font-size: 9px;">
				@if(isset($companySetting))
                    {{ $companySetting->adresse ?? 'Adresse non définie' }}
                    | Tél:
                    {{ $companySetting->telephone ?? 'Non défini' }}
                    | Site:
                    {{ $companySetting->site_web ?? 'Non défini' }}
                @else
                COFIMA BENIN
            @endif
			</div>
				<div style="font-size: 9px; margin-top: 3px;"> Ce document est confidentiel et ne doit être communiqué qu'aux personnes autorisées.
			</div>
		</div>
	</div>
</body></html>

