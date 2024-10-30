<?php

declare(strict_types=1);

namespace Domain\Iiko\Models;

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
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization query()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization whereInn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoOrganization whereUpdatedAt($value)
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
