<?php

namespace App\Nova;

use App\Models\Attribute as AttributeModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Color;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class AttributeValue extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\AttributeValue>
     */
    public static $model = \App\Models\AttributeValue::class;

    /** Agrupación en el sidebar */
    public static $group = 'Catalog - Variations';

    /** Campo que se muestra como título */
    public static $title = 'value';

    /** Búsqueda */
    public static $search = ['id', 'value', 'metadata->name', 'metadata->hex'];

    /** Subtítulo en index */
    public function subtitle()
    {
        return $this->attribute ? "Attribute: {$this->attribute->name}" : '';
    }

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(NovaRequest $request)
    {
        // Helper to resolve the display type from the selected Attribute.
        $resolveDisplayType = function (FormData $form): ?string {
            $attributeId = (int) $form->resource(\App\Nova\Attribute::uriKey(), $form->attribute);
            return optional(AttributeModel::find($attributeId))->display_type;
        };

        return [
            ID::make()->sortable(),

            BelongsTo::make('Attribute')
                ->rules('required')
                ->searchable(), // Base field for dependencies.

            // Custom display for index and detail views
            Text::make('Display', function () {
                // 1. Handle color_swatch
                $name = data_get($this->metadata, 'name');
                $hex  = data_get($this->metadata, 'hex');
                if ($name && $hex) {
                    return <<<HTML
                        <div class="flex items-center">
                            <div class="w-6 h-6 rounded-md border border-gray-200 dark:border-gray-700 mr-2" style="background-color: {$hex}"></div>
                            <span>{$name} ({$hex})</span>
                        </div>
                    HTML;
                }

                // 2. Handle image_swatch by showing the 'swatch' conversion
                $swatchUrl = $this->getFirstMediaUrl('swatch_image', 'swatch');
                if ($swatchUrl) {
                    return <<<HTML
                        <div class="flex items-center">
                            <img src="{$swatchUrl}" class="w-8 h-8 rounded-md object-cover mr-2" alt="{$this->value}">
                            <span>{$this->value}</span>
                        </div>
                    HTML;
                }

                // 3. Fallback to standard value
                return $this->value ?: null;
            })->exceptOnForms()->asHtml(),


            // =================== DYNAMIC FORM FIELDS ===================

            // 1) Standard Flow -> 'Value' field
            Text::make('Value', 'value')
                ->onlyOnForms()
                ->hide() // Start hidden
                ->dependsOn(['attribute'], function (Text $field, NovaRequest $request, FormData $form) use ($resolveDisplayType) {
                    $type = $resolveDisplayType($form);
                    if ($type === 'color_swatch' || $type === 'image_swatch') {
                        $field->hide()->rules('nullable')->setValue(null);
                    } else {
                        $field->show()
                            ->rules('required', 'string', 'max:100')
                            ->help('Ej.: "M", "L", "XL".');
                    }
                }),

            // 2) Color Flow -> "Color Name" and "Color Hex Code" fields
            Text::make('Color Name', 'metadata->name')
                ->onlyOnForms()
                ->hide()
                ->dependsOn(['attribute'], function (Text $field, NovaRequest $request, FormData $form) use ($resolveDisplayType) {
                    if ($resolveDisplayType($form) === 'color_swatch') {
                        $field->show()->rules('required', 'string', 'max:100');
                    } else {
                        $field->hide()->rules('nullable')->setValue(null);
                    }
                }),

            Color::make('Color Hex Code', 'metadata->hex')
                ->onlyOnForms()
                ->hide()
                ->dependsOn(['attribute'], function (Color $field, NovaRequest $request, FormData $form) use ($resolveDisplayType) {
                    if ($resolveDisplayType($form) === 'color_swatch') {
                        $field->show()->rules('required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/');
                    } else {
                        $field->hide()->rules('nullable')->setValue(null);
                    }
                }),

            // 3) Image Swatch Flow -> Native Nova Image field integrated with Spatie Media Library
            Image::make('Swatch Image', 'swatch_image_upload') // Use a virtual attribute
                ->onlyOnForms()
                ->hide()
                ->dependsOn(['attribute'], function (Image $field, NovaRequest $request, FormData $form) use ($resolveDisplayType) {
                    if ($resolveDisplayType($form) === 'image_swatch') {
                        $field->show()->rules(['required', 'image', 'max:1024']);
                    } else {
                        $field->hide()->rules(['nullable']);
                    }
                })
                ->thumbnail(function ($value, $disk, $model) {
                    // Show the 'swatch' conversion as the thumbnail in the form
                    return $model->getFirstMediaUrl('swatch_image', 'swatch');
                })
                ->store(function (Request $request, $model) {
                    // Intercept the upload and delegate it to Spatie Media Library
                    if ($request->hasFile('swatch_image_upload')) {
                        // Clear previous image before adding the new one
                        $model->clearMediaCollection('swatch_image');
                        $model->addMediaFromRequest('swatch_image_upload')->toMediaCollection('swatch_image');
                    }
                    return []; // Return an empty array to prevent Nova from trying to save to a column
                })
                ->delete(function (Request $request, $model) {
                    // Handle file deletion from the form
                    $model->clearMediaCollection('swatch_image');
                    return [];
                }),

            // Sort Order field (always visible)
            Number::make('Sort Order', 'sort_order')
                ->rules('required', 'integer', 'min:0')
                ->default(0)
                ->sortable(),
        ];
    }
}

