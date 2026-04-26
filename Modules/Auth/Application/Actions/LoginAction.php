<?php

namespace Modules\Auth\Application\Actions;

use Modules\Auth\Application\DTOs\LoginInputDTO;
use Modules\Auth\Application\DTOs\LoginOutputDTO;
use Modules\Auth\Domain\Contracts\TokenServiceContract;
use Modules\Auth\Domain\Contracts\UserRepositoryContract;
use Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use Modules\Auth\Domain\ValueObjects\Email;
use Modules\Authorization\Domain\Contracts\RoleRepositoryContract;
use Modules\Authorization\Domain\Entities\Permission;

final class LoginAction
{
    public function __construct(
        private readonly UserRepositoryContract $userRepository,
         private readonly RoleRepositoryContract $roleRepository,
        private readonly TokenServiceContract $tokenService,
    ) {}

    public function execute(LoginInputDTO $input): LoginOutputDTO
    {
        // 1. Find User
        $user = $this->userRepository->findByEmail(new Email($input->email));

        if (! $user) {
            throw new InvalidCredentialsException('Invalid credentials');
        }

        // 2. Domain enforces business rules
        $user->login($input->password);

        // 3. Load Roles + Permissions From Authorization Module via it's contract
         $role = $this->roleRepository->findByUserId($user->id());

        if ($role) {
            $user->assignRole($role);
        }

        // 4. Issue Token
        $token = $this->tokenService->issue($user->id(), $input->deviceName);

        // 5. Return Output DTO
        return new LoginOutputDTO(
            userId: $user->id()->value(),
            email: $user->email()->value(),
            token: $token->plainText(),
            tokenType: 'Bearer',
            roleName: $role?->name() ?? 'guest',
            permissions: array_map(
                fn(Permission $p) => $p->name(),
                $role?->permissions() ?? []
            ),
        );
    }
}
