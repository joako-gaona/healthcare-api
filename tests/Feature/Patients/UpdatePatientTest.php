<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Hash;
use Lightit\Patients\App\Controllers\UpdatePatientController;
use Lightit\Patients\App\Requests\UpsertPatientRequest;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Users\Domain\Models\User;
use Tests\RequestFactories\StorePatientRequestFactory;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\putJson;

describe('patients', function (): void {
    /** @see UpdatePatientController */
    it('can update a patient successfully', function (): void {
        $patient = UserFactory::new()->createOne([
            'name' => 'Old patient',
        ]);

        $data = StorePatientRequestFactory::new()->create([
            UpsertPatientRequest::NAME => 'Updated patient',
        ]);

        $response = putJson(url("/api/patients/$patient->id"), $data);

        $patient->refresh();

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = PatientResource::make($patient)
            ->response()
            ->getData(true);

        $response
            ->assertOk()
            ->assertJsonPath('data', $resourceResponse['data']);

        assertDatabaseHas(User::class, [
            'id' => $patient->id,
            'name' => $data[UpsertPatientRequest::NAME],
            'email' => $data[UpsertPatientRequest::EMAIL],
        ]);

        expect(Hash::check(StorePatientRequestFactory::VALID_PASSWORD, $patient->password))->toBeTrue();
    });

    it('can update a patient without changing its email', function (): void {
        $patient = UserFactory::new()->createOne([
            'email' => 'patient@example.com',
        ]);

        $data = StorePatientRequestFactory::new()->create([
            UpsertPatientRequest::NAME => 'Updated patient',
            UpsertPatientRequest::EMAIL => $patient->email,
        ]);

        $response = putJson(url("/api/patients/$patient->id"), $data);

        $response->assertOk()
            ->assertJsonPath('data.email', $patient->email);

        assertDatabaseHas(User::class, [
            'id' => $patient->id,
            'name' => $data[UpsertPatientRequest::NAME],
            'email' => $patient->email,
        ]);
    });

    it('cannot update a patient with invalid data', function (): void {
        $existingPatient = UserFactory::new()->createOne();

        $data = [
            UpsertPatientRequest::NAME => '',
            UpsertPatientRequest::EMAIL => 'not-an-email',
            UpsertPatientRequest::PASSWORD => 'short',
        ];

        $response = putJson(url("/api/patients/$existingPatient->id"), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                UpsertPatientRequest::NAME,
                UpsertPatientRequest::EMAIL,
                UpsertPatientRequest::PASSWORD,
            ], 'error.fields');
    });
});
