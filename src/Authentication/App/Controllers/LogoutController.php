<?php

declare(strict_types=1);

namespace Lightit\Authentication\App\Controllers;

use Illuminate\Http\Response;
use Lightit\Authentication\Domain\Actions\LogoutAction;

class LogoutController
{
    public function __invoke(LogoutAction $logoutAction): Response
    {
        $logoutAction->execute();

        return response()->noContent();
    }
}
