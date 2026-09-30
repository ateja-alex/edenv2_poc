<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Request;

use App\Eden\Librairies\Ekyna\Component\Dpd\AbstractInput;
use App\Eden\Librairies\Ekyna\Component\Dpd\Definition;
use App\Eden\Librairies\Ekyna\Component\Dpd\RequestInterface;

/**
 * Class RetourShipmentRequest
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property int $countrycode           Code pays (250 = France)
 * @property int $centernumber          Code agence
 * @property int $original_parcelnumber N° de colis originel
 */
class RetourShipmentRequest extends AbstractInput implements RequestInterface
{
    /**
     * @inheritdoc
     */
    protected function buildDefinition(Definition\Definition $definition): void
    {
        $definition
            ->addField(new Definition\Numeric('countrycode', true, 3))
            ->addField(new Definition\Numeric('centernumber', true, 3))
            ->addField(new Definition\Numeric('original_parcelnumber', true, 9));
    }
}
