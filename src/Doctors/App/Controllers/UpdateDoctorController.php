<?php

declare(strict_types=1);

namespace Lightit\Doctors\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Doctors\App\Requests\UpsertDoctorRequest;
use Lightit\Doctors\App\Resources\DoctorResource;
use Lightit\Doctors\Domain\Actions\UpsertDoctorAction;
use Lightit\Doctors\Domain\Models\Doctor;

#[Group('Doctors')]
final readonly class UpdateDoctorController
{
    #[Endpoint(
        operationId: 'updateDoctor',
        title: 'Update a doctor',
        description: 'Updates an existing doctor.'
    )]
    public function __invoke(
        Doctor $doctor,
        UpsertDoctorRequest $request,
        UpsertDoctorAction $upsertDoctorAction,
    ): JsonResponse {
        $doctor = $upsertDoctorAction->execute($request->toDto(), $doctor);

        return DoctorResource::make($doctor)
            ->response();
    }
}
