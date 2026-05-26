<?php

declare(strict_types=1);

use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Route;
use Lightit\Authentication\App\Controllers\LoginController;
use Lightit\Authentication\App\Controllers\LogoutController;
use Lightit\Authentication\App\Controllers\RefreshController;
use Lightit\Users\App\Controllers\DeleteUserController;
use Lightit\Users\App\Controllers\GetUserController;
use Lightit\Users\App\Controllers\ListUserController;
use Lightit\Users\App\Controllers\StoreUserController;
use Lightit\Users\App\Controllers\UpdateUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(static function (): void {
    Route::post('login', LoginController::class);

    Route::middleware('auth:api')->group(static function (): void {
        Route::post('logout', LogoutController::class);
        Route::post('refresh', RefreshController::class);

        Route::get('me', fn(
            #[CurrentUser] $user
        ) => response()->json([
            'data' => $user,
        ]));
    });
});

/*
|--------------------------------------------------------------------------
| Users Routes
|--------------------------------------------------------------------------
*/
Route::prefix('users')
    ->group(static function (): void {
        Route::get('/', ListUserController::class);
        Route::post('/', StoreUserController::class);
        Route::prefix('{user}')->group(static function (): void {
            Route::get('/', GetUserController::class)->withTrashed();
            Route::put('/', UpdateUserController::class);
            Route::delete('/', DeleteUserController::class);
        })->whereNumber('user');
    });
