<?php

namespace Modules\Auth\Application\DTOs;

use Modules\Shared\Domain\ValueObjects\UserId;

final readonly class LogoutInputDTO
{
    public function __construct(
        public UserId $userId,
    ) {}
}
