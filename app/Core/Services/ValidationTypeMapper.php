<?php

namespace App\Core\Services;

/**
 * Сервис для преобразования типов данных в правила валидации Laravel
 */
class ValidationTypeMapper
{
    /**
     * Карта преобразования типов данных в правила валидации
     */
    private const TYPE_MAP = [
        // Строковые типы
        'string' => 'string',
        'text' => 'string',
        'char' => 'string',
        'varchar' => 'string',
        
        // Числовые типы
        'integer' => 'integer',
        'int' => 'integer',
        'bigint' => 'integer',
        'smallint' => 'integer',
        'tinyint' => 'integer',
        'float' => 'numeric',
        'double' => 'numeric',
        'decimal' => 'numeric',
        'numeric' => 'numeric',
        'real' => 'numeric',
        
        // Логические типы
        'boolean' => 'boolean',
        'bool' => 'boolean',
        
        // Дата и время
        'date' => 'date',
        'datetime' => 'date',
        'timestamp' => 'date',
        'time' => 'date_format:H:i:s',
        'year' => 'integer|min:1901|max:2155',
        
        // JSON и массивы
        'json' => 'array',
        'array' => 'array',
        
        // Файлы
        'file' => 'file',
        'image' => 'image',
        
        // Специальные типы
        'email' => 'email',
        'url' => 'url',
        'ip' => 'ip',
        'ipv4' => 'ipv4',
        'ipv6' => 'ipv6',
        'mac' => 'mac_address',
    ];
    
    /**
     * Карта типов для Eloquent casts
     */
    private const CAST_MAP = [
        'boolean' => 'bool',
        'bool' => 'bool',
        'integer' => 'int',
        'int' => 'int',
        'float' => 'float',
        'double' => 'float',
        'decimal' => 'float',
        'json' => 'array',
        'array' => 'array',
        'date' => 'date',
        'datetime' => 'datetime',
        'timestamp' => 'datetime',
    ];
    
    /**
     * Карта типов для SQL/миграций
     */
    private const SQL_TYPE_MAP = [
        'string' => 'string',
        'text' => 'text',
        'integer' => 'integer',
        'int' => 'integer',
        'bigint' => 'bigInteger',
        'float' => 'float',
        'double' => 'double',
        'decimal' => 'decimal',
        'boolean' => 'boolean',
        'bool' => 'boolean',
        'date' => 'date',
        'datetime' => 'dateTime',
        'timestamp' => 'timestamp',
        'json' => 'json',
    ];
    
    /**
     * Преобразует тип данных в правило валидации Laravel
     */
    public static function toValidationRule(string $type): string
    {
        $type = strtolower(trim($type));
        
        return self::TYPE_MAP[$type] ?? 'string';
    }
    
    /**
     * Преобразует тип данных в Eloquent cast
     */
    public static function toCastType(string $type): ?string
    {
        $type = strtolower(trim($type));
        
        return self::CAST_MAP[$type] ?? null;
    }
    
    /**
     * Преобразует тип данных в тип SQL для миграций
     */
    public static function toSqlType(string $type): string
    {
        $type = strtolower(trim($type));
        
        return self::SQL_TYPE_MAP[$type] ?? 'string';
    }
    
    /**
     * Получает максимальную длину для строкового типа
     */
    public static function getStringMaxLength(string $type): int
    {
        $type = strtolower(trim($type));
        
        return match($type) {
            'text' => 65535,
            'mediumtext' => 16777215,
            'longtext' => 4294967295,
            default => 255,
        };
    }
    
    /**
     * Проверяет, является ли тип строковым
     */
    public static function isStringType(string $type): bool
    {
        $type = strtolower(trim($type));
        
        $stringTypes = ['string', 'text', 'char', 'varchar', 'mediumtext', 'longtext'];
        
        return in_array($type, $stringTypes);
    }
    
    /**
     * Проверяет, является ли тип числовым
     */
    public static function isNumericType(string $type): bool
    {
        $type = strtolower(trim($type));
        
        $numericTypes = ['integer', 'int', 'bigint', 'smallint', 'tinyint', 'float', 'double', 'decimal', 'numeric', 'real'];
        
        return in_array($type, $numericTypes);
    }
    
    /**
     * Проверяет, является ли тип логическим
     */
    public static function isBooleanType(string $type): bool
    {
        $type = strtolower(trim($type));
        
        return in_array($type, ['boolean', 'bool']);
    }
    
    /**
     * Проверяет, является ли тип датой/временем
     */
    public static function isDateTimeType(string $type): bool
    {
        $type = strtolower(trim($type));
        
        $dateTypes = ['date', 'datetime', 'timestamp', 'time', 'year'];
        
        return in_array($type, $dateTypes);
    }
    
    /**
     * Получает все доступные типы
     */
    public static function getAvailableTypes(): array
    {
        return array_keys(self::TYPE_MAP);
    }
}