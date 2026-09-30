<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model;

use App\Eden\Librairies\Ekyna\Component\Dpd\Definition;
use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Enum\ELabelType;
use App\Eden\Librairies\Ekyna\Component\Dpd\AbstractInput;

/**
 * Class LabelType
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LabelType extends AbstractInput
{
    /**
     * PNG / PDF / PDF_A6
     *
     * @var string
     *
     * @see ELabelType
     */
    public $type = ELabelType::PNG;


    /**
     * @inheritdoc
     */
    protected function buildDefinition(Definition\Definition $definition): void
    {
        $definition->addField(new Definition\Enum('type', true, ELabelType::class));
    }
}
