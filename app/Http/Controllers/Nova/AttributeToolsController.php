<?php

namespace App\Http\Controllers\Nova;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\JsonResponse;

class AttributeToolsController extends Controller
{
    /**
     * Check the display type of a given attribute and return it.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkDisplayType(Attribute $attribute): JsonResponse
    {
        return response()->json([
            'display_type' => $attribute->display_type,
            'is_color_swatch' => $attribute->display_type === 'color_swatch',
        ]);
    }
}
