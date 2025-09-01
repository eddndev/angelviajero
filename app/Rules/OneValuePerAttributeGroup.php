<?php

namespace App\Rules;

use App\Models\AttributeValue;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;

class OneValuePerAttributeGroup implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value) || empty($value)) {
            return;
        }

        // Obtenemos los IDs de los atributos padres de cada valor seleccionado
        $parentAttributeIds = AttributeValue::whereIn('id', $value)->pluck('attribute_id');

        // Contamos las ocurrencias de cada ID de atributo padre
        $counts = $parentAttributeIds->countBy();
        
        // Buscamos si algún atributo padre se repite
        $duplicates = $counts->filter(fn ($count) => $count > 1);

        if ($duplicates->isNotEmpty()) {
            $fail('A SKU cannot have multiple values from the same attribute group.');
        }
    }
}