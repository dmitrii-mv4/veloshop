<?php

namespace App\Modules\Menu\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use App\Modules\Menu\Models\Menu;

class ApiController extends Controller
{
    public function index(): JsonResponse
    {
        // Получаем первую запись настроек и скрываем ненужные поля
        $menu = Menu::All();

        // Если запись не найдена
        if (!$menu) {
            return response()->json([
                'menu' => [
                    
                ]
            ]);
        }

        // Преобразуем в массив и структурируем
        $apiApp = [
            'menu' => $menu->toArray(),
        ];

        return response()->json($apiApp);
    }
}