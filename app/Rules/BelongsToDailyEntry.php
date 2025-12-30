<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BelongsToDailyEntry implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
    }

    protected $dailyEntryId;

    public function __construct($dailyEntryId)
    {
        $this->dailyEntryId = $dailyEntryId;
    }

    public function passes($attribute, $value)
    {
        // $attribute = time_entries.0.id par exemple
        // $value = l'ID du time_entry

        if (!$value) return true; // nullable

        return \App\Models\TimeEntry::where('id', $value)
            ->where('daily_entry_id', $this->dailyEntryId)
            ->exists();
    }

    public function message()
    {
        return 'L\'activité sélectionnée n\'appartient pas à cette feuille de temps.';
    }
}
