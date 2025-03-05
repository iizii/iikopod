<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $organization_id
 * @property string|null $inn
 * @property string|null $name
 * @property string|null $address
 * @property string $latitude
 * @property string $longitude
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization whereInn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoOrganization whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoOrganization extends Model
{
    protected $fillable = [
        'organization_id',
        'inn',
        'name',
        'address',
        'latitude',
        'longitude',
        'coordinates',
    ];
}
