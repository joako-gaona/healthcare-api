<?php

declare(strict_types=1);

namespace Lightit\Patients\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Patients\App\Requests\UpsertPatientRequest;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Patients\Domain\Actions\UpsertPatientAction;
use Lightit\Users\Domain\Models\User;

#[Group('Patients')]
final readonly class UpdatePatientController
{
    #[Endpoint(
        operationId: 'updatePatient',
        title: 'Update a patient',
        description: 'Updates an existing patient.'
    )]
    public function __invoke(
        User $patient,
        UpsertPatientRequest $request,
        UpsertPatientAction $upsertPatientAction,
    ): JsonResponse {
        $patient = $upsertPatientAction->execute($request->toDto(), $patient);

        return PatientResource::make($patient)
            ->response();
    }
}
