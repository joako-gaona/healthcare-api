<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\UserFactory;
use Lightit\Patients\App\Controllers\GetPatientController;
use Lightit\Patients\App\Resources\PatientResource;
use function Pest\Laravel\getJson;

describe('patients', function (): void {
    /** @see GetPatientController */
    it('retrieves a patient and returns a successful response', function (): void {
        $existingPatient = UserFactory::new()->createOne();

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = PatientResource::make($existingPatient)
            ->response()
            ->getData(true);

        getJson("api/patients/$existingPatient->id")
            ->assertOk()
            ->assertJsonPath('data', $resourceResponse['data']);
    });

    it('returns a 404 response when patient is not found', function (): void {
        $nonExistentPatientId = 99999;

        getJson("api/patients/{$nonExistentPatientId}")->assertNotFound();
    });
});
