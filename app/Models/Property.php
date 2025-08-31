<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Property
 *
 * Almacena una especificación técnica o informativa (par clave-valor)
 * que puede pertenecer a un producto general o a un SKU específico.
 *
 * @property int $id
 * @property int|null $product_id
 * @property int|null $sku_id
 * @property string $property_name
 * @property string $property_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Sku|null $sku
 */
class Property extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'properties';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'sku_id',
        'property_name',
        'property_value',
    ];

    /**
     * Define la relación inversa con el producto.
     * La propiedad puede pertenecer a un producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Define la relación inversa con el SKU.
     * La propiedad puede pertenecer a un SKU.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }
}
