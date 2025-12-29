<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        // Logo par défaut de COFIMA
        $source = public_path('assets/img/logo_cofima_bon.jpg');
        $destination = 'company/logo_cofima_bon.jpg';

        if (file_exists($source) && !Storage::disk('public')->exists($destination)) {
            Storage::disk('public')->put(
                $destination,
                file_get_contents($source)
            );
        }

        CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'COFIMA',
                'slogan' => "Compagnie de Fiduciaire de Microfinance et d'Audits",
                'email' => 'cofima@cofima.cc',
                'telephone' => '+229 01 21 38 04 58',
                'adresse' => 'C/2197 Immeuble Luca Pacioli, Kouhounou Cotonou, Benin',
                'ville' => 'Cotonou',
                'pays' => 'Bénin',
                'site_web' => 'https://www.cofima.cc',
                'logo' => $destination,
            ]
        );
    }
}
