<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\UserFactory;
use Lightit\Patients\App\Controllers\DeletePatientController;
use Lightit\Users\Domain\Models\User;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

describe('patients', function (): void {
    /** @see DeletePatientController */
    it('deletes a patient and returns a successful response', function (): void {
        $existingPatient = UserFactory::new()->createOne();

        $response = deleteJson("api/patients/$existingPatient->id");

        $response->assertNoContent();

        assertDatabaseMissing(User::class, ['id' => $existingPatient->id]);
    });

    it('returns a 404 response when patient is not found', function (): void {
        $nonExistentPatientId = 99999;

        deleteJson("api/patients/{$nonExistentPatientId}")->assertNotFound();
    });
});
