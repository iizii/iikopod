<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Shared\Infrastructure\Integrations\ResponseData;

final class GetActiveOrganizationCouriersResponseData extends ResponseData
{
    public function __construct(
        public readonly string $courierId,
        public $lastActiveLatitude, // Не стал прописывать тип т.к. не используется в проекте, а прописан тут для полноты
        public $lastActiveLongitude, // Не стал прописывать тип т.к. не используется в проекте, а прописан тут для полноты
        public $lastActiveClientDate // Не стал прописывать тип т.к. не используется в проекте, а прописан тут для полноты

    ) {}
}
