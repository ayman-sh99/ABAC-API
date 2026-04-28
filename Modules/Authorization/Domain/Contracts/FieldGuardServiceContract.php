<?php

namespace Modules\Authorization\Domain\Contracts;

use Modules\Authorization\Domain\Exceptions\ForbiddenFieldException;
use Modules\Authorization\Domain\ValueObjects\FieldPermissions;

interface FieldGuardServiceContract
{
    /**
     * Throw ForbiddenFieldException if $input contains fields the user cannot write.
     *
     * @param  array<string, mixed> $input
     * @throws ForbiddenFieldException
     */
    public function guard(array $input, FieldPermissions $fieldPermissions): void;
}
