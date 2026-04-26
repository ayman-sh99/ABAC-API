<?php

namespace Modules\Auth\Application\DTOs;

final readonly class LoginInputDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName = 'WEB'
    ) {}
}
