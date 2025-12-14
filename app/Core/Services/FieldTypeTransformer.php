<?php

namespace App\Core\Services;

/**
 * Сервис для обработки свойств в sql типы
 */

class FieldTypeTransformer
{
    /**
     * Преобразует один тип UI в SQL-тип
     */
    public function transformForDatabase(string $uiType): string
    {
        return $this->getTypeMapping($uiType);
    }

    /**
     * Преобразует массив UI-типов в массив SQL-типов
     */
    public function transformForDatabaseArray(array $uiTypes): array
    {
        $result = [];
        foreach ($uiTypes as $uiType) {
            $result[] = $this->getTypeMapping($uiType);
        }
        return $result;
    }

    /**
     * Получаем SQL-тип по UI-типу
     */
    private function getTypeMapping(string $uiType): string
    {
        $map = [
            'file' => 'string',
            'string' => 'string',
            'text' => 'text',
            'decimal' => 'decimal',
            'integer' => 'integer',
        ];
        
        return $map[$uiType] ?? 'string';
    }
}