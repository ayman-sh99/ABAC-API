<?php

namespace Modules\Authorization\Application\DTOs;

use Modules\Authorization\Domain\ValueObjects\ResourceAttributes;
use Modules\Shared\Domain\ValueObjects\UserId;

final readonly class CheckPermissionInputDTO
{
    public function __construct(
        public UserId             $userId,
        public string             $permissionName,   // e.g. "posts:edit"
        public ResourceAttributes $resource,         // attributes of the resource being acted on
    ) {}
}
