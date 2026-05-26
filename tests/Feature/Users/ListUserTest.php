<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use Database\Factories\UserFactory;
use Lightit\Users\App\Controllers\StoreUserController;
use function Pest\Laravel\getJson;

const USERS_TO_CREATE = 5;

describe('users', function (): void {
    /** @see StoreUserController */
    it('can list users successfully', function (): void {
        $users = UserFactory::new()
            ->createMany(USERS_TO_CREATE);

        getJson(url('/api/users'))
            ->assertSuccessful()
            ->assertJsonCount(USERS_TO_CREATE, 'data');
    });
});
