<?php

namespace Modules\Authorization\Application\DTOs;

final readonly class FieldAccessOutputDTO
{
    /**
     * @param string[]|null $readableFields  null = all fields allowed
     * @param string[]|null $writableFields  null = all fields allowed
     */
    public function __construct(
        public ?array $readableFields,
        public ?array $writableFields,
    ) {}
}
