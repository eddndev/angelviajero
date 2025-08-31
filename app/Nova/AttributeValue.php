<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class AttributeValue extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\AttributeValue>
     */
    public static $model = \App\Models\AttributeValue::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'value';

    /**
     * Get the subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        return "Attribute: {$this->attribute->name}";
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'value',
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

            BelongsTo::make('Attribute')
                ->searchable()
                ->rules('required'),

            Text::make('Value')
                ->sortable()
                ->rules('required', 'string', 'max:100'),

            // Campo de código para gestionar el JSON de metadata.
            Code::make('Metadata')
                ->json()
                ->nullable()
                ->help('E.g., {"hex": "#FF0000"} for a color swatch.'),

            Number::make('Sort Order')
                ->sortable()
                ->rules('required', 'integer', 'min:0')
                ->default(0),
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
     * @param  \Laravel\Nova\Http\requests\NovaRequest  $request
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
