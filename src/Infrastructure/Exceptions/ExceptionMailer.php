<?php

declare(strict_types=1);

namespace Infrastructure\Exceptions;

use Domain\Settings\ContactSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Infrastructure\Mail\ExceptionMail;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;

final class ExceptionMailer
{
    public static function handle(\Throwable $exception): void
    {
        $key = sprintf('exception:%s:%s', class_basename($exception), Str::slug($exception->getMessage()));

        RateLimiter::hit($key, 60);

        if (RateLimiter::attempts($key) >= 3) {
            $contactSettings = resolve(ContactSetting::class);

            if (! $contactSettings->specialist_email) {
                return;
            }

            $exceptionRenderer = new HtmlErrorRenderer(true);
            $renderableException = $exceptionRenderer->render($exception);

            Mail::to($contactSettings->specialist_email)->send(new ExceptionMail($renderableException));

            RateLimiter::clear($key);
        }
    }
}
