<?php

namespace Modules\Authorization\Presentation\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Authorization\Application\Actions\CheckPermissionAction;
use Modules\Authorization\Application\DTOs\CheckPermissionInputDTO;
use Modules\Authorization\Domain\ValueObjects\ResourceAttributes;
use Modules\Shared\Domain\ValueObjects\UserId;
use Symfony\Component\HttpFoundation\Response;

readonly class AbacMiddleware
{
    public function __construct(
        private CheckPermissionAction $checkPermissionAction,
    ) {}

    /**
     * Usage on route: ->middleware('abac:posts:edit')
     *
     * The middleware automatically extracts resource attributes from:
     * 1. Route model binding  (e.g. {post} → $request->route('post'))
     * 2. Request body fields  (owner_id, status, amount, etc.)
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Build resource attributes from the request context
        $resource = $this->buildResourceAttributes($request);

        $output = $this->checkPermissionAction->execute(
            new CheckPermissionInputDTO(
                userId:         new UserId($user->id),
                permissionName: $permission,
                resource:       $resource,
            )
        );

        if (! $output->allowed) {
            return response()->json([
                'message' => 'Forbidden.',
                'reason'  => $output->reason,
            ], 403);
        }

        return $next($request);
    }

    /**
     * Extracts resource context from the current request.
     * Checks route-bound models first, then falls back to request data.
     */
    private function buildResourceAttributes(Request $request): ResourceAttributes
    {
        $attributes = [];

        // Check all route parameters for model instances (route model binding)
        foreach ($request->route()->parameters() as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                // Merge the model's attributes — owner_id, status, etc. will be included
                $attributes = array_merge($attributes, $value->toArray());
                break; // Use the first model found
            }
        }

        // Also merge any explicit fields from the request body
        $attributes = array_merge($attributes, $request->only([
            'owner_id',
            'status',
            'amount',
            'type',
        ]));

        return ResourceAttributes::from($attributes);
    }
}
