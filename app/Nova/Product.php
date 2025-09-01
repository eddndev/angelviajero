<?php

namespace App\Nova;

use App\Http\Requests\StoreProductRequest;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Panel;
use Laravel\Nova\Http\Requests\NovaRequest;

class Product extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Product>
     */
    public static $model = \App\Models\Product::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'brand',
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
            // Panel principal con los datos del producto base.
            new Panel('Product Details', [
                ID::make()->sortable(),

                Text::make('Name')
                    ->sortable()
                    ->rules('required', 'string', 'max:255'),

                // Asumimos que un observador en el modelo Product se encarga de generar el slug.
                Text::make('Slug')
                    ->sortable()
                    ->exceptOnForms(),

                Trix::make('Description')
                    ->withFiles('public'), // Habilita la subida de imágenes en el editor.

                Text::make('Brand')
                    ->sortable()
                    ->nullable(),

                URL::make('Mercado Libre URL')
                    ->displayUsing(fn () => 'Link')
                    ->nullable(),

                Boolean::make('Is Active')
                    ->default(true),

                // Campo para gestionar las imágenes polimórficas con el paquete avanzado.
                Images::make('Images', 'default')
                    ->conversionOnIndexView('thumbnail')
                    ->setFileName(function($originalFilename, $extension, $model){
                        // Opcional: Renombra los archivos para mantenerlos organizados.
                        return md5($originalFilename . time()) . '.' . $extension;
                    }),
            ]),

            // Panel para gestionar las relaciones de organización y variación.
            new Panel('Organization & Variations', [
                BelongsToMany::make('Categories'),

                // RF-ADM-004: Mapeo de Atributos a Productos.
                // Aquí el admin selecciona qué atributos globales se aplicarán a este producto.
                BelongsToMany::make('Attributes'),
            ]),
            
            // Paneles para visualizar datos relacionados (SKUs y Propiedades).
            // Estos campos solo aparecerán en la vista de detalle del producto.
            new Panel('Associated Data', [
                HasMany::make('SKUs'),
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public static function rulesForCreation(NovaRequest $request): array
    {
        return (new StoreProductRequest())->rules();
    }

    /**
     * Get the validation rules that apply to the request for update.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public static function rulesForUpdate(NovaRequest $request, ?\Laravel\Nova\Resource $resource = null): array
    {
        return (new StoreProductRequest)->rules();
    }
}