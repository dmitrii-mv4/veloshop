<?php

namespace App\Modules\Page\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use App\Modules\Page\Models\Page;

class ApiController extends Controller
{
    public function index(): JsonResponse
    {
        // Получаем первую запись настроек и скрываем ненужные поля
        $pages = Page::All();

        // Если запись не найдена
        if (!$pages) {
            return response()->json([
                'pages' => [
                    
                ]
            ]);
        }

        // Преобразуем в массив и структурируем
        $apiApp = [
            'pages' => $pages->toArray(),
        ];

        return response()->json($apiApp);
    }
}