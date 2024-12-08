<?php

declare(strict_types=1);

ini_set('memory_limit', '-1');

use function Pest\Stressless\stress;

it('Webhook Order Stress Test', function () {
    $result = stress(route('api.v1.iiko.webhook'))
        ->post(["name" => "Nuno"])
        ->concurrency(1)
        ->for(1)
        ->seconds()
        ->run();

    $requests = $result->requests;

    info(sprintf('Total Requests: %s', $requests->count));
    info(sprintf('Failed requests count: %s', $requests->failed->count));
    info(sprintf('Requests per second: %s', $requests->rate));
    info(sprintf('Request duration ms: %s', $requests->duration->med));

    expect($requests->failed->count)
        ->toBe(0);
});
