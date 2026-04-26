<?php

namespace Modules\Auth\Infrastructure\Repositories;

use Modules\Auth\Domain\Contracts\UserRepositoryContract;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\ValueObjects\Email;
use Modules\Auth\Domain\ValueObjects\HashedPassword;
use Modules\Auth\Infrastructure\Models\UserModel;
use Modules\Shared\Domain\ValueObjects\UserId;

final class EloquentUserRepository implements UserRepositoryContract
{
    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::where('email', $email->value())->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findById(UserId $id): ?User
    {
        $model = UserModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    // Map Eloquent → Domain Entity
    // Roles/permissions are NOT loaded here — that's RoleRepository's job
    private function toDomain(UserModel $model): User
    {
        return new User(
            id:              new UserId($model->id),
            email:           new Email($model->email),
            password:        new HashedPassword($model->password),
            isActive:        $model->is_active,
            isEmailVerified: $model->email_verified_at !== null,
        );
    }
}
