<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\UserFactory;
use Lightit\Patients\App\Controllers\ListPatientController;
use function Pest\Laravel\getJson;

const PATIENTS_TO_CREATE = 5;

describe('patients', function (): void {
    /** @see ListPatientController */
    it('can list patients successfully', function (): void {
        UserFactory::new()
            ->createMany(PATIENTS_TO_CREATE);

        getJson(url('/api/patients'))
            ->assertSuccessful()
            ->assertJsonCount(PATIENTS_TO_CREATE, 'data');
    });
});
