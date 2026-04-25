<?php

namespace Modules\Auth\Presentation\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Auth\Application\Actions\LoginAction;
use Modules\Auth\Application\Actions\LogoutAction;
use Modules\Auth\Application\DTOs\LoginInputDTO;
use Modules\Auth\Application\DTOs\LogoutInputDTO;
use Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use Modules\Auth\Domain\Exceptions\UserNotActiveException;
use Modules\Auth\Domain\Exceptions\EmailNotVerifiedException;
use Modules\Auth\Presentation\Requests\LoginRequest;
use Modules\Auth\Presentation\Resources\AuthResource;
use Modules\Shared\Domain\ValueObjects\UserId;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginAction  $loginAction,
        private readonly LogoutAction $logoutAction,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $output = $this->loginAction->execute(
                new LoginInputDTO(
                    email:      $request->input('email'),
                    password:   $request->input('password'),
                    deviceName: $request->input('device_name', 'web'),
                )
            );

            return response()->json(new AuthResource($output), 200);

        } catch (InvalidCredentialsException $e) {
            return response()->json(['message' => $e->getMessage()], 401);

        } catch (UserNotActiveException $e) {
            return response()->json(['message' => $e->getMessage()], 403);

        } catch (EmailNotVerifiedException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->logoutAction->execute(
            new LogoutInputDTO(
                userId: new UserId($request->user()->id)
            )
        );

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }

    public function me(Request $request): JsonResponse
    {
        // The authenticated user's info — reuse AuthResource or a separate MeResource
        return response()->json([
            'user' => [
                'id'    => $request->user()->id,
                'email' => $request->user()->email,
            ]
        ]);
    }
}
