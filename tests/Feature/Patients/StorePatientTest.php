<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lightit\Patients\App\Controllers\StorePatientController;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Users\Domain\Models\User;
use Tests\RequestFactories\StorePatientRequestFactory;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

function getLongPatientValue(int $length): string
{
    return Str::repeat(string: 'a', times: $length);
}

dataset(name: 'patient-validation-rules', dataset: [
    'name is required' => ['name', '', 'name'],
    'name must be a string' => ['name', ['array'], 'name'],
    'name must not be too short' => ['name', 'abc', 'name'],
    'name must not be too long' => ['name', getLongPatientValue(81), 'name'],

    'email is required' => ['email', '', 'email'],
    'email must be valid' => ['email', 'invalid-email', 'email'],
    'email must not be too long' => ['email', getLongPatientValue(101) . '@example.com', 'email'],

    'password is required' => ['password', '', 'password'],
    'password must be confirmed' => ['password_confirmation', 'different-password', 'password'],
]);

describe('patients', function (): void {
    /** @see StorePatientController */
    it(description: 'can create a patient successfully', closure: function (): void {
        $data = StorePatientRequestFactory::new()->create([
            'password' => '>e$pV4chNFcJoAB%X#{',
            'password_confirmation' => '>e$pV4chNFcJoAB%X#{',
        ]);

        $response = postJson(url('/api/patients'), $data);

        $patient = User::query()
            ->where('email', $data['email'])
            ->firstOrFail();

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = PatientResource::make($patient)
            ->response()
            ->getData(true);

        $response
            ->assertCreated()
            ->assertJsonPath('data', $resourceResponse['data']);

        assertDatabaseHas(User::class, [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        expect(Hash::check('>e$pV4chNFcJoAB%X#{', $patient->password))->toBeTrue();
    });

    it(description: 'cannot create a patient with an already registered email', closure: function (): void {
        $existingPatient = UserFactory::new()->createOne();

        $data = StorePatientRequestFactory::new()->create([
            'email' => $existingPatient->email,
        ]);

        $response = postJson(url('/api/patients'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email'], 'error.fields');

        assertDatabaseMissing(User::class, [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    });

    it(
        'cannot create a patient with invalid data',
        function (string $field, string|array $value, string $errorField): void {
            $data = StorePatientRequestFactory::new()->create();

            $response = postJson(url('/api/patients'), [...$data, $field => $value]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors([$errorField], 'error.fields');
        }
    )->with('patient-validation-rules');
});
