<?php

namespace App\Console\Commands;

use App\Models\DailyEntry;
use Illuminate\Console\Command;

class RecalculerHeuresReelles extends Command
{
    protected $signature = 'heures:recalculer';
    protected $description = 'Recalcule toutes les heures réelles des DailyEntry';

    public function handle()
    {
        $this->info('Début du recalcul des heures...');

        $dailyEntries = DailyEntry::with('timeEntries')->get();
        $progressBar = $this->output->createProgressBar($dailyEntries->count());

        foreach ($dailyEntries as $entry) {
            $total = $entry->timeEntries->sum('heures_reelles');
            $entry->updateQuietly(['heures_reelles' => $total]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('✓ Recalcul terminé avec succès !');

        return Command::SUCCESS;
    }
}

// Pour exécuter la commande :
// php artisan heures:recalculer
