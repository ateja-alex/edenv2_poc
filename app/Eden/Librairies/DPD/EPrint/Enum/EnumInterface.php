<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Enum;

/**
 * Interface EnumInterface
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface EnumInterface
{
    /**
     * Returns the values.
     *
     * @return array
     */
    public static function getValues(): array;
}