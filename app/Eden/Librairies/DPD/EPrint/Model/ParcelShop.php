<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model;

use App\Eden\Librairies\Ekyna\Component\Dpd\AbstractInput;
use App\Eden\Librairies\Ekyna\Component\Dpd\Definition;

/**
 * Class ParcelShop
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property ShopAddress $shopaddress Adresse complète du Point DPD Relais
 */
class ParcelShop extends AbstractInput
{
    /**
     * @inheritdoc
     */
    protected function buildDefinition(Definition\Definition $definition): void
    {
        $definition->addField(new Definition\Model('shopaddress', false, ShopAddress::class));
    }
}
