<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Factories\DoctorFactory;
use Illuminate\Database\Seeder;
use Lightit\Clinics\Domain\Models\Clinic;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            DoctorFactory::new()
                ->hasAttached(
                    Clinic::query()
                        ->inRandomOrder()
                        ->take(random_int(1, 3))
                        ->get(),
                    relationship: 'clinics',
                )
                ->create();
        }
    }
}
