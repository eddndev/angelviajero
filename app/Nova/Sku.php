<?php

namespace App\Nova;

use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Panel;
use Laravel\Nova\Http\Requests\NovaRequest;

class Sku extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Sku>
     */
    public static $model = \App\Models\Sku::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'sku_code';

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Contracts\Database\Eloquent\Builder  $query
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, \Illuminate\Contracts\Database\Eloquent\Builder $query): \Illuminate\Contracts\Database\Eloquent\Builder
    {
        // Eager load the product relationship on the index view for performance.
        return $query->with('product');
    }

    /**
     * Get the subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        // This provides context in relationship fields and search results.
        if ($this->product) {
            return "Product: {$this->product->name}";
        }
        return null;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'sku_code',
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
            new Panel('Core Details', [
                ID::make()->sortable(),

                BelongsTo::make('Product')
                    ->searchable()
                    ->rules('required'),

                Text::make('SKU Code', 'sku_code')
                    ->sortable()
                    ->rules('required', 'max:100', 'unique:skus,sku_code,{{resourceId}}'),
            ]),

            new Panel('Pricing & Stock', [
                Currency::make('Price')
                    ->sortable()
                    ->rules('required', 'numeric', 'min:0'),

                Currency::make('Sale Price')
                    ->nullable()
                    ->rules('nullable', 'numeric', 'min:0', 'lt:price'),

                Currency::make('Cost Price')
                    ->nullable()
                    ->rules('nullable', 'numeric', 'min:0')
                    ->onlyOnForms(), // Hide from index view for data privacy.

                Number::make('Stock Quantity')
                    ->sortable()
                    ->rules('required', 'integer', 'min:0'),
            ]),

            new Panel('Variant Definition (EAV)', [
                // RF-ADM-005c: This is where the admin defines the specific variant.
                BelongsToMany::make('Attribute Values', 'attributeValues', AttributeValue::class),
            ]),

            new Panel('External Links & Media', [
                URL::make('Mercado Libre URL')
                    ->displayUsing(fn () => 'Link')
                    ->nullable(),

                // SKU-specific images (e.g., the red version of a t-shirt).
                Images::make('Images', 'default')
                    ->conversionOnIndexView('thumbnail')
                    ->multiple(),
            ]),

            new Panel('Associated Data', [
                HasMany::make('Properties'),
            ]),
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

