<?php

namespace App\Nova;

use App\Http\Requests\StoreSkuRequest;
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
use Outl1ne\MultiselectField\Multiselect;

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

                // 1. Este es el campo del que dependeremos
                BelongsTo::make('Product')->searchable(),

                Text::make('SKU Code', 'sku_code')->sortable(),
            ]),

            new Panel('Pricing & Stock', [
                Currency::make('Price')->sortable(),
                Currency::make('Sale Price')->nullable(),
                Currency::make('Cost Price')->nullable()->onlyOnForms(),
                Number::make('Stock Quantity')->sortable(),
            ]),

            new Panel('Variant Definition (EAV)', [
                Multiselect::make('Attribute Values', 'attributeValues')
                    ->belongsToMany(\App\Nova\AttributeValue::class)
                    // Quitamos ->api() de aquí. Se definirá dinámicamente.
                    
                    ->dependsOn('product', function ($field, $request, $formData) {
                        // Esta función se ejecuta cuando el campo 'product' cambia.
                        // $formData contiene todos los datos del formulario actual.
                        $productId = $formData->product;

                        if ($productId) {
                            // Si hay un ID de producto, construimos la URL y la asignamos
                            // al campo usando withMeta(). Esto envía la URL al componente frontend.
                            $field->withMeta([
                                'apiUrl' => '/api/nova/product-attribute-values?product_id=' . $productId,
                            ]);
                        }
                    })
                    
                    ->reorderable()
                    ->help('Select a product to see its available attribute values.')
                    ->displayUsing(function ($values) {
                        return $values->pluck('value')->implode(', ');
                    }),
            ]),

            new Panel('External Links & Media', [
                URL::make('Mercado Libre URL')->displayUsing(fn () => 'Link')->nullable(),
                Images::make('Images', 'default')
                    ->conversionOnIndexView('thumbnail')
                    ->conversionOnPreview('thumbnail')
                    ->setFileName(function($originalFilename, $extension, $model){
                        return md5($originalFilename . time()) . '.' . $extension;
                    }),
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

    public static function rulesForCreation(NovaRequest $request): array
    {
        return (new StoreSkuRequest)->getRulesForModel();
    }

    public static function rulesForUpdate(NovaRequest $request, ?\Laravel\Nova\Resource $resource = null): array
    {
        // Al actualizar, pasamos el resource actual para que sea ignorado por la regla 'unique'.
        return (new StoreSkuRequest)->getRulesForModel($resource->resource);
    }
}

