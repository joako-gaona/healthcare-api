<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Lightit\Doctors\App\Controllers\UpdateDoctorController;
use Lightit\Doctors\App\Resources\DoctorResource;
use Lightit\Doctors\Domain\Models\Doctor;
use Tests\RequestFactories\StoreDoctorRequestFactory;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\putJson;

describe('doctors', function (): void {
    /** @see UpdateDoctorController */
    it('can update a doctor successfully', function (): void {
        $oldClinics = ClinicFactory::new()
            ->count(2)
            ->create();

        $doctor = DoctorFactory::new()->createOne([
            'name' => 'Old doctor',
        ]);

        /** @var list<int> $oldClinicIds */
        $oldClinicIds = $oldClinics->pluck('id')->all();

        $doctor->clinics()->sync($oldClinicIds);

        /** @var array{name: string, clinic_ids: list<int>} $data */
        $data = StoreDoctorRequestFactory::new()->create([
            'name' => 'Updated doctor',
        ]);

        $response = putJson(url("/api/doctors/$doctor->id"), $data);

        $doctor = Doctor::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $doctor->load('clinics');

        /** @var array{data: array<string, mixed>} $resourceResponse */
        $resourceResponse = DoctorResource::make($doctor)
            ->response()
            ->getData(true);

        $response
            ->assertOk()
            ->assertJsonPath('data', $resourceResponse['data']);

        assertDatabaseHas(Doctor::class, [
            'id' => $doctor->id,
            'name' => $data['name'],
        ]);

        foreach ($data['clinic_ids'] as $clinicId) {
            assertDatabaseHas('clinic_doctor', [
                'doctor_id' => $doctor->id,
                'clinic_id' => $clinicId,
            ]);
        }

        foreach ($oldClinicIds as $oldClinicId) {
            assertDatabaseMissing('clinic_doctor', [
                'doctor_id' => $doctor->id,
                'clinic_id' => $oldClinicId,
            ]);
        }
    });

    it('cannot update a doctor with invalid data', function (): void {
        $existingDoctor = DoctorFactory::new()->createOne();

        $data = [
            'name' => '',
            'clinic_ids' => [],
        ];

        $response = putJson(url("/api/doctors/$existingDoctor->id"), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'clinic_ids'], 'error.fields');
    });
});
