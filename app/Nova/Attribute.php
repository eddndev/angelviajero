<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Attribute extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Attribute>
     */
    public static $model = \App\Models\Attribute::class;

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
        'id', 'name',
    ];

    /**
     * The group that this resource belongs to.
     *
     * @var string
     */
    public static $group = 'Catalog - Variations';

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

            Text::make('Name')
                ->sortable()
                ->rules('required', 'string', 'max:100', 'unique:attributes,name,{{resourceId}}'),

            // RF-ADM-002: Improved field for display type.
            // We now use a Select field to provide a controlled list of options,
            // improving usability and preventing data entry errors.
            Select::make('Display Type')
                ->options([
                    'button' => 'Buttons (Selectable Text Boxes)',
                    'color_swatch' => 'Color Swatch (Visual Color Selector)',
                    'image_swatch' => 'Image Swatch (Visual Image Selector)',
                ])
                ->sortable()
                ->rules('required')
                ->help('Select how this attribute\'s options should be displayed on the storefront.'),

            Number::make('Sort Order')
                ->sortable()
                ->rules('required', 'integer', 'min:0')
                ->default(0),

            // Relationship to see associated values directly from the attribute's detail view.
            HasMany::make('Attribute Values', 'values', AttributeValue::class),
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

