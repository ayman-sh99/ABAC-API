<?php

namespace Modules\Auth\Domain\Contracts;

use Modules\Auth\Domain\ValueObjects\IssuedToken;
use Modules\Shared\Domain\ValueObjects\UserId;

interface TokenServiceContract
{
    public function issue(UserId $userId, string $device_name): IssuedToken;
    public function revoke(UserId $userId): void;
}
