<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * App\Models\Category
 *
 * Representa una categoría en la jerarquía del catálogo.
 * Este modelo gestiona una estructura de árbol (padre-hijo) y está
 * diseñado para optimizar las consultas de subcategorías mediante
 * una ruta materializada.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $parent_id
 * @property string|null $path
 * @property int $level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|Category[] $children
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products
 */
class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'path',
        'level',
    ];

    /**
     * The "booted" method of the model.
     *
     * Se utiliza para adjuntar la lógica de negocio que se ejecuta durante
     * el ciclo de vida del modelo. En este caso, se asegura de que los campos
     * 'slug', 'level' y 'path' se calculen y asignen automáticamente
     * antes de que una categoría sea guardada.
     */
    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            // Generar el slug a partir del nombre si no existe o el nombre ha cambiado.
            if ($category->isDirty('name') || empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }

            // Calcular el nivel (profundidad) y la ruta materializada.
            if ($category->parent_id) {
                $parent = self::find($category->parent_id);
                if ($parent) {
                    $category->level = $parent->level + 1;
                    $category->path = $parent->path . $parent->id . '/';
                }
            } else {
                // Es una categoría raíz.
                $category->level = 0;
                $category->path = '/';
            }
        });
    }

    /**
     * Define la relación de una categoría con su padre.
     * Una categoría pertenece a otra categoría (su padre).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Define la relación de una categoría con sus hijos directos.
     * Una categoría puede tener muchas categorías hijas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Define la relación muchos a muchos con los productos.
     * Una categoría puede tener muchos productos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category_map')
            ->withPivot('is_primary')
            ->withTimestamps();
    }
}
