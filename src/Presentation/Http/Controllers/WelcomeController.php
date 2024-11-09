<?php

declare(strict_types=1);

namespace Presentation\Http\Controllers;

use Illuminate\Routing\ResponseFactory;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Spatie\RouteAttributes\Attributes\Route;

final readonly class WelcomeController
{
    public function __construct(private ResponseFactory $responseFactory) {}

    #[Route(methods: 'GET', uri: '/', name: 'welcome')]
    public function __invoke()
    {
        return $this->responseFactory->json(IikoMenuItem::toDomainEntity(IikoMenuItem::find(16)));
    }
}
