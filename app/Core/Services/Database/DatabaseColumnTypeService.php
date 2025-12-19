<?php

namespace App\Core\Services\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseColumnTypeService
{
    /**
     * Получает SQL-типы столбцов для указанной таблицы
     *
     * @param string $tableName Название таблицы
     * @param array $excludeTypes Массив типов для исключения (например: ['integer', 'string'])
     * @return array Массив типов столбцов или сообщение об ошибке
     */
    public function getColumnTypes(string $tableName, array $excludeTypes = []): array
    {
        try {
            // Проверяем существование таблицы
            if (!Schema::hasTable($tableName)) {
                return [
                    'success' => false,
                    'error' => "Таблица '{$tableName}' не найдена в базе данных.",
                    'columns' => []
                ];
            }

            // Получаем информацию о столбцах
            $columns = Schema::getColumnListing($tableName);
            
            $columnTypes = [];
            
            foreach ($columns as $column) {
                $type = Schema::getColumnType($tableName, $column);
                
                // Пропускаем типы из исключений
                if (!empty($excludeTypes) && in_array($type, $excludeTypes)) {
                    continue;
                }
                
                $columnTypes[$column] = $type;
            }

            return [
                'success' => true,
                'table' => $tableName,
                'columns' => $columnTypes,
                'count' => count($columnTypes),
                'driver' => DB::connection()->getDriverName()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Ошибка при получении данных: " . $e->getMessage(),
                'columns' => []
            ];
        }
    }

    /**
     * Альтернативный метод с более детальной информацией через DB::select
     *
     * @param string $tableName
     * @param array $excludeTypes Массив типов для исключения
     * @return array
     */
    public function getColumnTypesDetailed(string $tableName, array $excludeTypes = []): array
    {
        try {
            if (!Schema::hasTable($tableName)) {
                return [
                    'success' => false,
                    'error' => "Таблица '{$tableName}' не найдена.",
                    'columns' => []
                ];
            }

            // Получаем детальную информацию о столбцах
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            
            $result = [];
            
            if ($driver === 'mysql') {
                $result = $this->getMySQLColumnTypes($tableName, $excludeTypes);
            } elseif ($driver === 'pgsql') {
                $result = $this->getPostgreSQLColumnTypes($tableName, $excludeTypes);
            } else {
                return [
                    'success' => false,
                    'error' => "Неподдерживаемая СУБД: {$driver}. Поддерживаются только MySQL и PostgreSQL.",
                    'columns' => []
                ];
            }
            
            return $result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'columns' => []
            ];
        }
    }

    /**
     * Получает типы столбцов для MySQL
     */
    private function getMySQLColumnTypes(string $tableName, array $excludeTypes = []): array
    {
        $databaseName = DB::connection()->getDatabaseName();
        
        $columns = DB::select("
            SELECT 
                COLUMN_NAME as column_name,
                DATA_TYPE as data_type,
                COLUMN_TYPE as column_type,
                IS_NULLABLE as is_nullable,
                COLUMN_DEFAULT as column_default,
                EXTRA as extra,
                CHARACTER_MAXIMUM_LENGTH as character_maximum_length,
                NUMERIC_PRECISION as numeric_precision,
                NUMERIC_SCALE as numeric_scale
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [$databaseName, $tableName]);
        
        return $this->processColumnsResult($columns, $tableName, $excludeTypes, 'mysql');
    }

    /**
     * Получает типы столбцов для PostgreSQL
     */
    private function getPostgreSQLColumnTypes(string $tableName, array $excludeTypes = []): array
    {
        $schema = DB::connection()->getConfig('schema') ?? 'public';
        
        $columns = DB::select("
            SELECT 
                column_name,
                data_type,
                udt_name as full_type,
                is_nullable,
                column_default,
                character_maximum_length,
                numeric_precision,
                numeric_scale
            FROM information_schema.columns 
            WHERE table_schema = ? AND table_name = ?
            ORDER BY ordinal_position
        ", [$schema, $tableName]);
        
        return $this->processColumnsResult($columns, $tableName, $excludeTypes, 'pgsql');
    }

    /**
     * Обрабатывает результат запроса и форматирует данные
     */
    private function processColumnsResult(array $columns, string $tableName, array $excludeTypes, string $driver): array
    {
        $result = [];
        
        foreach ($columns as $column) {
            $columnName = $column->column_name;
            $dataType = $column->data_type;
            
            // Пропускаем типы из исключений
            if (!empty($excludeTypes) && in_array($dataType, $excludeTypes)) {
                continue;
            }
            
            if ($driver === 'mysql') {
                $result[$columnName] = [
                    'type' => $dataType,
                    'full_type' => $column->column_type ?? $dataType,
                    'nullable' => $column->is_nullable === 'YES',
                    'default' => $column->column_default,
                    'extra' => $column->extra ?? null,
                    'max_length' => $column->character_maximum_length,
                    'precision' => $column->numeric_precision,
                    'scale' => $column->numeric_scale
                ];
            } elseif ($driver === 'pgsql') {
                $result[$columnName] = [
                    'type' => $dataType,
                    'full_type' => $column->full_type ?? $dataType,
                    'nullable' => $column->is_nullable === 'YES',
                    'default' => $column->column_default,
                    'max_length' => $column->character_maximum_length,
                    'precision' => $column->numeric_precision,
                    'scale' => $column->numeric_scale
                ];
            }
        }
        
        return [
            'success' => true,
            'table' => $tableName,
            'driver' => $driver,
            'columns' => $result,
            'count' => count($result)
        ];
    }

    /**
     * Проверяет доступность подключения к БД
     */
    public function checkConnection(): array
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            $databaseName = $connection->getDatabaseName();
            
            // Пробуем выполнить простой запрос
            $connection->select('SELECT 1');
            
            return [
                'success' => true,
                'driver' => $driver,
                'database' => $databaseName,
                'status' => 'connected'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Ошибка подключения к БД: " . $e->getMessage(),
                'driver' => DB::connection()->getDriverName() ?? 'unknown'
            ];
        }
    }

    /**
     * Получает список всех таблиц в базе данных
     */
    public function getTablesList(): array
    {
        try {
            $driver = DB::connection()->getDriverName();
            $tables = [];
            
            if ($driver === 'mysql') {
                $result = DB::select('SHOW TABLES');
                foreach ($result as $table) {
                    foreach ($table as $key => $value) {
                        $tables[] = $value;
                    }
                }
            } elseif ($driver === 'pgsql') {
                $result = DB::select("
                    SELECT table_name 
                    FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    ORDER BY table_name
                ");
                foreach ($result as $table) {
                    $tables[] = $table->table_name;
                }
            } else {
                return [
                    'success' => false,
                    'error' => "Неподдерживаемая СУБД: {$driver}"
                ];
            }
            
            return [
                'success' => true,
                'driver' => $driver,
                'tables' => $tables,
                'count' => count($tables)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Ошибка при получении списка таблиц: " . $e->getMessage()
            ];
        }
    }
}