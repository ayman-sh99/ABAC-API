<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\Domain\Contracts\TokenServiceContract;
use Modules\Auth\Domain\Contracts\UserRepositoryContract;
use Modules\Auth\Infrastructure\Repositories\EloquentUserRepository;
use Modules\Auth\Infrastructure\Services\SanctumTokenService;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Auth module's own contracts
        $this->app->bind(UserRepositoryContract::class, EloquentUserRepository::class);
        $this->app->bind(TokenServiceContract::class, SanctumTokenService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Infrastructure/Database/Migrations');

        $this->loadRoutesFrom(__DIR__ . '/../Presentation/Routes/api.php');
    }
}
