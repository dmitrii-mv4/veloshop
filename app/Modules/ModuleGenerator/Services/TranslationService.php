<?php

namespace App\Modules\ModuleGenerator\Services;

use App\Modules\ModuleGenerator\Models\ModuleTranslation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с переводами модулей
 * 
 * Предоставляет функционал для получения переводов из таблиц {module}_trans
 * с автоматическим определением языка текущего пользователя
 */
class TranslationService
{
    /**
     * @var ModuleTranslation Модель для работы с переводами
     */
    private ModuleTranslation $translationModel;

    /**
     * @var string Язык по умолчанию
     */
    private string $defaultLanguage = 'ru';

    /**
     * @var array Кэш доступных языков по таблицам
     */
    private array $availableLanguagesCache = [];

    /**
     * Конструктор сервиса
     */
    public function __construct()
    {
        // Инициализация модели
        $this->translationModel = new ModuleTranslation();
    }

    /**
     * Получить перевод для модуля
     *
     * @param string $moduleName Название модуля
     * @param string $code Код перевода
     * @param string|null $language Язык перевода (если null, определяется автоматически)
     * @return string|null
     */
    public function getTranslation(string $moduleName, string $code, ?string $language = null): ?string
    {
        // Определяем таблицу переводов
        $translationTable = $this->getTranslationTableName($moduleName);

        // Логирование начала процесса
        Log::info('Запрос перевода', [
            'module' => $moduleName,
            'table' => $translationTable,
            'code' => $code,
            'language' => $language ?? 'auto'
        ]);

        // Проверка существования таблицы
        if (!$this->checkTableExists($translationTable, $moduleName)) {
            return $this->getFallbackTranslation($moduleName, $code);
        }

        // Инициализация модели с таблицей переводов
        $this->translationModel->setTable($translationTable);

        // Определение языка
        $targetLanguage = $language ?? $this->getUserLanguage();

        // Проверка существования кода перевода
        if (!$this->translationModel->codeExists($code)) {
            Log::warning('Код перевода не найден', [
                'module' => $moduleName,
                'table' => $translationTable,
                'code' => $code
            ]);
            return $this->getFallbackTranslation($moduleName, $code);
        }

        // Получение перевода
        $translation = $this->translationModel->getTranslation($code, $targetLanguage);

        // Если перевод для указанного языка не найден, пробуем язык по умолчанию
        if (empty($translation) && $targetLanguage !== $this->defaultLanguage) {
            Log::debug('Перевод для языка "' . $targetLanguage . '" не найден, пробуем язык по умолчанию');
            $translation = $this->translationModel->getTranslation($code, $this->defaultLanguage);
        }

        // Если перевод все еще не найден, возвращаем код
        if (empty($translation)) {
            Log::debug('Перевод не найден, возвращаем код');
            return $code;
        }

        return $translation;
    }

    /**
     * Получить все переводы для модуля на определенном языке
     *
     * @param string $moduleName Название модуля
     * @param string|null $language Язык (если null, определяется автоматически)
     * @return array
     */
    public function getAllModuleTranslations(string $moduleName, ?string $language = null): array
    {
        $translationTable = $this->getTranslationTableName($moduleName);

        if (!ModuleTranslation::tableExists($translationTable)) {
            Log::error('Таблица переводов не существует', ['table' => $translationTable]);
            return [];
        }

        $model = new ModuleTranslation([], $translationTable);
        $targetLanguage = $language ?? $this->getUserLanguage();

        return $model->getAllTranslations($targetLanguage);
    }

    /**
     * Получить имя таблицы переводов для модуля
     *
     * @param string $moduleName Название модуля
     * @return string
     */
    private function getTranslationTableName(string $moduleName): string
    {
        return strtolower($moduleName) . '_trans';
    }

    /**
     * Получить язык текущего пользователя
     *
     * @return string
     */
    private function getUserLanguage(): string
    {
        try {
            $user = Auth::user();
            
            if ($user && isset($user->is_lang) && !empty($user->is_lang)) {
                return $user->is_lang;
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при получении языка пользователя: ' . $e->getMessage());
        }

        // Возвращаем язык по умолчанию, если не удалось определить
        return $this->defaultLanguage;
    }

    /**
     * Проверить существование таблицы переводов
     *
     * @param string $tableName Имя таблицы
     * @param string $moduleName Название модуля
     * @return bool
     */
    private function checkTableExists(string $tableName, string $moduleName): bool
    {
        $exists = ModuleTranslation::tableExists($tableName);
        
        if (!$exists) {
            Log::error('Таблица переводов не существует', [
                'module' => $moduleName,
                'table' => $tableName,
                'user_id' => Auth::id()
            ]);
        }

        return $exists;
    }

    /**
     * Получить запасной перевод (код или сообщение об ошибке)
     *
     * @param string $moduleName Название модуля
     * @param string $code Код перевода
     * @return string
     */
    private function getFallbackTranslation(string $moduleName, string $code): string
    {
        // В режиме разработки возвращаем понятное сообщение
        if (config('app.debug')) {
            return '[Перевод: ' . $code . ']';
        }

        // В production возвращаем просто код
        return $code;
    }

    /**
     * Получить доступные языки для таблицы переводов
     *
     * @param string $moduleName Название модуля
     * @return array
     */
    public function getAvailableLanguages(string $moduleName): array
    {
        $translationTable = $this->getTranslationTableName($moduleName);
        $cacheKey = $translationTable . '_languages';

        if (isset($this->availableLanguagesCache[$cacheKey])) {
            return $this->availableLanguagesCache[$cacheKey];
        }

        if (!ModuleTranslation::tableExists($translationTable)) {
            Log::warning('Попытка получить языки для несуществующей таблицы', ['table' => $translationTable]);
            $this->availableLanguagesCache[$cacheKey] = [$this->defaultLanguage];
            return $this->availableLanguagesCache[$cacheKey];
        }

        $model = new ModuleTranslation([], $translationTable);
        $languages = $model->getAvailableLanguages();

        // Если нет доступных языков, возвращаем язык по умолчанию
        if (empty($languages)) {
            $languages = [$this->defaultLanguage];
        }

        $this->availableLanguagesCache[$cacheKey] = $languages;
        return $languages;
    }

    /**
     * Добавить новый язык в систему
     *
     * @param string $languageCode Код языка (например, 'de', 'fr')
     * @return bool
     */
    public function addLanguage(string $languageCode): bool
    {
        try {
            // Получаем все модули из таблицы modules
            $modules = \App\Models\Modules::where('active', true)->get();
            
            foreach ($modules as $module) {
                $tableName = strtolower($module->name) . '_trans';
                
                if (ModuleTranslation::tableExists($tableName)) {
                    // Добавляем колонку для нового языка, если ее еще нет
                    if (!\Illuminate\Support\Facades\Schema::hasColumn($tableName, $languageCode)) {
                        \Illuminate\Support\Facades\Schema::table($tableName, function ($table) use ($languageCode) {
                            $table->string($languageCode, 250)->nullable()->comment('Перевод на ' . $languageCode . ' языке');
                        });
                        
                        Log::info('Добавлен новый язык в таблицу переводов', [
                            'table' => $tableName,
                            'language' => $languageCode
                        ]);
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка при добавлении нового языка: ' . $e->getMessage());
            return false;
        }
    }
}