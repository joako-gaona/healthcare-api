<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Hash;
use Lightit\Patients\App\Controllers\UpdatePatientController;
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
            'name' => 'Updated patient',
            'password' => '>e$pV4chNFcJoAB%X#{',
            'password_confirmation' => '>e$pV4chNFcJoAB%X#{',
        ]);

        $response = putJson(url("/api/patients/$patient->id"), $data);

        $patient = User::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = PatientResource::make($patient)
            ->response()
            ->getData(true);

        $response
            ->assertOk()
            ->assertJsonPath('data', $resourceResponse['data']);

        assertDatabaseHas(User::class, [
            'id' => $patient->id,
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        expect(Hash::check('>e$pV4chNFcJoAB%X#{', $patient->password))->toBeTrue();
    });

    it('can update a patient without changing its email', function (): void {
        $patient = UserFactory::new()->createOne([
            'email' => 'patient@example.com',
        ]);

        $data = StorePatientRequestFactory::new()->create([
            'name' => 'Updated patient',
            'email' => $patient->email,
        ]);

        $response = putJson(url("/api/patients/$patient->id"), $data);

        $response->assertOk()
            ->assertJsonPath('data.email', $patient->email);

        assertDatabaseHas(User::class, [
            'id' => $patient->id,
            'name' => $data['name'],
            'email' => $patient->email,
        ]);
    });

    it('cannot update a patient with invalid data', function (): void {
        $existingPatient = UserFactory::new()->createOne();

        $data = [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ];

        $response = putJson(url("/api/patients/$existingPatient->id"), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password'], 'error.fields');
    });
});
