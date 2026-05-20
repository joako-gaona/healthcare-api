<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Clinics\App\Requests\UpsertClinicRequest;
use Lightit\Clinics\App\Resources\ClinicResource;
use Lightit\Clinics\Domain\Actions\UpsertClinicAction;
use Lightit\Clinics\Domain\Models\Clinic;

#[Group('Clinics')]
final readonly class UpdateClinicController
{
    #[Endpoint(
        operationId: 'updateClinic',
        title: 'Update a clinic',
        description: 'Updates an existing clinic.'
    )]
    public function __invoke(
        Clinic $clinic,
        UpsertClinicRequest $request,
        UpsertClinicAction $upsertClinicAction,
    ): JsonResponse {
        $clinic = $upsertClinicAction->execute($request->toDto(), $clinic);

        $clinic->loadCount('doctors');

        return ClinicResource::make($clinic)
            ->response();
    }
}
