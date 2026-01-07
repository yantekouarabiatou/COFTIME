<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class PersonnelMissionExport implements
    FromArray,
    WithTitle,
    WithStyles,
    WithEvents,
    WithCustomStartCell
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];

        // Les données du tableau principal commencent plus bas (ligne 8)
        // On ne met rien ici car tout est géré dans AfterSheet

        // On prépare les lignes de données brutes pour le mapping
        foreach ($this->data['personnels'] as $personnel) {
            $autresMissions = count($personnel['autres_missions']) . ' mission(s)';

            // Statut avec emoji (conservé pour visibilité, mais peut être remplacé par texte)
            $statut = 'Disponible';
            $statutColor = 'success'; // pour usage futur si besoin
            if ($personnel['charge_totale']['ecart'] > 10) {
                $statut = 'Surcharge';
                $statutColor = 'danger';
            } elseif ($personnel['charge_totale']['ecart'] > 5) {
                $statut = 'Charge élevée';
                $statutColor = 'warning';
            }

            $rows[] = [
                $personnel['user']->full_name,
                $personnel['user']->poste?->intitule ?? 'Non défini',
                $personnel['total_heures'],
                $personnel['charge_totale']['heures_reelles'],
                $autresMissions,
                $statut,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Personnels sur Mission';
    }

    public function startCell(): string
    {
        return 'A8'; // Les données commencent en ligne 8
    }

    public function styles(Worksheet $sheet)
    {
        // Hauteur par défaut des lignes
        $sheet->getDefaultRowDimension()->setRowHeight(22);

        // Largeurs des colonnes
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(18);

        // Alignements généraux
        $sheet->getStyle('A:F')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dossier = $this->data['dossier'];
                $stats = $this->data['stats'];
                $personnelsCount = count($this->data['personnels']);

                // Dernière ligne de données
                $lastDataRow = $personnelsCount + 7; // 8 + count - 1

                // ===== TITRE PRINCIPAL =====
                $sheet->setCellValue('A1', 'RAPPORT DES PERSONNELS SUR MISSION');
                $sheet->mergeCells('A1:F1');
                $sheet->getRowDimension(1)->setRowHeight(35);
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FF2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // ===== INFORMATIONS DOSSIER =====
                $sheet->setCellValue('A3', 'Dossier :');
                $sheet->setCellValue('B3', $dossier->nom);
                $sheet->setCellValue('A4', 'Référence :');
                $sheet->setCellValue('B4', $dossier->reference);
                $sheet->setCellValue('A5', 'Client :');
                $sheet->setCellValue('B5', $dossier->client->nom ?? 'N/A');

                $sheet->getStyle('A3:A5')->getFont()->setBold(true);
                $sheet->getStyle('B3:B5')->getFont()->setSize(12);

                // ===== EN-TÊTES DU TABLEAU =====
                $headers = ['Personnel', 'Poste', 'Heures sur le dossier', 'Charge totale (h)', 'Autres missions', 'Statut'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . '7', $header);
                    $col++;
                }

                $sheet->getRowDimension(7)->setRowHeight(35);
                $sheet->getStyle('A7:F7')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF2E75B6']]],
                ]);

                // ===== STYLE DES DONNÉES =====
                $dataRange = 'A8:F' . $lastDataRow;
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD9D9D9']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Alignements spécifiques
                $sheet->getStyle('C8:D' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('F8:F' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Couleur conditionnelle du statut
                for ($row = 8; $row <= $lastDataRow; $row++) {
                    $statut = $sheet->getCell('F' . $row)->getValue();
                    $fillColor = 'FFE2EFDA'; // vert clair par défaut
                    if ($statut === 'Surcharge') {
                        $fillColor = 'FFF8E0E0'; // rouge clair
                    } elseif ($statut === 'Charge élevée') {
                        $fillColor = 'FFFFF2CC'; // jaune clair
                    }
                    $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->applyFromArray([
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => $fillColor],
                    ]);
                }

                // ===== SECTION STATISTIQUES =====
                $statsStartRow = $lastDataRow + 2;

                $sheet->setCellValue('A' . $statsStartRow, 'STATISTIQUES GLOBALES');
                $sheet->mergeCells('A' . $statsStartRow . ':C' . $statsStartRow);
                $sheet->getStyle('A' . $statsStartRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF2E75B6']],
                ]);

                $statsData = [
                    ['Nombre de personnels intervenants', $stats['total_personnels']],
                    ['Total heures travaillées sur le dossier', $stats['total_heures'] . ' h'],
                    ['Heures théoriques attendues', $stats['heure_theorique'] . ' h'],
                    ['Surplus / Déficit', $stats['surplus'] . ' h'],
                    ['Taux de réalisation', $stats['surplus_pourcentage'] . ' %'],
                    ['Moyenne par personnel', $stats['moyenne_par_personnel'] . ' h'],
                ];

                $currentRow = $statsStartRow + 2;
                foreach ($statsData as [$label, $value]) {
                    $sheet->setCellValue('A' . $currentRow, $label);
                    $sheet->setCellValue('C' . $currentRow, $value);
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                    $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $currentRow++;
                }

                // Style du bloc statistiques
                $statsRange = 'A' . ($statsStartRow + 2) . ':C' . ($currentRow - 1);
                $sheet->getStyle($statsRange)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // ===== DATE D'EXPORT =====
                $exportRow = $currentRow + 2;
                $sheet->setCellValue('F' . $exportRow, 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'));
                $sheet->getStyle('F' . $exportRow)->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                // ===== FIGER LES EN-TÊTES =====
                $sheet->freezePane('A8');
            },
        ];
    }
}
