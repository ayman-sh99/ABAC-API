<?php

namespace Modules\Auth\Tests\Unit\Application;

use Mockery;
use Mockery\MockInterface;
use Modules\Auth\Application\Actions\LoginAction;
use Modules\Auth\Application\DTOs\LoginInputDTO;
use Modules\Auth\Domain\Contracts\TokenServiceContract;
use Modules\Auth\Domain\Contracts\UserRepositoryContract;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use Modules\Auth\Domain\ValueObjects\Email;
use Modules\Auth\Domain\ValueObjects\HashedPassword;
use Modules\Auth\Domain\ValueObjects\IssuedToken;
use Modules\Authorization\Domain\Contracts\RoleRepositoryContract;
use Modules\Authorization\Domain\Entities\Role;
use Modules\Authorization\Domain\ValueObjects\RoleId;
use Modules\Shared\Domain\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class LoginActionTest extends TestCase
{
    private UserRepositoryContract|MockInterface $userRepo;
    private RoleRepositoryContract|MockInterface $roleRepo;
    private TokenServiceContract|MockInterface   $tokenService;
    private LoginAction                          $action;

    protected function setUp(): void
    {
        $this->userRepo     = Mockery::mock(UserRepositoryContract::class);
        $this->roleRepo     = Mockery::mock(RoleRepositoryContract::class);
        $this->tokenService = Mockery::mock(TokenServiceContract::class);

        $this->action = new LoginAction(
            $this->userRepo,
            $this->roleRepo,
            $this->tokenService,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_successful_login_returns_output_dto(): void
    {
        $userId = new UserId(1);
        $user   = new User(
            id:              $userId,
            email:           new Email('john@example.com'),
            password:        HashedPassword::fromPlain('password'),
            isActive:        true,
            isEmailVerified: true,
        );
        $role = new Role(new RoleId(1), 'admin', []);

        $this->userRepo->shouldReceive('findByEmail')->once()->andReturn($user);
        $this->roleRepo->shouldReceive('findByUserId')->once()->andReturn($role);
        $this->tokenService->shouldReceive('issue')->once()->andReturn(new IssuedToken('token-abc'));

        $output = $this->action->execute(new LoginInputDTO('john@example.com', 'password'));

        $this->assertSame('john@example.com', $output->email);
        $this->assertSame('token-abc', $output->token);
        $this->assertSame('Bearer', $output->tokenType);
        $this->assertSame('admin', $output->roleName);
    }

    public function test_throws_when_user_not_found(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $this->userRepo->shouldReceive('findByEmail')->once()->andReturn(null);

        $this->action->execute(new LoginInputDTO('nobody@example.com', 'password'));
    }
}
