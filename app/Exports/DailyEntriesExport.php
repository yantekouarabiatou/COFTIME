<?php

namespace App\Exports;

use App\Models\DailyEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell; // ← Ajouté
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class DailyEntriesExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithEvents,
    WithColumnFormatting,
    WithCustomStartCell
{
    protected $entries;
    protected $totalHeuresReelles = 0;
    protected $totalHeuresTheoriques = 0;
    protected $nombreJours = 0;
    protected $userName;
    protected $startDate;
    protected $endDate;

    public function __construct($entries)
    {
        $this->entries = $entries;
        $this->calculerTotaux();
        $this->extractUserInfo();
    }

    private function calculerTotaux()
    {
        $this->totalHeuresReelles = $this->entries->sum('heures_reelles');
        $this->totalHeuresTheoriques = $this->entries->sum('heures_theoriques');
        $this->nombreJours = $this->entries->count();
    }

    private function extractUserInfo()
    {
        if ($this->entries->isNotEmpty()) {
            $firstEntry = $this->entries->first();
            $this->userName = $firstEntry->user->prenom . ' ' . $firstEntry->user->nom;

            $this->startDate = $this->entries->min('jour');
            $this->endDate = $this->entries->max('jour');
        }
    }

    public function collection()
    {
        return $this->entries;
    }

    public function headings(): array
    {
        // On retourne vide car les en-têtes sont écrits manuellement dans AfterSheet
        return [];
    }

    public function map($entry): array
    {
        $activites = $entry->timeEntries->map(function ($te) {
            $heureDebut = $te->heure_debut ? Carbon::parse($te->heure_debut)->format('H:i') : '';
            $heureFin = $te->heure_fin ? Carbon::parse($te->heure_fin)->format('H:i') : '';
            $plage = $heureDebut && $heureFin ? "($heureDebut-$heureFin)" : '';

            return '- ' . $te->dossier?->nom . ' ' . $plage . ' : ' . number_format($te->heures_reelles, 2) . 'h';
        })->implode("\n");

        $ecart = $entry->heures_reelles - $entry->heures_theoriques;
        $taux = $entry->heures_theoriques > 0
                ? ($entry->heures_reelles / $entry->heures_theoriques) * 100
                : 0;

        return [
            $entry->jour->format('d/m/Y'),
            ucfirst($entry->jour->translatedFormat('l')),
            $entry->user->prenom . ' ' . $entry->user->nom,
            $entry->user->poste?->intitule ?? '-',
            number_format($entry->heures_reelles, 2),
            number_format($entry->heures_theoriques, 2),
            number_format($ecart, 2),
            number_format($taux, 1),
            $activites ?: 'Aucune activité',
            $entry->commentaire ?: '-',
            ucfirst($entry->statut),
            $entry->valide_le?->format('d/m/Y H:i') ?? '-',
            $entry->motif_refus ?: '-',
        ];
    }

    // Les données commencent en A5
    public function startCell(): string
    {
        return 'A5';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getDefaultRowDimension()->setRowHeight(22);
        $sheet->getStyle('A:M')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('I:I')->getAlignment()->setWrapText(true);

        // Largeurs des colonnes
        $sheet->getColumnDimension('A')->setWidth(13);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(16);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(11);
        $sheet->getColumnDimension('I')->setWidth(45);
        $sheet->getColumnDimension('J')->setWidth(28);
        $sheet->getColumnDimension('K')->setWidth(13);
        $sheet->getColumnDimension('L')->setWidth(17);
        $sheet->getColumnDimension('M')->setWidth(28);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Dernière ligne de données (ligne 5 + nombre d'entrées)
                $lastDataRow = $this->entries->count() + 4;

                // ===== TITRE PRINCIPAL =====
                $sheet->setCellValue('A1', 'RAPPORT DES ENTRÉES JOURNALIÈRES');
                $sheet->mergeCells('A1:M1');
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);

                // ===== SOUS-TITRE =====
                $dateRange = '';
                if ($this->startDate && $this->endDate) {
                    $start = Carbon::parse($this->startDate)->format('d/m/Y');
                    $end = Carbon::parse($this->endDate)->format('d/m/Y');
                    $dateRange = $start === $end ? "Date : {$start}" : "Période : {$start} au {$end}";
                }

                $subTitle = "Collaborateur : {$this->userName}";
                if ($dateRange) {
                    $subTitle .= " | {$dateRange}";
                }

                $sheet->setCellValue('A2', $subTitle);
                $sheet->mergeCells('A2:M2');
                $sheet->getRowDimension(2)->setRowHeight(25);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF404040']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']]
                ]);

                // Ligne vide
                $sheet->getRowDimension(3)->setRowHeight(10);

                // ===== EN-TÊTES DES COLONNES =====
                $headers = [
                    'A4' => 'Date', 'B4' => 'Jour', 'C4' => 'Collaborateur', 'D4' => 'Poste',
                    'E4' => 'Heures Réelles', 'F4' => 'Heures Théoriques', 'G4' => 'Écart (h)',
                    'H4' => 'Taux (%)', 'I4' => 'Activités', 'J4' => 'Commentaire',
                    'K4' => 'Statut', 'L4' => 'Validée le', 'M4' => 'Motif Refus',
                ];

                foreach ($headers as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                }

                $sheet->getRowDimension(4)->setRowHeight(35);
                $sheet->getStyle('A4:M4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF2E75B6']]],
                ]);

                // ===== STYLE DES DONNÉES =====
                $dataRange = 'A5:M' . $lastDataRow;
                $sheet->getStyle($dataRange)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD9D9D9']]],
                ]);

                $sheet->getStyle('A5:A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B5:B' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E5:G' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('H5:H' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('K5:K' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('L5:L' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ===== TOTAUX =====
                $separatorRow = $lastDataRow + 1;
                $totalLabelsRow = $separatorRow + 1;
                $totalValuesRow = $totalLabelsRow + 1;

                $sheet->setCellValue("A{$totalLabelsRow}", "TOTAL");
                $sheet->setCellValue("E{$totalLabelsRow}", "Heures réelles");
                $sheet->setCellValue("F{$totalLabelsRow}", "Heures théoriques");
                $sheet->setCellValue("G{$totalLabelsRow}", "Écart total");
                $sheet->setCellValue("H{$totalLabelsRow}", "Taux moyen");
                $sheet->setCellValue("I{$totalLabelsRow}", "Nombre de jours");

                $sheet->setCellValue("E{$totalValuesRow}", number_format($this->totalHeuresReelles, 2));
                $sheet->setCellValue("F{$totalValuesRow}", number_format($this->totalHeuresTheoriques, 2));

                $ecartTotal = $this->totalHeuresReelles - $this->totalHeuresTheoriques;
                $sheet->setCellValue("G{$totalValuesRow}", number_format($ecartTotal, 2));

                $tauxMoyen = $this->totalHeuresTheoriques > 0
                    ? ($this->totalHeuresReelles / $this->totalHeuresTheoriques) * 100
                    : 0;
                $sheet->setCellValue("H{$totalValuesRow}", number_format($tauxMoyen, 1) . '%');

                $sheet->setCellValue("I{$totalValuesRow}", $this->nombreJours);

                $sheet->getStyle("A{$totalLabelsRow}:I{$totalLabelsRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->getStyle("A{$totalValuesRow}:I{$totalValuesRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2EFDA']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF95B3D7']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // ===== STATISTIQUES PAR STATUT =====
                $statsStartRow = $totalValuesRow + 2;
                $sheet->setCellValue("A{$statsStartRow}", "STATISTIQUES PAR STATUT");
                $sheet->mergeCells("A{$statsStartRow}:D{$statsStartRow}");
                $sheet->getStyle("A{$statsStartRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $statsByStatus = [];
                foreach ($this->entries as $entry) {
                    $status = $entry->statut;
                    if (!isset($statsByStatus[$status])) {
                        $statsByStatus[$status] = ['count' => 0, 'total_heures' => 0];
                    }
                    $statsByStatus[$status]['count']++;
                    $statsByStatus[$status]['total_heures'] += $entry->heures_reelles;
                }

                $statsRow = $statsStartRow + 1;
                foreach ($statsByStatus as $status => $data) {
                    $sheet->setCellValue("A{$statsRow}", ucfirst($status) . ":");
                    $sheet->setCellValue("B{$statsRow}", $data['count'] . " jour(s)");
                    $sheet->setCellValue("C{$statsRow}", number_format($data['total_heures'], 2) . " heures");

                    $sheet->getStyle("A{$statsRow}:C{$statsRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFCE4D6']],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2EFDA']]],
                    ]);

                    $statsRow++;
                }

                // ===== DATE D'EXPORT =====
                $exportDateRow = $statsRow + 1;
                $sheet->setCellValue("M{$exportDateRow}", "Exporté le " . now()->format('d/m/Y à H:i'));
                $sheet->getStyle("M{$exportDateRow}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
                ]);

                // Ajustement hauteur des lignes selon le nombre de lignes dans "Activités"
                for ($row = 5; $row <= $lastDataRow; $row++) {
                    $value = $sheet->getCell("I{$row}")->getValue();
                    if ($value && strpos($value, "\n") !== false) {
                        $lineCount = substr_count($value, "\n") + 1;
                        $sheet->getRowDimension($row)->setRowHeight(22 * $lineCount);
                    }
                }

                // ===== FIGER L'EN-TÊTE =====
                $sheet->freezePane('A5'); // Tout ce qui est au-dessus de A5 reste fixe
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_00,
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'G' => NumberFormat::FORMAT_NUMBER_00,
            'H' => NumberFormat::FORMAT_PERCENTAGE_00,
        ];
    }
}
