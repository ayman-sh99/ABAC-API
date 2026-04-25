<?php

namespace Modules\Auth\Domain\Contracts;

use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\ValueObjects\Email;
use Modules\Shared\Domain\ValueObjects\UserId;

interface UserRepositoryContract
{
    public function findByEmail(Email $email): ?User;
    public function findById(UserId $id): ?User;
}
