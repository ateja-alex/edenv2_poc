<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model;

use App\Eden\Librairies\Ekyna\Component\Dpd\Definition;

/**
 * Class ShipmentRequestDefaultData
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property StdServices $services
 */
class ShipmentRequestDefaultData extends ShipmentRequestBase
{
    /**
     * @inheritdoc
     */
    protected function buildDefinition(Definition\Definition $definition): void
    {
        parent::buildDefinition($definition);

        $definition->addField(new Definition\Model('services', false, StdServices::class));
    }
}
