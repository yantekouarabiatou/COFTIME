<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Export Excel - feuille de temps d'UN collaborateur sur une période donnée
 * (un mois, ou une plage de dates libre). Une ligne par jour saisi, avec le
 * détail des dossiers travaillés (activités) afin de suivre en un coup d'œil
 * qui a travaillé, quand, et sur quel dossier.
 */
class UserPeriodTimesheetExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithEvents,
    WithColumnFormatting,
    WithCustomStartCell
{
    protected User $user;
    protected Collection $entries;
    protected Carbon $debut;
    protected Carbon $fin;

    protected float $totalHeuresReelles = 0;
    protected float $totalHeuresTheoriques = 0;

    public function __construct(User $user, Collection $entries, Carbon $debut, Carbon $fin)
    {
        $this->user    = $user;
        $this->entries = $entries;
        $this->debut   = $debut;
        $this->fin     = $fin;

        $this->totalHeuresReelles    = (float) $entries->sum('heures_reelles');
        $this->totalHeuresTheoriques = (float) $entries->sum('heures_theoriques');
    }

    /**
     * Libellé lisible de la période : "du DD/MM/YYYY au DD/MM/YYYY", basé sur
     * la date du premier et du dernier enregistrement réel (et non la plage
     * demandée dans le filtre).
     */
    protected function periodLabel(): string
    {
        $start = $this->entries->isNotEmpty() ? Carbon::parse($this->entries->min('jour')) : $this->debut;
        $end   = $this->entries->isNotEmpty() ? Carbon::parse($this->entries->max('jour')) : $this->fin;

        return 'du ' . $start->format('d/m/Y') . ' au ' . $end->format('d/m/Y');
    }

    public function collection()
    {
        return $this->entries;
    }

    public function headings(): array
    {
        // En-têtes écrits manuellement dans AfterSheet
        return [];
    }

    public function map($entry): array
    {
        $activites = $entry->timeEntries->map(function ($te) {
            $debut = $te->heure_debut ? Carbon::parse($te->heure_debut)->format('H:i') : '';
            $fin   = $te->heure_fin ? Carbon::parse($te->heure_fin)->format('H:i') : '';
            $plage = $debut && $fin ? " ({$debut}-{$fin})" : '';
            $client = $te->dossier?->client?->nom ? ' - ' . $te->dossier->client->nom : '';

            return '- ' . ($te->dossier?->nom ?? 'Sans dossier') . $client . $plage
                . ' : ' . number_format($te->heures_reelles, 2) . 'h';
        })->implode("\n");

        $ecart = $entry->heures_reelles - $entry->heures_theoriques;

        return [
            $entry->jour->format('d/m/Y'),
            ucfirst($entry->jour->translatedFormat('l')),
            number_format($entry->heures_theoriques, 2),
            number_format($entry->heures_reelles, 2),
            number_format($ecart, 2),
            $activites ?: 'Aucune activité saisie',
            $entry->commentaire ?: '-',
            ucfirst($entry->statut),
            $entry->valide_le?->format('d/m/Y H:i') ?? '-',
            $entry->motif_refus ?: '-',
        ];
    }

    // Les données commencent en A7 : lignes 1-5 = titre/résumé, ligne 6 = en-têtes (écrits
    // manuellement dans AfterSheet), ligne 7+ = données mappées par la librairie.
    public function startCell(): string
    {
        return 'A7';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getDefaultRowDimension()->setRowHeight(22);
        $sheet->getStyle('A:J')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('F:F')->getAlignment()->setWrapText(true);
        $sheet->getStyle('G:G')->getAlignment()->setWrapText(true);

        $sheet->getColumnDimension('A')->setWidth(13);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(55);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(13);
        $sheet->getColumnDimension('I')->setWidth(17);
        $sheet->getColumnDimension('J')->setWidth(28);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Couleurs de la charte COFIMA (public/assets/css/custom.css)
                $primary      = 'FF244584';
                $primaryDark  = 'FF1A3461';
                $primaryLight = 'FF2D5AA8';
                $primaryTint  = 'FFEEF2F9';

                $lastDataRow = $this->entries->count() + 6;

                // ===== TITRE =====
                $sheet->setCellValue('A1', 'FEUILLE DE TEMPS');
                $sheet->mergeCells('A1:J1');
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => $primary]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // ===== SOUS-TITRE : COLLABORATEUR / PÉRIODE =====
                $subTitle = 'Collaborateur : ' . $this->user->prenom . ' ' . $this->user->nom
                    . ' (' . ($this->user->poste?->intitule ?? 'Poste non défini') . ')'
                    . ' | Période : ' . $this->periodLabel();

                $sheet->setCellValue('A2', $subTitle);
                $sheet->mergeCells('A2:J2');
                $sheet->getRowDimension(2)->setRowHeight(24);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF404040']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']],
                ]);

                $sheet->getRowDimension(3)->setRowHeight(8);

                // ===== RÉSUMÉ RAPIDE =====
                $ecartTotal = $this->totalHeuresReelles - $this->totalHeuresTheoriques;
                $resume = sprintf(
                    'Jours saisis : %d   |   Heures théoriques : %sh   |   Heures réelles : %sh   |   %s : %sh',
                    $this->entries->count(),
                    number_format($this->totalHeuresTheoriques, 2),
                    number_format($this->totalHeuresReelles, 2),
                    $ecartTotal >= 0 ? 'Surplus' : 'Déficit',
                    number_format(abs($ecartTotal), 2)
                );
                $sheet->setCellValue('A4', $resume);
                $sheet->mergeCells('A4:J4');
                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10.5, 'color' => ['argb' => 'FF404040']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getRowDimension(5)->setRowHeight(6);

                // ===== EN-TÊTES =====
                $headers = [
                    'A6' => 'Date', 'B6' => 'Jour', 'C6' => 'H. Théoriques', 'D6' => 'H. Réelles',
                    'E6' => 'Écart', 'F6' => 'Dossiers / Activités', 'G6' => 'Commentaire',
                    'H6' => 'Statut', 'I6' => 'Validée le', 'J6' => 'Motif refus',
                ];
                foreach ($headers as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                }

                $sheet->getRowDimension(6)->setRowHeight(32);
                $sheet->getStyle('A6:J6')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $primary]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => $primary]]],
                ]);

                // ===== DONNÉES =====
                $dataRange = "A7:J{$lastDataRow}";
                $sheet->getStyle($dataRange)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD9D9D9']]],
                ]);
                $sheet->getStyle("A7:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C7:E{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("H7:I{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Couleur alternée légère + ajustement hauteur selon nb d'activités
                for ($row = 7; $row <= $lastDataRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $primaryTint]],
                        ]);
                    }
                    $value = $sheet->getCell("F{$row}")->getValue();
                    if ($value && strpos($value, "\n") !== false) {
                        $lineCount = substr_count($value, "\n") + 1;
                        $sheet->getRowDimension($row)->setRowHeight(max(22, 18 * $lineCount));
                    }
                }

                // ===== TOTAUX =====
                $totalRow = $lastDataRow + 2;
                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->mergeCells("A{$totalRow}:B{$totalRow}");
                $sheet->setCellValue("C{$totalRow}", number_format($this->totalHeuresTheoriques, 2));
                $sheet->setCellValue("D{$totalRow}", number_format($this->totalHeuresReelles, 2));
                $sheet->setCellValue("E{$totalRow}", number_format($ecartTotal, 2));

                $sheet->getStyle("A{$totalRow}:J{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $primaryDark]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ===== STATISTIQUES PAR STATUT =====
                $statsByStatus = [];
                foreach ($this->entries as $entry) {
                    $statsByStatus[$entry->statut] ??= ['count' => 0, 'heures' => 0];
                    $statsByStatus[$entry->statut]['count']++;
                    $statsByStatus[$entry->statut]['heures'] += $entry->heures_reelles;
                }

                $statsRow = $totalRow + 2;
                $sheet->setCellValue("A{$statsRow}", 'RÉPARTITION PAR STATUT');
                $sheet->mergeCells("A{$statsRow}:D{$statsRow}");
                $sheet->getStyle("A{$statsRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11.5],
                ]);
                $statsRow++;

                foreach ($statsByStatus as $status => $data) {
                    $sheet->setCellValue("A{$statsRow}", ucfirst($status) . ' :');
                    $sheet->setCellValue("B{$statsRow}", $data['count'] . ' jour(s)');
                    $sheet->setCellValue("C{$statsRow}", number_format($data['heures'], 2) . ' h');
                    $sheet->getStyle("A{$statsRow}:C{$statsRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD9D9D9']]],
                    ]);
                    $statsRow++;
                }

                $exportRow = $statsRow + 1;
                $sheet->setCellValue("J{$exportRow}", 'Exporté le ' . now()->format('d/m/Y à H:i'));
                $sheet->getStyle("J{$exportRow}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                $sheet->freezePane('A7');
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_00,
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'E' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
