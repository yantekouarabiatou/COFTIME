<?php

namespace App\Exports;

use App\Models\User;
use App\Models\DailyEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TimesExport implements WithMultipleSheets
{
    protected $dateDebut;
    protected $dateFin;

    public function __construct($dateDebut, $dateFin)
    {
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function sheets(): array
    {
        $sheets = [];
        $users = User::orderBy('nom')->get();

        foreach ($users as $user) {
            $sheets[] = new UserTimesSheet($user, $this->dateDebut, $this->dateFin);
        }

        return $sheets;
    }
}
