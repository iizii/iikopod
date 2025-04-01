<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\DeliveryPoint;

/**
 * Class EndpointAddress
 *
 * Represents a delivery address associated with an order.
 *
 * @property int $id
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $index
 * @property string|null $street
 * @property string|null $city
 * @property string|null $house
 * @property string|null $building
 * @property string|null $flat
 * @property string|null $entrance
 * @property string|null $floor
 * @property string|null $doorphone
 * @property string|null $region
 * @property string|null $line1
 * @property int $order_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\Orders\Models\Order $order
 *
 * @method static Builder<static>|EndpointAddress newModelQuery()
 * @method static Builder<static>|EndpointAddress newQuery()
 * @method static Builder<static>|EndpointAddress query()
 * @method static Builder<static>|EndpointAddress whereBuilding($value)
 * @method static Builder<static>|EndpointAddress whereCity($value)
 * @method static Builder<static>|EndpointAddress whereCreatedAt($value)
 * @method static Builder<static>|EndpointAddress whereDoorphone($value)
 * @method static Builder<static>|EndpointAddress whereEntrance($value)
 * @method static Builder<static>|EndpointAddress whereFlat($value)
 * @method static Builder<static>|EndpointAddress whereFloor($value)
 * @method static Builder<static>|EndpointAddress whereHouse($value)
 * @method static Builder<static>|EndpointAddress whereId($value)
 * @method static Builder<static>|EndpointAddress whereIndex($value)
 * @method static Builder<static>|EndpointAddress whereLatitude($value)
 * @method static Builder<static>|EndpointAddress whereLine1($value)
 * @method static Builder<static>|EndpointAddress whereLongitude($value)
 * @method static Builder<static>|EndpointAddress whereOrderId($value)
 * @method static Builder<static>|EndpointAddress whereRegion($value)
 * @method static Builder<static>|EndpointAddress whereStreet($value)
 * @method static Builder<static>|EndpointAddress whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class EndpointAddress extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'latitude',
        'longitude',
        'street',
        'city',
        'house',
        'building',
        'flat',
        'entrance',
        'floor',
        'doorphone',
        'region',
        'line1',
        'order_id',
        'index',
    ];

    /**
     * Get the order associated with this endpoint address.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function fromDomainEntity(DeliveryPoint $deliveryPoint): self
    {
        $this->newInstance();

        return $this->fill([
            'latitude' => $deliveryPoint->coordinates->latitude,
            'longitude' => $deliveryPoint->coordinates->longitude,
            'street' => $deliveryPoint->address->street->name,
            'city' => 'МОК ДАННЫХ', // $deliveryPoint->address->street->city->name,
            'house' => $deliveryPoint->address->house,
            'building' => $deliveryPoint->address->building,
            'flat' => $deliveryPoint->address->flat,
            'entrance' => $deliveryPoint->address->entrance,
            'floor' => $deliveryPoint->address->floor,
            'doorphone' => $deliveryPoint->address->doorphone,
            'region' => 'МОК ДАННЫХ', // $deliveryPoint->address->region->name,
            'line1' => $deliveryPoint->address->line1,
            'index' => $deliveryPoint->address->index,
        ]);
    }
}
