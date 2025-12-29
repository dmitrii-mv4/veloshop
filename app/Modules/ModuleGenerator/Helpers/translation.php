<?php

/**
 * Helper функция для получения переводов модулей в Blade шаблонах
 *
 * @param string $moduleName Название модуля
 * @param string $code Код перевода
 * @param string|null $language Язык (опционально)
 * @return string
 */
function module_trans(string $moduleName, string $code, ?string $language = null): string
{
    try {
        $translationService = app(\App\Modules\ModuleGenerator\Services\TranslationService::class);
        $translation = $translationService->getTranslation($moduleName, $code, $language);
        
        // Если перевод не найден, возвращаем код
        return $translation ?? $code;
    } catch (\Exception $e) {
        // Логируем ошибку, но не падаем в production
        if (config('app.debug')) {
            \Illuminate\Support\Facades\Log::error('Ошибка в функции module_trans: ' . $e->getMessage(), [
                'module' => $moduleName,
                'code' => $code,
                'language' => $language
            ]);
        }
        
        // В режиме отладки показываем код с пояснением
        if (config('app.debug')) {
            return '[Ошибка перевода: ' . $code . ']';
        }
        
        return $code;
    }
}

/**
 * Получить все переводы для модуля
 *
 * @param string $moduleName Название модуля
 * @param string|null $language Язык (опционально)
 * @return array
 */
function module_all_trans(string $moduleName, ?string $language = null): array
{
    try {
        $translationService = app(\App\Modules\ModuleGenerator\Services\TranslationService::class);
        return $translationService->getAllModuleTranslations($moduleName, $language);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Ошибка в функции module_all_trans: ' . $e->getMessage());
        return [];
    }
}