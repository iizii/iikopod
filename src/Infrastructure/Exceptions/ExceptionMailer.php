<?php

declare(strict_types=1);

namespace Infrastructure\Exceptions;

use Domain\Settings\ContactSetting;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Infrastructure\Mail\ExceptionMail;
use Infrastructure\Mail\ExceptionOperatorMail;
use Shared\Domain\Exceptions\ShouldNotifyOperator;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;

final class ExceptionMailer
{
    public static function handle(\Throwable $exception): void
    {
        if ($exception instanceof ShouldntReport) {
            return;
        }

        $key = sprintf('exception:%s:%s', class_basename($exception), Str::slug($exception->getMessage()));

        RateLimiter::hit($key, 60);

        if (RateLimiter::attempts($key) < 3) {
            return;
        }

        $contactSettings = resolve(ContactSetting::class);

        if ($contactSettings->specialist_email) {
            $exceptionRenderer = new HtmlErrorRenderer(true);
            $renderableException = $exceptionRenderer->render($exception);

            Mail::to($contactSettings->specialist_email)->send(new ExceptionMail($renderableException));
        }

        if ($exception instanceof ShouldNotifyOperator && $contactSettings->call_center_operator_email) {
            Mail::to($contactSettings->call_center_operator_email)->send(new ExceptionOperatorMail($exception));
        }

        RateLimiter::clear($key);
    }
}
