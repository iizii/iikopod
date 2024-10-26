<?php

declare(strict_types=1);

namespace Presentation\Console\Commands\Iiko;

use Domain\Integrations\Iiko\IikoConnectorInterface;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationRequestData;
use Infrastructure\Integrations\IIko\Requests\GetOrganizationsRequest;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('app:iiko:request-organization', 'Запрос организаций из Iiko')]
final class RequestOrganizationsCommand extends Command
{
    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function handle(IikoConnectorInterface $iikoConnector): void
    {
        $response = $iikoConnector->send(
            new GetOrganizationsRequest(
                new GetOrganizationRequestData(
                    [],
                    true,
                    true,
                ),
            ),
        );
        dd($response);
    }
}
