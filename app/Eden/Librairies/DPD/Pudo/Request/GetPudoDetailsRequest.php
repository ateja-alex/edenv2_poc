<?php

namespace App\Eden\Librairies\Ekyna\Component\Dpd\Pudo\Request;

use App\Eden\Librairies\Ekyna\Component\Dpd\AbstractInput;
use App\Eden\Librairies\Ekyna\Component\Dpd\Definition;
use App\Eden\Librairies\Ekyna\Component\Dpd\RequestInterface;

/**
 * Class GetPudoDetailsRequest
 * @package Ekyna\Component\Dpd\Pudo
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property string $pudo_id
 */
class GetPudoDetailsRequest extends AbstractInput implements RequestInterface
{
    protected function buildDefinition(Definition\Definition $definition): void
    {
        $definition->addField(new Definition\AlphaNumeric('pudo_id', true, 10));
    }
}
