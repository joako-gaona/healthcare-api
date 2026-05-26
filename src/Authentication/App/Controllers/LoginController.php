<?php

declare(strict_types=1);

namespace Lightit\Authentication\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Authentication\App\Requests\LoginRequest;
use Lightit\Authentication\App\Resources\LoginResource;
use Lightit\Authentication\Domain\Actions\LoginAction;

#[Group('Authentication')]
final readonly class LoginController
{
    #[Endpoint(
        operationId: 'login',
        title: 'Log in',
        description: 'Authenticates a user and returns a JWT access token.'
    )]
    public function __invoke(LoginRequest $request, LoginAction $loginAction): JsonResponse
    {
        $loginDto = $loginAction->execute($request->toDto());

        return LoginResource::make($loginDto)
            ->response();
    }
}
