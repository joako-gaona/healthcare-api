<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lightit\Patients\App\Controllers\StorePatientController;
use Lightit\Patients\App\Requests\UpsertPatientRequest;
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
    'name is required' => [UpsertPatientRequest::NAME, '', UpsertPatientRequest::NAME],
    'name must be a string' => [UpsertPatientRequest::NAME, ['array'], UpsertPatientRequest::NAME],
    'name must not be too short' => [UpsertPatientRequest::NAME, 'abc', UpsertPatientRequest::NAME],
    'name must not be too long' => [UpsertPatientRequest::NAME, getLongPatientValue(81), UpsertPatientRequest::NAME],

    'email is required' => [UpsertPatientRequest::EMAIL, '', UpsertPatientRequest::EMAIL],
    'email must be valid' => [UpsertPatientRequest::EMAIL, 'invalid-email', UpsertPatientRequest::EMAIL],
    'email must not be too long' => [
        UpsertPatientRequest::EMAIL,
        getLongPatientValue(101) . '@example.com',
        UpsertPatientRequest::EMAIL,
    ],

    'password is required' => [UpsertPatientRequest::PASSWORD, '', UpsertPatientRequest::PASSWORD],
    'password must be confirmed' => [
        UpsertPatientRequest::PASSWORD . '_confirmation',
        'different-password',
        UpsertPatientRequest::PASSWORD,
    ],
]);

describe('patients', function (): void {
    /** @see StorePatientController */
    it(description: 'can create a patient successfully', closure: function (): void {
        $data = StorePatientRequestFactory::new()->create();

        $response = postJson(url('/api/patients'), $data);

        $patient = User::query()
            ->where('email', $data[UpsertPatientRequest::EMAIL])
            ->firstOrFail();

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = PatientResource::make($patient)
            ->response()
            ->getData(true);

        $response
            ->assertCreated()
            ->assertJsonPath('data', $resourceResponse['data']);

        assertDatabaseHas(User::class, [
            'name' => $data[UpsertPatientRequest::NAME],
            'email' => $data[UpsertPatientRequest::EMAIL],
        ]);

        expect(Hash::check(StorePatientRequestFactory::VALID_PASSWORD, $patient->password))->toBeTrue();
    });

    it(description: 'cannot create a patient with an already registered email', closure: function (): void {
        $existingPatient = UserFactory::new()->createOne();

        $data = StorePatientRequestFactory::new()->create([
            UpsertPatientRequest::EMAIL => $existingPatient->email,
        ]);

        $response = postJson(url('/api/patients'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([UpsertPatientRequest::EMAIL], 'error.fields');

        assertDatabaseMissing(User::class, [
            'name' => $data[UpsertPatientRequest::NAME],
            'email' => $data[UpsertPatientRequest::EMAIL],
        ]);
    });

    it(description: 'cannot create a patient with a differently-cased registered email', closure: function (): void {
        UserFactory::new()->createOne([
            'email' => 'patient@example.com',
        ]);

        $data = StorePatientRequestFactory::new()->create([
            UpsertPatientRequest::NAME => 'Mixed case duplicate',
            UpsertPatientRequest::EMAIL => 'Patient@Example.com',
        ]);

        $response = postJson(url('/api/patients'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([UpsertPatientRequest::EMAIL], 'error.fields');

        assertDatabaseMissing(User::class, [
            'name' => $data[UpsertPatientRequest::NAME],
            'email' => 'patient@example.com',
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
