<?php

namespace Modules\Auth\Tests\Unit\Domain;

use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use Modules\Auth\Domain\Exceptions\UserNotActiveException;
use Modules\Auth\Domain\Exceptions\EmailNotVerifiedException;
use Modules\Auth\Domain\ValueObjects\Email;
use Modules\Auth\Domain\ValueObjects\HashedPassword;
use Modules\Shared\Domain\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

// Pure unit test — no Laravel, no database, no mocks needed
class UserTest extends TestCase
{
    private function makeUser(array $overrides = []): User
    {
        return new User(
            id:              new UserId($overrides['id'] ?? 1),
            email:           new Email($overrides['email'] ?? 'test@example.com'),
            password:        new HashedPassword(bcrypt('password')),
            isActive:        $overrides['isActive'] ?? true,
            isEmailVerified: $overrides['isEmailVerified'] ?? true,
        );
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = new User(
            id:              new UserId(1),
            email:           new Email('test@example.com'),
            password:        HashedPassword::fromPlain('secret123'),
            isActive:        true,
            isEmailVerified: true,
        );

        // Should not throw
        $user->login('secret123');
        $this->assertTrue(true);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->expectException(UserNotActiveException::class);

        $user = $this->makeUser(['isActive' => false]);
        $user->login('password');
    }

    public function test_unverified_user_cannot_login(): void
    {
        $this->expectException(EmailNotVerifiedException::class);

        $user = $this->makeUser(['isEmailVerified' => false]);
        $user->login('password');
    }

    public function test_wrong_password_throws_invalid_credentials(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $user = new User(
            id:              new UserId(1),
            email:           new Email('test@example.com'),
            password:        HashedPassword::fromPlain('correct-password'),
            isActive:        true,
            isEmailVerified: true,
        );

        $user->login('wrong-password');
    }
}
