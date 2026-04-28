<?php

namespace Modules\Authorization\Presentation\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Authorization\Application\Actions\ResolveFieldAccessAction;
use Modules\Authorization\Domain\Exceptions\ForbiddenFieldException;
use Modules\Authorization\Domain\Services\FieldGuardService;
use Modules\Authorization\Domain\ValueObjects\FieldPermissions;
use Modules\Shared\Domain\ValueObjects\UserId;
use Symfony\Component\HttpFoundation\Response;

class FieldGuardMiddleware
{
    public function __construct(
        private readonly ResolveFieldAccessAction $resolveFieldAccessAction,
        private readonly FieldGuardService        $fieldGuardService,
    ) {}

    /**
     * Usage: ->middleware('field.guard:posts:edit')
     *
     * On the way IN:  rejects request fields the user cannot write.
     * On the way OUT: strips response fields the user cannot read.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userId           = new UserId($user->id);
        $fieldAccessOutput = $this->resolveFieldAccessAction->execute($userId, $permission);
        $fieldPermissions  = new FieldPermissions(
            $fieldAccessOutput->readableFields,
            $fieldAccessOutput->writableFields,
        );

        // --- GUARD: reject forbidden write fields BEFORE the request hits the controller ---
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            try {
                $this->fieldGuardService->guard($request->all(), $fieldPermissions);
            } catch (ForbiddenFieldException $e) {
                return response()->json([
                    'message'          => 'You cannot write to some of these fields.',
                    'forbidden_fields' => $e->forbiddenFields(),
                ], 422);
            }
        }

        // --- Pass to controller ---
        $response = $next($request);

        // --- FILTER: strip unreadable fields from JSON responses ---
        if ($response instanceof JsonResponse) {
            $response = $this->filterResponse($response, $fieldPermissions);
        }

        return $response;
    }

    private function filterResponse(JsonResponse $response, FieldPermissions $fieldPermissions): JsonResponse
    {
        $data = $response->getData(true); // decode to array

        // Handle both {"data": {...}} and flat {"id": 1, ...} structures
        if (isset($data['data']) && is_array($data['data'])) {
            if (isset($data['data'][0])) {
                // Collection: filter each item
                $data['data'] = array_map(
                    fn($item) => $fieldPermissions->filterReadable($item),
                    $data['data']
                );
            } else {
                // Single resource
                $data['data'] = $fieldPermissions->filterReadable($data['data']);
            }
        } else {
            // Flat structure
            $data = $fieldPermissions->filterReadable($data);
        }

        return response()->json($data, $response->getStatusCode());
    }
}
