<?php 

use App\Http\Controllers\Nova\AttributeToolsController;
use App\Http\Controllers\Nova\ProductAttributeValueController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get(
        '/nova-tools/attribute/{attribute}/check-display-type',
        [AttributeToolsController::class, 'checkDisplayType']
    )->name('nova.tools.attribute.check_display_type');
});

Route::middleware('auth:sanctum')->get('/api/nova/product-attribute-values', [ProductAttributeValueController::class, 'index']);
