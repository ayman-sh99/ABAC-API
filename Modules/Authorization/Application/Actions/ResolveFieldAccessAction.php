<?php

namespace Modules\Authorization\Application\Actions;

use Modules\Authorization\Application\DTOs\FieldAccessOutputDTO;
use Modules\Authorization\Domain\Contracts\PolicyEngineContract;
use Modules\Shared\Domain\ValueObjects\UserId;

final class ResolveFieldAccessAction
{
    public function __construct(
        private readonly PolicyEngineContract $policyEngine,
    ) {}

    public function execute(UserId $userId, string $permissionName): FieldAccessOutputDTO
    {
        $fieldPermissions = $this->policyEngine->resolveFieldPermissions($userId, $permissionName);

        return new FieldAccessOutputDTO(
            readableFields: $fieldPermissions->readableFields(),
            writableFields: $fieldPermissions->writableFields(),
        );
    }
}
