<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Endpoint.
 *
 * * @OA\Schema(
 *     type="object",
 *     schema="Endpoint",
 *     example={
 *         "url": "https://example.com/api",
 *         "title": "Example.com OParl API",
 *         "description": "This is a cool OParl API"
 *     },
 *     required={
 *         "id",
 *         "url",
 *         "title"
 *     },
 *     @OA\Property(
 *         property="id",
 *         type="int",
 *         description="Endpoint id on this api"
 *     ),
 *     @OA\Property(
 *         property="url",
 *         type="string",
 *         description="The OParl endpoint's entrypoint"
 *     ),
 *     @OA\Property(
 *         property="osm",
 *         type="int",
 *         description="Optional: A corresponding OpenStreetMap relation for the endpoint"
 *     ),
 *     @OA\Property(
 *         property="wikidata",
 *         type="string",
 *         description="Optional: A corresponding Wikidata item for the endpoint"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="The OParl endpoint's name"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Optional detailed endpoint description"
 *     ),
 *     @OA\Property(
 *         property="bodyCount",
 *         type="int",
 *         description="Number of bodies available on endpoint"
 *     )
 * )
 *
 * @method Endpoint find(mixed $id)
 * @method Endpoint orderBy(string $field)
 * @method Endpoint firstOrNew(array $attributes)
 * @method Collection get()
 * @method int count()
 *
 * @property Carbon|null $endpoint_fetched;
 */
class Endpoint extends Model
{
    protected $fillable = [
        'url',
        'title',
        'description',
    ];

    protected $casts = [
        'system' => 'array',
        'osm' => 'integer'
    ];

    protected $dates = [
        'endpoint_fetched',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'endpoint_fetched',
    ];

    protected $appends = [
        'fetched',
        'formattedDescription',
        'bodyCount'
    ];

    /**
     * @OA\Property( property="bodies", type="array", @OA\Items( ref="#/components/schemas/EndpointBody" ))
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bodies()
    {
        return $this->hasMany(EndpointBody::class, 'endpoint_id', 'id');
    }

    /**
     * @OA\Property(
     *   property="fetched",
     *   type="string",
     *   description="ISO 8601-conform timestamp of when this endpoint information was last updated"
     * )
     *
     * @return string
     */
    public function getFetchedAttribute()
    {
        $fetched = $this->endpoint_fetched;

        if (!is_null($fetched)) {
            return $fetched->toIso8601String();
        }

        return '';
    }

    public function getFormattedDescriptionAttribute()
    {
        return $this->description;
    }

    public function getBodyCountAttribute() {
        return $this->bodies()->count();
    }
}
