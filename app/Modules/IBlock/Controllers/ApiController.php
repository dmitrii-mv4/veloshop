<?php

namespace App\Modules\IBlock\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use App\Modules\IBlock\Models\IBlock;

class ApiController extends Controller
{
    public function index(): JsonResponse
    {
        // Получаем первую запись настроек и скрываем ненужные поля
        $iblocks = IBlock::All();

        // Если запись не найдена
        if (!$iblocks) {
            return response()->json([
                'iblocks' => [
                    
                ]
            ]);
        }

        // Преобразуем в массив и структурируем
        $apiApp = [
            'iblocks' => $iblocks->toArray(),
        ];

        return response()->json($apiApp);
    }
}