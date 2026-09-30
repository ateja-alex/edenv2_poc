<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model;

use App\Eden\Librairies\Ekyna\Component\Dpd\AbstractInput;
use App\Eden\Librairies\Ekyna\Component\Dpd\Definition;
use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Enum\EType;

/**
 * Class Label
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property string $type  Type d'étiquette
 * @property string $label Etiquette sous forme de Bytearrays
 *
 * @see \Ekyna\Component\Dpd\EPrint\Enum\EType
 */
class Label extends AbstractInput
{
    /**
     * @inheritdoc
     */
    protected function buildDefinition(Definition\Definition $definition): void
    {
        $definition
            ->addField(new Definition\Enum('type', true, EType::class))
            ->addField(new Definition\AlphaNumeric('label', false, 3));
    }
}
