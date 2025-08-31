<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\AttributeValue
 *
 * Representa un valor específico para un atributo, como "Rojo" para el atributo "Color".
 *
 * @property int $id
 * @property int $attribute_id
 * @property string $value
 * @property array|null $metadata
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Attribute $attribute
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sku[] $skus
 */
class AttributeValue extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attribute_values';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attribute_id',
        'value',
        'metadata',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Define la relación inversa con el atributo al que pertenece.
     * Un valor (ej. "Rojo") pertenece a un único atributo (ej. "Color").
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Define la relación muchos a muchos con los SKUs.
     * Un valor de atributo es parte de la definición de múltiples SKUs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(Sku::class, 'sku_attribute_map')
            ->withTimestamps();
    }
}
