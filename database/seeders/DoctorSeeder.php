<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Factories\DoctorFactory;
use Illuminate\Database\Seeder;
use Lightit\Clinics\Domain\Models\Clinic;
use Lightit\Doctors\Domain\Models\Doctor;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        DoctorFactory::new()
            ->count(10)
            ->create()
            ->each(static function (Doctor $doctor): void {
                $clinicIds = Clinic::query()
                    ->inRandomOrder()
                    ->take(random_int(1, 3))
                    ->pluck('id')
                    ->all();

                $doctor->clinics()->sync($clinicIds);
            });
    }
}
