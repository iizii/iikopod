<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;

final readonly class SignatureCompiler
{
    public function __construct(
        public string $user,
        public string $password,
    ) {}

    public function compile(
        RequestInterface $request,
        CarbonImmutable $date,
    ): string {
        $requestParams = $request->data() instanceof Arrayable
            ? $request->data()->toArray()
            : $request->data();

        $params = null;

        if ($request->method() === RequestMethod::GET) {
            $paramStrings = [];
            foreach ($requestParams as $key => $value) {
                $paramStrings[] = sprintf('%s=%s', $key, $value);
            }

            \sort($paramStrings, SORT_STRING);

            $params = \implode(',', $paramStrings);
        }

        if ($request->method() === RequestMethod::POST || $request->method() === RequestMethod::PATCH) {
            $params = \json_encode($requestParams);
        }

        $hashed = \sprintf(
            '%s%s%s%s%s',
            $this->user,
            $request->method()->value,
            $request->endpoint(),
            $date->toRfc7231String(),
            $params,
        );

        return hash_hmac('sha256', $hashed, $this->password);
    }
}
