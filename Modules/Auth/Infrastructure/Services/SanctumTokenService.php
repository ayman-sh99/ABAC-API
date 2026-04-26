<?php

namespace Modules\Auth\Infrastructure\Services;

use Modules\Auth\Domain\Contracts\TokenServiceContract;
use Modules\Auth\Domain\ValueObjects\IssuedToken;
use Modules\Auth\Infrastructure\Models\UserModel;
use Modules\Shared\Domain\ValueObjects\UserId;

final class SanctumTokenService implements TokenServiceContract
{
    public function issue(UserId $userId, string $deviceName): IssuedToken
    {
        $user = UserModel::findOrFail($userId->value());

        // Revoke existing tokens for this device first
        $user->tokens()->where('name', $deviceName)->delete();

        $plain = $user->createToken($deviceName)->plainTextToken;

        return new IssuedToken($plain);
    }

    public function revoke(UserId $userId): void
    {
        $user = UserModel::findOrFail($userId->value());

        // Revoke all tokens (full logout)
        $user->tokens()->delete();
    }
}
