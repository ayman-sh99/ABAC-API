<?php

namespace Modules\Authorization\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Authorization\Domain\Contracts\RoleRepositoryContract;
use Modules\Authorization\Infrastructure\Repositories\EloquentRoleRepository;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Authorization module binds its own contracts
        // Auth module's LoginAction depends on RoleRepositoryContract
        // but NEVER imports this provider or EloquentRoleRepository directly
        $this->app->bind(RoleRepositoryContract::class, EloquentRoleRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Infrastructure/Database/Migrations');
    }
}
