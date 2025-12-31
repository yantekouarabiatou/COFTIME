<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\TimeEntry;

class BelongsToDailyEntry implements Rule
{
    protected $dailyEntryId;

    public function __construct($dailyEntryId)
    {
        $this->dailyEntryId = $dailyEntryId;
    }

    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true; // Les nouvelles entrées n'ont pas d'ID
        }

        // Vérifie que l'TimeEntry appartient bien au DailyEntry
        return TimeEntry::where('id', $value)
            ->where('daily_entry_id', $this->dailyEntryId)
            ->exists();
    }

    public function message()
    {
        return 'Cette activité de temps ne fait pas partie de cette feuille de temps.';
    }
}
