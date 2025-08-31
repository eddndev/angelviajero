<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CartItem
 *
 * Representa una línea de ítem dentro de un carrito de compras,
 * vinculando un SKU específico con una cantidad.
 *
 * @property int $id
 * @property int $cart_id
 * @property int $sku_id
 * @property int $quantity
 * @property float $price_at_addition
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cart $cart
 * @property-read \App\Models\Sku $sku
 * @property-read float $subtotal
 */
class CartItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cart_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cart_id',
        'sku_id',
        'quantity',
        'price_at_addition',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price_at_addition' => 'decimal:2',
    ];

    /**
     * Define la relación inversa con el carrito.
     * Un ítem pertenece a un carrito.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Define la relación inversa con el SKU.
     * Un ítem del carrito corresponde a un SKU específico.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }
    
    /**
     * Accessor para calcular el subtotal del ítem (precio * cantidad).
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_at_addition * $this->quantity,
        );
    }
}
