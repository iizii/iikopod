<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Contracts\Support\Arrayable;
use Infrastructure\Integrations\IIko\DataTransferObjects\AddOrderItemsRequest\AddOrderItemsRequestData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;

final readonly class AddOrderItemsRequest implements RequestInterface
{
    public function __construct(
        private AddOrderItemsRequestData $data,
        private string $token,
    ) {
    }

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/deliveries/add_items';
    }

    public function headers(): array|Arrayable
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }

    public function data(): array|Arrayable
    {
        return [
            'organizationId' => $this->data->organizationId,
            'orderId' => $this->data->orderId,
            'items' => array_map(function ($item) {
                return [
                    'productId' => $item->productId,
                    'modifiers' => array_map(function ($modifier) {
                        return [
                            'productId' => $modifier->productId,
                            'amount' => 1,
                            'productGroupId' => $modifier->productGroupId,
                        ];
                    }, $item->modifiers),
                    'price' => $item->price,
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'productSizeId' => $item->productSizeId,
                    'comboInformation' => $item->comboInformation,
                    'comment' => $item->comment,
                ];
            }, $this->data->items),
        ];
    }
} 