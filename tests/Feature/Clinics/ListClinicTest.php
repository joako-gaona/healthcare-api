<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Lightit\Clinics\App\Controllers\ListClinicController;
use function Pest\Laravel\getJson;

const CLINICS_TO_CREATE = 5;

describe('clinics', function (): void {
    /** @see ListClinicController */
    it('can list clinics successfully', function (): void {
        ClinicFactory::new()
            ->createMany(CLINICS_TO_CREATE);

        getJson(url('/api/clinics'))
            ->assertSuccessful()
            ->assertJsonCount(CLINICS_TO_CREATE, 'data');
    });
});
