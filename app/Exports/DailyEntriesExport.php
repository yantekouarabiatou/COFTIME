<?php

namespace App\Exports;

use App\Models\DailyEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class DailyEntriesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithColumnFormatting
{
    protected $entries;
    protected $totalHeuresReelles = 0;
    protected $totalHeuresTheoriques = 0;
    protected $nombreJours = 0;

    public function __construct($entries)
    {
        $this->entries = $entries;
        $this->calculerTotaux();
    }

    private function calculerTotaux()
    {
        $this->totalHeuresReelles = $this->entries->sum('heures_reelles');
        $this->totalHeuresTheoriques = $this->entries->sum('heures_theoriques');
        $this->nombreJours = $this->entries->count();
    }

    public function collection()
    {
        return $this->entries;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Jour',
            'Collaborateur',
            'Poste',
            'Heures Réelles',
            'Heures Théoriques',
            'Écart (h)',
            'Taux (%)',
            'Activités',
            'Commentaire',
            'Statut',
            'Validée le',
            'Motif Refus',
        ];
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
        $taux = $entry->heures_theoriques > 0 ? 
                ($entry->heures_reelles / $entry->heures_theoriques) * 100 : 0;

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

    public function styles(Worksheet $sheet)
    {
        // Styles généraux
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getStyle('A:M')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('I:I')->getAlignment()->setWrapText(true); // Seulement pour les activités
        
        // Largeurs des colonnes
        $sheet->getColumnDimension('A')->setWidth(12); // Date
        $sheet->getColumnDimension('B')->setWidth(12); // Jour
        $sheet->getColumnDimension('C')->setWidth(25); // Collaborateur
        $sheet->getColumnDimension('D')->setWidth(20); // Poste
        $sheet->getColumnDimension('E')->setWidth(12); // Heures Réelles
        $sheet->getColumnDimension('F')->setWidth(12); // Heures Théoriques
        $sheet->getColumnDimension('G')->setWidth(10); // Écart
        $sheet->getColumnDimension('H')->setWidth(10); // Taux
        $sheet->getColumnDimension('I')->setWidth(40); // Activités
        $sheet->getColumnDimension('J')->setWidth(25); // Commentaire
        $sheet->getColumnDimension('K')->setWidth(12); // Statut
        $sheet->getColumnDimension('L')->setWidth(15); // Validée le
        $sheet->getColumnDimension('M')->setWidth(25); // Motif Refus

        // Toutes les lignes en blanc (pas d'alternance)
        $lastRow = $this->entries->count() + 2; // +2 pour l'en-tête
        $dataRange = 'A3:M' . $lastRow;
        $sheet->getStyle($dataRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFFFFF');

        // Bordures pour les données
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC'],
                ],
            ],
        ]);

        // Alignement spécifique
        $sheet->getStyle('A3:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Date
        $sheet->getStyle('B3:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Jour
        $sheet->getStyle('E3:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Heures
        $sheet->getStyle('H3:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Taux
        $sheet->getStyle('K3:K' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Statut
        $sheet->getStyle('L3:L' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Validée le

        return [
            // En-têtes de colonnes (Ligne 2)
            2 => [
                'font' => [
                    'bold' => true, 
                    'size' => 11, 
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2E75B6'] // Bleu plus clair
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = $this->entries->count() + 2; // Dernière ligne de données
                
                // Ligne de séparation avant les totaux
                $separatorRow = $lastDataRow + 1;
                
                // Ligne des totaux - les libellés au-dessus des valeurs
                $totalLabelsRow = $separatorRow + 1;
                $totalValuesRow = $totalLabelsRow + 1;
                
                // 1. Libellés des totaux (ligne au-dessus)
                $sheet->setCellValue("A{$totalLabelsRow}", "TOTAL");
                $sheet->setCellValue("E{$totalLabelsRow}", "Heures réelles");
                $sheet->setCellValue("F{$totalLabelsRow}", "Heures théoriques");
                $sheet->setCellValue("G{$totalLabelsRow}", "Écart total");
                $sheet->setCellValue("H{$totalLabelsRow}", "Taux moyen");
                $sheet->setCellValue("I{$totalLabelsRow}", "Nombre de jours");
                
                // 2. Valeurs des totaux (ligne en dessous)
                $sheet->setCellValue("E{$totalValuesRow}", number_format($this->totalHeuresReelles, 2));
                $sheet->setCellValue("F{$totalValuesRow}", number_format($this->totalHeuresTheoriques, 2));
                
                $ecartTotal = $this->totalHeuresReelles - $this->totalHeuresTheoriques;
                $sheet->setCellValue("G{$totalValuesRow}", number_format($ecartTotal, 2));
                
                $tauxMoyen = $this->totalHeuresTheoriques > 0 ? 
                            ($this->totalHeuresReelles / $this->totalHeuresTheoriques) * 100 : 0;
                $sheet->setCellValue("H{$totalValuesRow}", number_format($tauxMoyen, 1) . '%');
                
                $sheet->setCellValue("I{$totalValuesRow}", $this->nombreJours);
                
                // Style des libellés des totaux
                $sheet->getStyle("A{$totalLabelsRow}:I{$totalLabelsRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF2F2F2'] // Gris clair
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
                
                // Style des valeurs des totaux
                $sheet->getStyle("A{$totalValuesRow}:I{$totalValuesRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE2EFDA'] // Vert clair
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF95B3D7'],
                        ],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
                
                // Format numérique pour les valeurs
                $sheet->getStyle("E{$totalValuesRow}:G{$totalValuesRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                
                // Statistiques par statut (en dessous des totaux)
                $statsStartRow = $totalValuesRow + 2;
                $sheet->setCellValue("A{$statsStartRow}", "STATISTIQUES PAR STATUT");
                $sheet->mergeCells("A{$statsStartRow}:D{$statsStartRow}");
                $sheet->getStyle("A{$statsStartRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
                
                // Calculer les statistiques par statut
                $statsByStatus = [];
                foreach ($this->entries as $entry) {
                    $status = $entry->statut;
                    if (!isset($statsByStatus[$status])) {
                        $statsByStatus[$status] = [
                            'count' => 0,
                            'total_heures' => 0
                        ];
                    }
                    $statsByStatus[$status]['count']++;
                    $statsByStatus[$status]['total_heures'] += $entry->heures_reelles;
                }
                
                // Afficher les statistiques
                $statsRow = $statsStartRow + 1;
                foreach ($statsByStatus as $status => $data) {
                    $sheet->setCellValue("A{$statsRow}", ucfirst($status) . ":");
                    $sheet->setCellValue("B{$statsRow}", $data['count'] . " jour(s)");
                    $sheet->setCellValue("C{$statsRow}", number_format($data['total_heures'], 2) . " heures");
                    
                    // Style des statistiques
                    $sheet->getStyle("A{$statsRow}:C{$statsRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFCE4D6'] // Orange clair
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFE2EFDA'],
                            ],
                        ],
                    ]);
                    
                    $statsRow++;
                }
                
                // Date d'export
                $exportDateRow = $statsRow + 1;
                $sheet->setCellValue("M{$exportDateRow}", "Exporté le " . now()->format('d/m/Y H:i'));
                $sheet->getStyle("M{$exportDateRow}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
                ]);
                
                // Ajuster automatiquement la hauteur des lignes avec activités
                for ($row = 3; $row <= $lastDataRow; $row++) {
                    $activites = $sheet->getCell("I{$row}")->getValue();
                    if ($activites && strpos($activites, "\n") !== false) {
                        $lineCount = substr_count($activites, "\n") + 1;
                        $sheet->getRowDimension($row)->setRowHeight(20 * $lineCount);
                    }
                }
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_00, // Heures Réelles
            'F' => NumberFormat::FORMAT_NUMBER_00, // Heures Théoriques
            'G' => NumberFormat::FORMAT_NUMBER_00, // Écart
            'H' => NumberFormat::FORMAT_PERCENTAGE_00, // Taux
        ];
    }
}