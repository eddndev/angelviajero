<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\AttributeValue
 *
 * Represents a specific option for a given Attribute (e.g., "Red" for "Color").
 * This is a core component of the EAV (Entity-Attribute-Value) system for product variations.
 */
class AttributeValue extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saving(function (self $attributeValue) {
            // This logic ensures the 'value' column, which has a NOT NULL constraint,
            // is always populated with a sensible default, maintaining data integrity.

            // 1. Prioritize the color name from metadata if it exists.
            $colorName = data_get($attributeValue->metadata, 'name');
            if ($colorName) {
                $attributeValue->value = $colorName;
                return; // Exit early if we have a color name.
            }

            // 2. If it's a new model and the 'value' is empty (which happens with image_swatch),
            // we will temporarily set a placeholder. Spatie Media Library runs its processes
            // *after* the initial model save, so we can't get the filename yet.
            if (is_null($attributeValue->value) && !$attributeValue->exists) {
                 $attributeValue->value = 'image_placeholder_' . uniqid();
            }
        });

        // Use the 'saved' event to get access to the media file *after* it has been processed by Spatie.
        static::saved(function (self $attributeValue) {
            // Check if the value is our temporary placeholder.
            if (str_starts_with($attributeValue->value, 'image_placeholder_')) {
                // Get the first media item from the 'swatch_image' collection.
                $media = $attributeValue->getFirstMedia('swatch_image');
                if ($media) {
                    // Update the value with the actual filename and save without triggering events.
                    $attributeValue->value = $media->file_name;
                    $attributeValue->saveQuietly();
                }
            }
        });
    }

    /**
     * Define the media collections and their conversions.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('swatch_image')
            ->singleFile() // Restrict to a single file
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('swatch')
                    ->width(50)
                    ->height(50)
                    ->sharpen(10);
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(Sku::class, 'sku_attribute_map');
    }
}
