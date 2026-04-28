<?php

namespace Modules\Authorization\Domain\Services;

use Modules\Authorization\Domain\Contracts\FieldGuardServiceContract;
use Modules\Authorization\Domain\Exceptions\ForbiddenFieldException;
use Modules\Authorization\Domain\ValueObjects\FieldPermissions;

final class FieldGuardService implements FieldGuardServiceContract
{
    public function guard(array $input, FieldPermissions $fieldPermissions): void
    {
        $forbidden = $fieldPermissions->findForbiddenWriteFields($input);

        if (! empty($forbidden)) {
            throw new ForbiddenFieldException($forbidden);
        }
    }
}

