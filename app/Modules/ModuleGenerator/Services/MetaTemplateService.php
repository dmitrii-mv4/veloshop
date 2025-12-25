<?php

namespace App\Modules\ModuleGenerator\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Сервис для обработки шаблонных мета-тегов
 * Поддерживает подстановку переменных вида {variable} из данных записи
 * 
 * Пример использования:
 * $metaTitle = "Новость: {title} | {site_name}"
 * $data = ['title' => 'Новая версия CMS', 'site_name' => 'Kotiks']
 * Результат: "Новость: Новая версия CMS | Kotiks"
 */
class MetaTemplateService
{
    
}