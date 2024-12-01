<?php

declare(strict_types=1);

namespace Shared\Presentation\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Log\Context\Repository as ContextRepository;

final readonly class ContextualLogMiddleware
{
    public function __construct(private ContextRepository $contextRepository) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $this
            ->contextRepository
            ->add('path', $request->path())
            ->add('request', $request->all());

        return $next($request);
    }
}
