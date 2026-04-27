<?php

namespace Modules\Authorization\Application\Actions;

use Modules\Authorization\Application\DTOs\CheckPermissionInputDTO;
use Modules\Authorization\Application\DTOs\CheckPermissionOutputDTO;
use Modules\Authorization\Domain\Contracts\PermissionRepositoryContract;
use Modules\Authorization\Domain\Contracts\PolicyEngineContract;

final class CheckPermissionAction
{
    public function __construct(
        private readonly PermissionRepositoryContract $permissionRepository,
        private readonly PolicyEngineContract $policyEngine
    ) {}

    public function execute(CheckPermissionInputDTO $input): CheckPermissionOutputDTO
    {
        // 1. Load the full permission entity (with conditions) for this user
        $permission = $this->permissionRepository->findForUser(
            $input->userId->value(),
            $input->permissionName,
        );

        if ($permission === null) {
            return new CheckPermissionOutputDTO(
                allowed: false,
                reason:  "Permission '{$input->permissionName}' not assigned.",
            );
        }

        // 2. Run ABAC evaluation
        $allowed = $this->policyEngine->evaluate(
            $input->userId,
            $permission,
            $input->resource,
        );

        return new CheckPermissionOutputDTO(
            allowed: $allowed,
            reason:  $allowed ? 'Allowed by policy.' : 'Denied by policy conditions.',
        );
    }
}
