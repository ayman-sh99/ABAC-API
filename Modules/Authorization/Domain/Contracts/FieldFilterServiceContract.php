<?php

namespace Modules\Authorization\Domain\Contracts;

use Modules\Authorization\Domain\ValueObjects\FieldPermissions;

interface FieldFilterServiceContract
{
    /**
     * Remove fields the user cannot read from a data array.
     * Used before returning resource data to the client.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function filter(array $data, FieldPermissions $fieldPermissions): array;
}

