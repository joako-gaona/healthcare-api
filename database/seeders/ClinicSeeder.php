<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Factories\ClinicFactory;
use Illuminate\Database\Seeder;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        ClinicFactory::new()
            ->count(10)
            ->create();
    }
}
