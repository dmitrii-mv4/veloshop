<?php

namespace App\Core\Enums;

/*
 * Перечисление пола.
 */

use App\Core\Enums\Traits\ToArrayTrait;
use App\Core\Enums\Traits\ValueByNameTrait;

enum Sex: int implements Lableable, Arrayable, ValueByNameInterface
{
    use ToArrayTrait,
        ValueByNameTrait;

    /*
     * Мужской.
     */
    case MALE = 0;

    /*
     * Женский.
     */
    case FEMALE = 1;

    /**
     * Возвращает лейбл.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Мужской',
            self::FEMALE => 'Женский',
        };
    }
}
