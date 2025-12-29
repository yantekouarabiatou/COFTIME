<?php

namespace App\Exports;

use App\Models\User;
use App\Models\DailyEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class UserTimesSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $user;
    protected $dateDebut;
    protected $dateFin;

    public function __construct(User $user, $dateDebut, $dateFin)
    {
        $this->user = $user;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function title(): string
    {
        return $this->user->full_name ?? $this->user->nom . ' ' . $this->user->prenom;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Jour',
            'Heures Théoriques',
            'Heures Réelles',
            'Écart',
            'Dossier',
            'Client',
            'Heures sur Dossier',
            'Début',
            'Fin',
            'Travaux',
            'Commentaire Général',
        ];
    }

    public function collection()
    {
        $entries = DailyEntry::with(['timeEntries.dossier.client'])
            ->where('user_id', $this->user->id)
            ->whereBetween('jour', [$this->dateDebut, $this->dateFin])
            ->orderBy('jour')
            ->get();

        $rows = [];

        foreach ($entries as $entry) {
            if ($entry->timeEntries->isEmpty()) {
                $rows[] = [
                    $entry->jour->format('d/m/Y'),
                    \Carbon\Carbon::parse($entry->jour)->translatedFormat('l'),
                    $entry->heures_theoriques,
                    $entry->heures_totales,
                    $entry->heures_totales - $entry->heures_theoriques,
                    '-', '-', 0, '-', '-', '-',
                    $entry->commentaire ?? '-',
                ];
            }

            foreach ($entry->timeEntries as $te) {
                $rows[] = [
                    $entry->jour->format('d/m/Y'),
                    \Carbon\Carbon::parse($entry->jour)->translatedFormat('l'),
                    $entry->heures_theoriques,
                    $entry->heures_totales,
                    $entry->heures_totales - $entry->heures_theoriques,
                    $te->dossier->nom ?? '-',
                    $te->dossier->client->nom ?? '-',
                    $te->heures,
                    $te->heure_debut,
                    $te->heure_fin,
                    $te->travaux ?? '-',
                    $entry->commentaire ?? '-',
                ];
            }
        }

        return collect($rows);
    }
}
