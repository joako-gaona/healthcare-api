<?php

declare(strict_types=1);

namespace Lightit\Authentication\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Lightit\Authentication\Domain\Actions\LogoutAction;

#[Group('Authentication')]
final readonly class LogoutController
{
    #[Endpoint(
        operationId: 'logout',
        title: 'Log out',
        description: 'Invalidates the current JWT access token.'
    )]
    public function __invoke(LogoutAction $logoutAction): Response
    {
        $logoutAction->execute();

        return response()->noContent();
    }
}
