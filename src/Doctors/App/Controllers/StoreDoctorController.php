<?php

declare(strict_types=1);

namespace Lightit\Doctors\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Doctors\App\Requests\UpsertDoctorRequest;
use Lightit\Doctors\App\Resources\DoctorResource;
use Lightit\Doctors\Domain\Actions\UpsertDoctorAction;

#[Group('Doctors')]
final readonly class StoreDoctorController
{
    #[Endpoint(
        operationId: 'storeDoctor',
        title: 'Create a doctor',
        description: 'Creates a new doctor.'
    )]
    public function __invoke(UpsertDoctorRequest $request, UpsertDoctorAction $upsertDoctorAction): JsonResponse
    {
        $doctor = $upsertDoctorAction->execute($request->toDto());

        $doctor->load('clinics');

        return DoctorResource::make($doctor)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }
}
