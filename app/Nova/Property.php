<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class Property extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Property>
     */
    public static $model = \App\Models\Property::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'property_name';

    /**
     * Get the subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        // Provide context by showing what the property is attached to.
        if ($this->product) {
            return "For Product: {$this->product->name}";
        }
        if ($this->sku) {
            return "For SKU: {$this->sku->sku_code}";
        }
        return null;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'property_name', 'property_value',
    ];

    /**
     * The group that this resource belongs to.
     *
     * @var string
     */
    public static $group = 'Catalog';


    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Property Name')
                ->sortable()
                ->rules('required', 'string', 'max:100'),

            Text::make('Property Value')
                ->sortable()
                ->rules('required', 'string', 'max:255'),

            // The property can belong to a Product (general property)...
            BelongsTo::make('Product')
                ->searchable()
                ->nullable()
                // This property is required if 'Sku' is not provided.
                ->rules('required_without:sku'),

            // ...or it can belong to a Sku (variant-specific property).
            BelongsTo::make('Sku')
                ->searchable()
                ->nullable()
                // This property is required if 'Product' is not provided.
                ->rules('required_without:product'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
