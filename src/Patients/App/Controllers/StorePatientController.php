<?php

declare(strict_types=1);

namespace Lightit\Patients\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Patients\App\Requests\UpsertPatientRequest;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Patients\Domain\Actions\UpsertPatientAction;

#[Group('Patients')]
final readonly class StorePatientController
{
    #[Endpoint(
        operationId: 'storePatient',
        title: 'Create a patient',
        description: 'Creates a new patient.'
    )]
    public function __invoke(UpsertPatientRequest $request, UpsertPatientAction $upsertPatientAction): JsonResponse
    {
        $patient = $upsertPatientAction->execute($request->toDto());

        return PatientResource::make($patient)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }
}
