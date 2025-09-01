<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Product
 *
 * Representa la entidad conceptual de un producto en el catálogo.
 * Agrupa un conjunto de variantes (SKUs) y define sus características generales.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $brand
 * @property string|null $mercado_libre_url
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categories
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sku[] $skus
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Attribute[] $attributes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Property[] $properties
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 */
class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'brand',
        'mercado_libre_url',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * Adjunta la lógica para la generación automática del slug
     * antes de guardar el modelo.
     */
    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if ($product->isDirty('name') || empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Define la relación muchos a muchos con las categorías.
     * Un producto puede pertenecer a múltiples categorías.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category_map')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Define la relación uno a muchos con los SKUs.
     * Un producto tiene muchas variantes (SKUs).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skus(): HasMany
    {
        return $this->hasMany(Sku::class);
    }

    /**
     * Define la relación muchos a muchos con los atributos de variación.
     * Esto determina qué atributos (ej. Color, Talla) se usarán para
     * generar las variantes de este producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute_map')
            ->withTimestamps();
    }

    /**
     * Define la relación uno a muchos con las propiedades descriptivas.
     * Estas son propiedades a nivel de producto (comunes a todos los SKUs).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function properties(): HasMany
    {
        // Se asegura de traer solo las propiedades cuyo sku_id es nulo,
        // es decir, las que pertenecen directamente al producto.
        return $this->hasMany(Property::class)->whereNull('sku_id');
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
              ->performOnCollections('default'); // 'default' es el nombre que usas en Nova.

        // Conversión a formato WebP para el frontend.
        $this->addMediaConversion('webp')
              ->format('webp')
              ->quality(80) // Calidad del 80%
              ->performOnCollections('default');
    }
}
