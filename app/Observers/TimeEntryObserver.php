<?php

namespace App\Observers;

use App\Models\TimeEntry;

class TimeEntryObserver
{
    public function created(TimeEntry $timeEntry)
    {
        $this->updateDailyTotal($timeEntry);
    }

    public function updated(TimeEntry $timeEntry)
    {
        $this->updateDailyTotal($timeEntry);
    }

    public function deleted(TimeEntry $timeEntry)
    {
        $this->updateDailyTotal($timeEntry);
    }

    protected function updateDailyTotal(TimeEntry $timeEntry)
    {
        $dailyEntry = $timeEntry->dailyEntry;
        $total = $dailyEntry->timeEntries()->sum('heures');

        $dailyEntry->update(['heures_totales' => $total]);
    }
}
