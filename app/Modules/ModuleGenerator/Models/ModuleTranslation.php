<?php

namespace App\Modules\ModuleGenerator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Модель для работы с таблицами переводов модулей
 * 
 * Модель динамически определяет имя таблицы на основе названия модуля
 * Используется для получения переводов интерфейса модулей на разные языки
 */
class ModuleTranslation extends Model
{
    /**
     * @var string Динамическое имя таблицы
     */
    protected $table;

    /**
     * @var array Массив доступных столбцов языков
     */
    protected $availableLanguages = [];

    /**
     * Конструктор модели
     *
     * @param array $attributes
     * @param string|null $tableName Имя таблицы переводов
     */
    public function __construct(array $attributes = [], string $tableName = null)
    {
        parent::__construct($attributes);

        if ($tableName) {
            $this->table = $tableName;
            $this->detectAvailableLanguages();
        }
    }

    /**
     * Установить имя таблицы для модели
     *
     * @param string $tableName Имя таблицы
     * @return $this
     */
    public function setTable($tableName)
    {
        $this->table = $tableName;
        $this->detectAvailableLanguages();
        return $this;
    }

    /**
     * Получить имя таблицы
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table ?? '';
    }

    /**
     * Определить доступные языковые колонки в таблице
     *
     * @return void
     */
    protected function detectAvailableLanguages(): void
    {
        if (!$this->table) {
            return;
        }

        try {
            // Получаем информацию о столбцах таблицы
            $columns = DB::getSchemaBuilder()->getColumnListing($this->table);
            
            // Определяем языковые колонки (исключаем служебные поля)
            $excludedColumns = ['id', 'code', 'created_at', 'updated_at', 'deleted_at'];
            $this->availableLanguages = array_filter($columns, function($column) use ($excludedColumns) {
                return !in_array($column, $excludedColumns) && strlen($column) <= 5;
            });

            Log::debug('Детектированы языковые колонки для таблицы ' . $this->table . ': ' . implode(', ', $this->availableLanguages));
        } catch (\Exception $e) {
            Log::error('Ошибка при детектировании языковых колонок для таблицы ' . $this->table . ': ' . $e->getMessage());
            $this->availableLanguages = [];
        }
    }

    /**
     * Получить доступные языки
     *
     * @return array
     */
    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }

    /**
     * Получить перевод по коду и языку
     *
     * @param string $code Код перевода
     * @param string $language Язык перевода
     * @return string|null
     */
    public function getTranslation(string $code, string $language): ?string
    {
        if (!$this->table) {
            Log::warning('Попытка получить перевод без установленной таблицы');
            return null;
        }

        if (!in_array($language, $this->availableLanguages)) {
            Log::warning('Запрошенный язык "' . $language . '" недоступен в таблице ' . $this->table . '. Доступные языки: ' . implode(', ', $this->availableLanguages));
            return null;
        }

        try {
            $translation = $this->where('code', $code)->value($language);
            
            if (is_null($translation)) {
                Log::debug('Перевод с кодом "' . $code . '" для языка "' . $language . '" не найден в таблице ' . $this->table);
            }
            
            return $translation;
        } catch (\Exception $e) {
            Log::error('Ошибка при получении перевода из таблицы ' . $this->table . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Проверить существование таблицы
     *
     * @param string $tableName Имя таблицы
     * @return bool
     */
    public static function tableExists(string $tableName): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($tableName);
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке существования таблицы ' . $tableName . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверить существование кода перевода
     *
     * @param string $code Код перевода
     * @return bool
     */
    public function codeExists(string $code): bool
    {
        if (!$this->table) {
            return false;
        }

        try {
            return $this->where('code', $code)->exists();
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке существования кода "' . $code . '" в таблице ' . $this->table . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить все переводы для определенного языка
     *
     * @param string $language Язык
     * @return array
     */
    public function getAllTranslations(string $language): array
    {
        if (!$this->table) {
            Log::warning('Попытка получить все переводы без установленной таблицы');
            return [];
        }

        if (!in_array($language, $this->availableLanguages)) {
            Log::warning('Запрошенный язык "' . $language . '" недоступен для получения всех переводов в таблице ' . $this->table);
            return [];
        }

        try {
            return $this->pluck($language, 'code')->toArray();
        } catch (\Exception $e) {
            Log::error('Ошибка при получении всех переводов для языка "' . $language . '" из таблицы ' . $this->table . ': ' . $e->getMessage());
            return [];
        }
    }
}