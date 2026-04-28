<?php

namespace Modules\Authorization\Domain\Services;

use Modules\Authorization\Domain\Contracts\FieldFilterServiceContract;
use Modules\Authorization\Domain\ValueObjects\FieldPermissions;

final class FieldFilterService implements FieldFilterServiceContract
{
    public function filter(array $data, FieldPermissions $fieldPermissions): array
    {
        return $fieldPermissions->filterReadable($data);
    }
}
