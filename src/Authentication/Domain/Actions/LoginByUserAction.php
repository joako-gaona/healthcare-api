<?php

declare(strict_types=1);

namespace Lightit\Authentication\Domain\Actions;

use Carbon\Constants\UnitValue;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Lightit\Authentication\Domain\DataTransferObjects\LoginDto;
use Lightit\Users\Domain\Models\User;
use PHPOpenSourceSaver\JWTAuth\Factory as JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

final readonly class LoginByUserAction
{
    public function __construct(
        private AuthFactory $factory,
        private JWTAuth $jwtAuth,
    ) {
    }

    public function execute(User $user): LoginDto
    {
        /** @var JWTGuard $guard */
        $guard = $this->factory->guard('api');

        /** @var string $token */
        $token = $guard->tokenById(id: $user->getKey());

        return new LoginDto(
            accessToken: $token,
            tokenType: 'Bearer',
            expiresIn: $this->jwtAuth->getTTL() * UnitValue::SECONDS_PER_MINUTE,
        );
    }
}
