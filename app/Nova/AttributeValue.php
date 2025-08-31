<?php

namespace App\Nova;

use App\Models\Attribute as AttributeModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Color;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
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
        // Helper para obtener el tipo de display del atributo seleccionado (en create/edit).
        $resolveDisplayType = function (FormData $form): ?string {
            // Obtiene el ID del recurso relacionado de forma segura desde el FormData (API oficial).
            // https://nova.laravel.com/docs/v5/resources/dependent-fields#accessing-request-resource-ids
            $attributeId = (int) $form->resource(\App\Nova\Attribute::uriKey(), $form->attribute);
            return optional(AttributeModel::find($attributeId))->display_type;
        };

        return [
            ID::make()->sortable(),

            BelongsTo::make('Attribute')
                ->rules('required')
                ->searchable(), // BelongsTo searchable funciona como campo “base” de dependencias.

            // --- Vista de lectura (no formularios): muestra un valor unificado amigable ---
            Text::make('Display', function () {
                // Prefiere nombre de color si existe; si no, value estándar.
                $name = data_get($this->metadata, 'name');
                $hex  = data_get($this->metadata, 'hex');
                if ($name && $hex) {
                    return "{$name} ({$hex})";
                }
                return $this->value ?: null;
            })->exceptOnForms(),

            // =================== CAMPOS DEL FORMULARIO (solo en forms) ===================

            // 1) Flujo estándar → campo único "Value"
            Text::make('Value', 'value')
                ->onlyOnForms()
                ->hide() // arranca oculto; se mostrará si NO es color
                ->dependsOn(['attribute'], function (Text $field, NovaRequest $request, FormData $form) use ($resolveDisplayType) {
                    $type = $resolveDisplayType($form);
                    if ($type === 'color_swatch') {
                        // Oculta y limpia cuando el atributo es de color
                        $field->hide()->rules('nullable')->setValue(null);
                    } else {
                        $field->show()
                            ->rules('required', 'string', 'max:100')
                            ->help('Ej.: "M", "L", "XL".');
                    }
                }),

            // 2) Flujo color → "Color Name"
            Text::make('Color Name', 'metadata->name')
                ->onlyOnForms()
                ->hide()
                ->dependsOn(['attribute'], function (Text $field, NovaRequest $request, FormData $form) use ($resolveDisplayType) {
                    $type = $resolveDisplayType($form);
                    if ($type === 'color_swatch') {
                        $field->show()
                            ->rules('required', 'string', 'max:100')
                            ->help('Nombre legible: "Rojo intenso", "Azul cielo", etc.');
                    } else {
                        $field->hide()->rules('nullable')->setValue(null);
                    }
                }),

            // 3) Flujo color → selector HEX (usa <input type="color"> nativo de Nova)
            Color::make('Color Hex Code', 'metadata->hex')
                ->onlyOnForms()
                ->hide()
                ->dependsOn(['attribute'], function (Color $field, NovaRequest $request, FormData $form) use ($resolveDisplayType) {
                    $type = $resolveDisplayType($form);
                    if ($type === 'color_swatch') {
                        $field->show()
                            ->rules('required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/')
                            ->help('Selecciona o pega un HEX válido, p. ej. #B91C1C.');
                    } else {
                        $field->hide()->rules('nullable')->setValue(null);
                    }
                }),

            // Orden
            Number::make('Sort Order', 'sort_order')
                ->rules('required', 'integer', 'min:0')
                ->default(0)
                ->sortable(),
        ];
    }
}

