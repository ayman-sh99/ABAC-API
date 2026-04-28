<?php

namespace Modules\Authorization\Domain\ValueObjects;

final class FieldPermissions
{
    /**
     * @param string[]|null $readable  null means "all fields allowed"
     * @param string[]|null $writable  null means "all fields allowed"
     */
    public function __construct(
        private readonly ?array $readable,
        private readonly ?array $writable,
    ) {}

    /** Can this user read the given field? */
    public function canRead(string $field): bool
    {
        if ($this->readable === null) {
            return true; // null = no restriction = all fields allowed
        }
        return in_array($field, $this->readable, true);
    }

    /** Can this user write to the given field? */
    public function canWrite(string $field): bool
    {
        if ($this->writable === null) {
            return true;
        }
        return in_array($field, $this->writable, true);
    }

    /** Filter an array of data, keeping only readable fields. */
    public function filterReadable(array $data): array
    {
        if ($this->readable === null) {
            return $data; // No restriction
        }
        return array_intersect_key($data, array_flip($this->readable));
    }

    /** Return only writable fields from an input array. */
    public function filterWritable(array $input): array
    {
        if ($this->writable === null) {
            return $input;
        }
        return array_intersect_key($input, array_flip($this->writable));
    }

    /** Return fields present in $input that are NOT writable. */
    public function findForbiddenWriteFields(array $input): array
    {
        if ($this->writable === null) {
            return [];
        }
        return array_keys(array_diff_key($input, array_flip($this->writable)));
    }

    public function readableFields(): ?array { return $this->readable; }
    public function writableFields(): ?array { return $this->writable; }

    /** Build from a PolicyConditions value object. */
    public static function fromConditions(PolicyConditions $conditions): self
    {
        return new self(
            readable: $conditions->has('readable_fields')
                ? (array) $conditions->get('readable_fields')
                : null,
            writable: $conditions->has('writable_fields')
                ? (array) $conditions->get('writable_fields')
                : null,
        );
    }

    /** No field restrictions at all (admin-level). */
    public static function unrestricted(): self
    {
        return new self(readable: null, writable: null);
    }

    /** User has no access to any fields. */
    public static function none(): self
    {
        return new self(readable: [], writable: []);
    }
}
