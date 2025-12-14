<?php

namespace App\Admin\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use App\Admin\Models\Settings;

class AppController extends Controller
{
    public function index(): JsonResponse
    {
        // Получаем первую запись настроек и скрываем ненужные поля
        $settings = Settings::first()->makeHidden(['created_at', 'updated_at', 'deleted_at']);

        // Если запись не найдена
        if (!$settings) {
            return response()->json([
                'site' => [
                    'name' => config('app.name', 'Мой сайт'),
                    'description' => 'Описание сайта по умолчанию',
                ]
            ]);
        }

        // Преобразуем в массив и структурируем
        $apiApp = [
            'site' => $settings->toArray(),
        ];

        return response()->json($apiApp);
    }
}