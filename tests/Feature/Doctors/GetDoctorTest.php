<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Lightit\Doctors\App\Controllers\GetDoctorController;
use Lightit\Doctors\App\Resources\DoctorResource;
use function Pest\Laravel\getJson;

describe('doctors', function (): void {
    /** @see GetDoctorController */
    it('retrieves a doctor and returns a successful response', function (): void {
        $clinics = ClinicFactory::new()
            ->count(2)
            ->create();

        $existingDoctor = DoctorFactory::new()->createOne();

        /** @var list<int> $clinicIds */
        $clinicIds = $clinics->pluck('id')->all();

        $existingDoctor->clinics()->sync($clinicIds);
        $existingDoctor->load('clinics');

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = DoctorResource::make($existingDoctor)
            ->response()
            ->getData(true);

        getJson("api/doctors/$existingDoctor->id")
            ->assertOk()
            ->assertJsonPath('data', $resourceResponse['data']);
    });

    it('returns a 404 response when doctor is not found', function (): void {
        $nonExistentDoctorId = 99999;

        getJson("api/doctors/{$nonExistentDoctorId}")->assertNotFound();
    });
});
