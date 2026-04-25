<?php

namespace Modules\Auth\Application\DTOs;

final readonly class LoginOutputDTO
{
    public function __construct(
        public int $userId,
        public string $email,
        public string $token,
        public string $tokenType,
        public string $roleName,
        public array $permissions
    ) {}
}
