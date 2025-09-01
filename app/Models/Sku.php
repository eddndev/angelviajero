<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute as EloquentAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Sku
 *
 * Representa la variante tangible y comprable de un producto.
 * Es la unidad fundamental para la gestión de inventario y ventas.
 *
 * @property int $id
 * @property int $product_id
 * @property string $sku_code
 * @property float $price
 * @property float|null $sale_price
 * @property float|null $cost_price
 * @property int $stock_quantity
 * @property string|null $mercado_libre_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AttributeValue[] $attributeValues
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Property[] $properties
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CartItem[] $cartItems
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 * @property-read bool $is_on_sale
 * @property-read float $effective_price
 */
class Sku extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'sku_code',
        'price',
        'sale_price',
        'cost_price',
        'stock_quantity',
        'mercado_libre_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    /**
     * Define la relación inversa con el producto.
     * Un SKU pertenece a un único producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Define la relación muchos a muchos con los valores de atributo.
     * La combinación de estos valores define unívocamente la variante.
     * (e.g., [Color: Rojo, Talla: M])
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'sku_attribute_map')
            ->withTimestamps();
    }

    /**
     * Define la relación uno a muchos con las propiedades descriptivas.
     * Estas son propiedades específicas de este SKU.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
    
    /**
     * Define la relación uno a muchos con los items del carrito.
     * Un SKU puede estar en múltiples carritos de compra.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Accessor para determinar si el SKU está en oferta.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function isOnSale(): EloquentAttribute
    {
        return EloquentAttribute::make(
            get: fn () => $this->sale_price !== null && $this->sale_price < $this->price,
        );
    }

    /**
     * Accessor para obtener el precio efectivo del SKU (el de oferta si aplica).
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function effectivePrice(): EloquentAttribute
    {
        return EloquentAttribute::make(
            get: fn () => $this->is_on_sale ? $this->sale_price : $this->price,
        );
    }

    /**
     * Registra las conversiones de medios para el modelo.
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $media
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Conversión para la vista previa en el panel de Nova.
        $this->addMediaConversion('thumbnail')
              ->width(200)
              ->height(200)
              ->sharpen(10)
              ->performOnCollections('default');

        // Conversión a formato WebP para el frontend.
        $this->addMediaConversion('webp')
              ->format('webp')
              ->quality(80)
              ->performOnCollections('default');
    }
}
