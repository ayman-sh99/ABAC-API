<?php

namespace Modules\Auth\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auth\Application\DTOs\LoginOutputDTO;

class AuthResource extends JsonResource
{
    public function __construct(private readonly LoginOutputDTO $output)
    {
        parent::__construct($output);
    }

    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id'    => $this->output->userId,
                'email' => $this->output->email,
                'role'  => $this->output->roleName,
            ],
            'permissions' => $this->output->permissions,
            'token' => [
                'access_token' => $this->output->token,
                'token_type'   => $this->output->tokenType,
            ],
        ];
    }
}
