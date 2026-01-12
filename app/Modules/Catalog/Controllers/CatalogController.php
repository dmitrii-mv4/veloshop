<?php

namespace App\Modules\Catalog\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Контроллер для управления каталогом товаров
 */
class CatalogController extends Controller
{   
    /**
     * Отображение списка товаров
     */
    public function index()
    {
        return view('catalog::index');
    }
}