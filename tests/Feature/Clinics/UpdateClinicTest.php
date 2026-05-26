<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Lightit\Clinics\App\Controllers\UpdateClinicController;
use Lightit\Clinics\App\Resources\ClinicResource;
use Lightit\Clinics\Domain\Models\Clinic;
use Tests\RequestFactories\StoreClinicRequestFactory;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\putJson;

describe('clinics', function (): void {
    /** @see UpdateClinicController */
    it('can update a clinic successfully', function (): void {
        $clinic = ClinicFactory::new()->createOne([
            'name' => 'Old clinic',
        ]);

        $data = StoreClinicRequestFactory::new()->create([
            'name' => 'Updated clinic',
        ]);

        $response = putJson(url("/api/clinics/$clinic->id"), $data);

        $clinic = Clinic::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $clinic->loadCount('doctors');

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = ClinicResource::make($clinic)
            ->response()
            ->getData(true);

        $response
            ->assertOk()
            ->assertJsonPath('data', $resourceResponse['data']);

        assertDatabaseHas(Clinic::class, [
            'id' => $clinic->id,
            'name' => $data['name'],
            'address' => $data['address'],
        ]);
    });

    it('can update a clinic and sync assigned doctors', function (): void {
        $oldDoctors = DoctorFactory::new()
            ->count(2)
            ->create();
        $newDoctors = DoctorFactory::new()
            ->count(2)
            ->create();

        $clinic = ClinicFactory::new()->createOne([
            'name' => 'Old clinic',
        ]);

        /** @var list<int> $oldDoctorIds */
        $oldDoctorIds = $oldDoctors->pluck('id')->all();
        /** @var list<int> $newDoctorIds */
        $newDoctorIds = $newDoctors->pluck('id')->all();

        $clinic->doctors()->sync($oldDoctorIds);

        $data = StoreClinicRequestFactory::new()->create([
            'name' => 'Updated clinic with doctors',
            'doctor_ids' => $newDoctorIds,
        ]);

        $response = putJson(url("/api/clinics/$clinic->id"), $data);

        $clinic = Clinic::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $clinic->loadCount('doctors');

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = ClinicResource::make($clinic)
            ->response()
            ->getData(true);

        $response
            ->assertOk()
            ->assertJsonPath('data', $resourceResponse['data'])
            ->assertJsonPath('data.doctors_count', count($newDoctorIds));

        foreach ($newDoctorIds as $doctorId) {
            assertDatabaseHas('clinic_doctor', [
                'doctor_id' => $doctorId,
                'clinic_id' => $clinic->id,
            ]);
        }

        foreach ($oldDoctorIds as $doctorId) {
            assertDatabaseMissing('clinic_doctor', [
                'doctor_id' => $doctorId,
                'clinic_id' => $clinic->id,
            ]);
        }
    });

    it('keeps assigned doctors when doctor ids are omitted', function (): void {
        $doctors = DoctorFactory::new()
            ->count(2)
            ->create();

        $clinic = ClinicFactory::new()->createOne([
            'name' => 'Old clinic',
        ]);

        /** @var list<int> $doctorIds */
        $doctorIds = $doctors->pluck('id')->all();

        $clinic->doctors()->sync($doctorIds);

        $data = StoreClinicRequestFactory::new()->create([
            'name' => 'Updated clinic without doctor ids',
        ]);

        $response = putJson(url("/api/clinics/$clinic->id"), $data);

        $clinic = Clinic::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $response
            ->assertOk()
            ->assertJsonPath('data.doctors_count', count($doctorIds));

        foreach ($doctorIds as $doctorId) {
            assertDatabaseHas('clinic_doctor', [
                'doctor_id' => $doctorId,
                'clinic_id' => $clinic->id,
            ]);
        }
    });

    it('clears assigned doctors when doctor ids are empty', function (): void {
        $doctors = DoctorFactory::new()
            ->count(2)
            ->create();

        $clinic = ClinicFactory::new()->createOne([
            'name' => 'Old clinic',
        ]);

        /** @var list<int> $doctorIds */
        $doctorIds = $doctors->pluck('id')->all();

        $clinic->doctors()->sync($doctorIds);

        $data = StoreClinicRequestFactory::new()->create([
            'name' => 'Updated clinic without doctors',
            'doctor_ids' => [],
        ]);

        $response = putJson(url("/api/clinics/$clinic->id"), $data);

        $clinic = Clinic::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $response
            ->assertOk()
            ->assertJsonPath('data.doctors_count', 0);

        foreach ($doctorIds as $doctorId) {
            assertDatabaseMissing('clinic_doctor', [
                'doctor_id' => $doctorId,
                'clinic_id' => $clinic->id,
            ]);
        }
    });

    it('cannot update a clinic with invalid data', function (): void {
        $existingClinic = ClinicFactory::new()->createOne();

        $data = [
            'name' => '',
            'address' => '',
        ];

        $response = putJson(url("/api/clinics/$existingClinic->id"), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'address'], 'error.fields');
    });
});
