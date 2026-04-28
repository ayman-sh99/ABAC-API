<?php

namespace Modules\Authorization\Tests\Unit\Domain;

use Modules\Authorization\Domain\ValueObjects\FieldPermissions;
use Modules\Authorization\Domain\ValueObjects\PolicyConditions;
use PHPUnit\Framework\TestCase;

class FieldPermissionsTest extends TestCase
{
    // --- canRead ---
    public function test_unrestricted_can_read_any_field(): void
    {
        $fp = FieldPermissions::unrestricted();
        $this->assertTrue($fp->canRead('internal_notes'));
        $this->assertTrue($fp->canRead('cost'));
    }

    public function test_restricted_can_only_read_allowed_fields(): void
    {
        $fp = new FieldPermissions(readable: ['title', 'body'], writable: null);
        $this->assertTrue($fp->canRead('title'));
        $this->assertFalse($fp->canRead('internal_notes'));
    }

    // --- canWrite ---
    public function test_unrestricted_can_write_any_field(): void
    {
        $fp = FieldPermissions::unrestricted();
        $this->assertTrue($fp->canWrite('status'));
    }

    public function test_restricted_cannot_write_forbidden_fields(): void
    {
        $fp = new FieldPermissions(readable: null, writable: ['title', 'body']);
        $this->assertTrue($fp->canWrite('title'));
        $this->assertFalse($fp->canWrite('status'));
        $this->assertFalse($fp->canWrite('cost'));
    }

    // --- filterReadable ---
    public function test_filter_readable_removes_forbidden_fields(): void
    {
        $fp   = new FieldPermissions(readable: ['id', 'title'], writable: null);
        $data = ['id' => 1, 'title' => 'Hello', 'internal_notes' => 'secret', 'cost' => 500];

        $filtered = $fp->filterReadable($data);

        $this->assertSame(['id' => 1, 'title' => 'Hello'], $filtered);
        $this->assertArrayNotHasKey('internal_notes', $filtered);
        $this->assertArrayNotHasKey('cost', $filtered);
    }

    public function test_filter_readable_returns_all_when_unrestricted(): void
    {
        $fp   = FieldPermissions::unrestricted();
        $data = ['id' => 1, 'secret' => 'value'];

        $this->assertSame($data, $fp->filterReadable($data));
    }

    // --- findForbiddenWriteFields ---
    public function test_finds_forbidden_write_fields(): void
    {
        $fp    = new FieldPermissions(readable: null, writable: ['title', 'body']);
        $input = ['title' => 'New', 'body' => '...', 'status' => 'published', 'cost' => 100];

        $forbidden = $fp->findForbiddenWriteFields($input);

        $this->assertContains('status', $forbidden);
        $this->assertContains('cost', $forbidden);
        $this->assertNotContains('title', $forbidden);
    }

    public function test_finds_no_forbidden_fields_when_unrestricted(): void
    {
        $fp    = FieldPermissions::unrestricted();
        $input = ['title' => 'New', 'status' => 'published', 'secret' => 'yes'];

        $this->assertEmpty($fp->findForbiddenWriteFields($input));
    }

    // --- fromConditions ---
    public function test_builds_from_conditions(): void
    {
        $conditions = new PolicyConditions([
            'readable_fields' => ['id', 'title'],
            'writable_fields' => ['title'],
        ]);

        $fp = FieldPermissions::fromConditions($conditions);

        $this->assertTrue($fp->canRead('title'));
        $this->assertFalse($fp->canRead('cost'));
        $this->assertTrue($fp->canWrite('title'));
        $this->assertFalse($fp->canWrite('id')); // readable but NOT writable
    }

    public function test_null_fields_when_conditions_have_no_field_keys(): void
    {
        $conditions = new PolicyConditions(['owner_only' => true]);
        $fp         = FieldPermissions::fromConditions($conditions);

        // No readable_fields / writable_fields in conditions → unrestricted
        $this->assertTrue($fp->canRead('anything'));
        $this->assertTrue($fp->canWrite('anything'));
    }
}
