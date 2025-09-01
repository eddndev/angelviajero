<?php

namespace App\Http\Controllers\Nova;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductAttributeValueController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', Rule::exists('products', 'id')],
            'search' => ['nullable', 'string'],
        ]);

        $productId = $data['product_id'];
        $searchQuery = $data['search'] ?? null;

        // Construimos la consulta para obtener los valores de atributo
        $query = AttributeValue::query()
            // Cargamos la relación con el atributo padre para obtener el nombre del grupo
            ->with('attribute')
            // Filtramos para obtener solo valores cuyos atributos estén asociados al producto
            ->whereHas('attribute.products', function ($query) use ($productId) {
                $query->where('products.id', $productId);
            })
            // Ordenamos por el nombre del atributo (grupo) y luego por el valor
            ->join('attributes', 'attribute_values.attribute_id', '=', 'attributes.id')
            ->orderBy('attributes.name')
            ->orderBy('attribute_values.value');

        // Si hay un término de búsqueda, lo aplicamos
        if ($searchQuery) {
            $query->where('attribute_values.value', 'like', "%{$searchQuery}%");
        }

        $attributeValues = $query->select('attribute_values.*')->get();

        // Mapeamos los resultados al formato que espera el campo Multiselect
        // id => ['label' => 'Rojo', 'group' => 'Color']
        $options = $attributeValues->mapWithKeys(function (AttributeValue $value) {
            return [
                $value->id => [
                    'label' => $value->value,
                    'group' => $value->attribute->name,
                ]
            ];
        });

        return response()->json($options);
    }
}