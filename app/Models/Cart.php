<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Cart
 *
 * Representa un carrito de compras, que puede pertenecer a un usuario
 * registrado o a un visitante (identificado por session_id).
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CartItem[] $items
 * @property-read float $total
 */
class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'status',
    ];

    /**
     * Define la relación inversa con el usuario.
     * Un carrito puede pertenecer a un usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define la relación uno a muchos con los ítems del carrito.
     * Un carrito contiene múltiples ítems.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Accessor para calcular el valor total del carrito.
     * Suma los subtotales de todos sus ítems.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items->sum(function (CartItem $item) {
                return $item->subtotal;
            }),
        );
    }
}
