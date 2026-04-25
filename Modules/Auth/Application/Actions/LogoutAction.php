<?php

namespace Modules\Auth\Application\Actions;

use Modules\Auth\Application\DTOs\LogoutInputDTO;
use Modules\Auth\Domain\Contracts\TokenServiceContract;

final class LogoutAction
{
    public function __construct(
        private readonly TokenServiceContract $tokenService,
    ) {}

    public function execute(LogoutInputDTO $input): void
    {
        $this->tokenService->revoke($input->userId);
    }
}
