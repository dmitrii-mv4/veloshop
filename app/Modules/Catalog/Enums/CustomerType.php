<?php

namespace App\Modules\Catalog\Enums;

use App\Core\Enums\Arrayable;
use App\Core\Enums\Lableable;
use App\Core\Enums\ValueByNameInterface;
use App\Core\Enums\Traits\ToArrayTrait;
use App\Core\Enums\Traits\ValueByNameTrait;

/*
 * Перечисление типа покупателя.
 */
enum CustomerType: int implements Lableable, Arrayable, ValueByNameInterface
{
    use ToArrayTrait,
        ValueByNameTrait;

    /*
     * Физическое лицо.
     */
    case INDIVIDUAL = 0;

    /*
     * Юридическое лицо.
     */
    case LEGAL_ENTITY = 1;

    /**
     * Возвращает лейбл.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Физическое лицо',
            self::LEGAL_ENTITY => 'Юридическое лицо',
        };
    }
}
