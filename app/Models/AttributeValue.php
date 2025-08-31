<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\AttributeValue
 *
 * Represents a specific option for a given Attribute (e.g., "Red" for "Color").
 * This is a core component of the EAV (Entity-Attribute-Value) system for product variations.
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
     * This prevents mass assignment vulnerabilities.
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
     * The attributes that should be cast to native types.
     * 'metadata' is cast to an array, allowing easy access to JSON data.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The "booted" method of the model.
     * This is the ideal place to register model event listeners that handle
     * business logic and data integrity.
     *
     * @return void
     */
    protected static function booted(): void
    {
        // Register a listener for the "saving" event. This hook runs automatically
        // whenever a model is created or updated, just before it's written to the database.
        static::saving(function (self $attributeValue) {
            // Use Laravel's data_get helper to safely access the nested 'name' key from the metadata JSON.
            // This prevents errors if 'metadata' is null or 'name' doesn't exist.
            $colorName = data_get($attributeValue->metadata, 'name');
            
            // If a color name exists in the metadata, we ensure the main 'value' column
            // is populated with it. This architectural decision solves two problems:
            // 1. It satisfies the NOT NULL database constraint on the 'value' column.
            // 2. It keeps the primary textual representation consistent for searching and display purposes.
            if ($colorName) {
                $attributeValue->value = $colorName;
            }
        });
    }

    /**
     * Get the parent Attribute that this value belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get all of the SKUs that are assigned this attribute value.
     * This defines the many-to-many relationship via the 'sku_attribute_map' pivot table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(Sku::class, 'sku_attribute_map');
    }
}

