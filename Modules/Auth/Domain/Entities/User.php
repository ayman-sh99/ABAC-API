<?php

namespace Modules\Auth\Domain\Entities;

use Modules\Auth\Domain\Exceptions\EmailNotVerifiedException;
use Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use Modules\Auth\Domain\Exceptions\UserNotActiveException;
use Modules\Auth\Domain\ValueObjects\Email;
use Modules\Auth\Domain\ValueObjects\HashedPassword;
use Modules\Authorization\Domain\Entities\Role;
use Modules\Shared\Domain\ValueObjects\UserId;

final class User
{
    public function __construct(
        private readonly UserId         $id,
        private readonly Email          $email,
        private readonly HashedPassword $password,
        private readonly bool           $isActive,
        private readonly bool           $isEmailVerified,
         private ?Role                  $role = null,
    ) {}


    // Business Rules

    public function login(string $plain_password): void
    {
        if (! $this->isActive) {
            Throw new UserNotActiveException('Account is inactive. Please contact support.');
        }

        if (! $this->isEmailVerified) {
            throw new EmailNotVerifiedException('Email not verified. Please check your inbox for the verification link.');
        }

        if (! $this->password->verify($plain_password)) {
            throw new InvalidCredentialsException('Invalid credentials. Please try again.');
        }
    }

    public function assignRole(Role $role): void
    {
         $this->role = $role;
    }

    public function can(string $permission): bool
    {
         if ($this->role === null) {
             return false;
         }

         return $this->role->hasPermission($permission);
    }

    // ----- Getters -----
    public function id(): UserId    { return $this->id; }
    public function email(): Email    { return $this->email; }
     public function role(): ?Role    { return $this->role; }
    public function isActive(): bool    { return $this->isActive; }
    public function isEmailVerified(): bool    { return $this->isEmailVerified; }
}
