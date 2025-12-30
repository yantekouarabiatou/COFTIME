<?php

namespace App\Exports;

use App\Models\DailyEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailyEntriesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return DailyEntry::with(['user', 'timeEntries.dossier.client'])
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Collaborateur',
            'Heures théoriques',
            'Heures réelles',
            'Nb activités',
            'Commentaire',
            'Statut',
            'Créée le',
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->jour->format('d/m/Y'),
            $entry->user->prenom . ' ' . $entry->user->nom,
            $entry->heures_theoriques,
            $entry->heures_reelles,
            $entry->timeEntries->count(),
            $entry->commentaire ?? 'Aucun',
            ucfirst($entry->statut),
            $entry->created_at->format('d/m/Y H:i'),
        ];
    }
}
