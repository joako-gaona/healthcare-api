<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Lightit\Doctors\App\Controllers\ListDoctorController;
use function Pest\Laravel\getJson;

const DOCTORS_TO_CREATE = 5;

describe('doctors', function (): void {
    /** @see ListDoctorController */
    it('can list doctors successfully', function (): void {
        $clinics = ClinicFactory::new()
            ->count(2)
            ->create();

        /** @var list<int> $clinicIds */
        $clinicIds = $clinics->pluck('id')->all();

        DoctorFactory::new()
            ->count(DOCTORS_TO_CREATE)
            ->create()
            ->each(fn ($doctor) => $doctor->clinics()->sync($clinicIds));

        getJson(url('/api/doctors'))
            ->assertSuccessful()
            ->assertJsonCount(DOCTORS_TO_CREATE, 'data');
    });
});
