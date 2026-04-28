<?php

namespace Modules\Authorization\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Authorization\Application\Actions\CheckPermissionAction;
use Modules\Authorization\Domain\Contracts\PermissionRepositoryContract;
use Modules\Authorization\Domain\Contracts\PolicyEngineContract;
use Modules\Authorization\Domain\Contracts\RoleRepositoryContract;
use Modules\Authorization\Domain\Services\PolicyEngine;
use Modules\Authorization\Domain\ValueObjects\ResourceAttributes;
use Modules\Authorization\Infrastructure\Repositories\EloquentPermissionRepository;
use Modules\Authorization\Infrastructure\Repositories\EloquentRoleRepository;
use Modules\Authorization\Presentation\Middleware\AbacMiddleware;
use Modules\Authorization\Presentation\Middleware\FieldGuardMiddleware;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Authorization module binds its own contracts
        // Auth module's LoginAction depends on RoleRepositoryContract
        // but NEVER imports this provider or EloquentRoleRepository directly
        $this->app->bind(RoleRepositoryContract::class, EloquentRoleRepository::class);

        $this->app->bind(PermissionRepositoryContract::class, EloquentPermissionRepository::class);
        $this->app->bind(PolicyEngineContract::class, PolicyEngine::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Infrastructure/Database/Migrations');


        // Register middleware alias so routes can use ->middleware('abac:posts:edit')
        $this->app['router']->aliasMiddleware('abac', AbacMiddleware::class);
        $this->app['router']->aliasMiddleware('field.guard',  FieldGuardMiddleware::class);

        // Fix: for testing only
        $this->loadRoutesFrom(__DIR__ . '/../Presentation/Routes/api.php');


        // Gate integration — $user->can('posts:edit', $post) works everywhere in Laravel
        Gate::before(function ($user, string $ability, array $arguments = []) {
            $resource = isset($arguments[0]) && is_object($arguments[0])
                ? ResourceAttributes::from($arguments[0]->toArray())
                : ResourceAttributes::empty();

            $action = $this->app->make(CheckPermissionAction::class);

            $output = $action->execute(new \Modules\Authorization\Application\DTOs\CheckPermissionInputDTO(
                userId:         new \Modules\Shared\Domain\ValueObjects\UserId($user->id),
                permissionName: $ability,
                resource:       $resource,
            ));

            // Return true/false explicitly (not null) so Gate uses our result
            return $output->allowed;
        });
    }
}
