<?php

declare(strict_types=1);

namespace Lightit\Users\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Users\App\Requests\UpsertUserRequest;
use Lightit\Users\App\Resources\UserResource;
use Lightit\Users\Domain\Actions\UpsertUserAction;
use Lightit\Users\Domain\Models\User;

#[Group('Users')]
final readonly class UpdateUserController
{
    #[Endpoint(
        operationId: 'updateUser',
        title: 'Update a user',
        description: 'Updates an existing user.'
    )]
    public function __invoke(User $user, UpsertUserRequest $request, UpsertUserAction $upsertUserAction): JsonResponse
    {
        $user = $upsertUserAction->execute($request->toDto(), $user);

        return UserResource::make($user)
            ->response();
    }
}
