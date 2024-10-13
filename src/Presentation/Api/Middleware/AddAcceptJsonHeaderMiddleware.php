<?php

declare(strict_types=1);

namespace Presentation\Api\Middleware;

use Closure;
use Illuminate\Http\Request;

final class AddAcceptJsonHeaderMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
