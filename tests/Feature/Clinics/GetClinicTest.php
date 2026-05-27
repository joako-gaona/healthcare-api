<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Lightit\Clinics\App\Controllers\GetClinicController;
use Lightit\Clinics\App\Resources\ClinicResource;
use function Pest\Laravel\getJson;

describe('clinics', function (): void {
    /** @see GetClinicController */
    it('retrieves a clinic and returns a successful response', function (): void {
        $existingClinic = ClinicFactory::new()->createOne();

        $existingClinic->loadCount('doctors');

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = ClinicResource::make($existingClinic)
            ->response()
            ->getData(true);

        getJson("api/clinics/$existingClinic->id")
            ->assertOk()
            ->assertJsonPath('data', $resourceResponse['data']);
    });

    it('returns a 404 response when clinic is not found', function (): void {
        $nonExistentClinicId = 99999;

        getJson("api/clinics/{$nonExistentClinicId}")->assertNotFound();
    });
});
