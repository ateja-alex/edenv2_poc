<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Request;

use App\Eden\Librairies\Ekyna\Component\Dpd\AbstractInput;
use App\Eden\Librairies\Ekyna\Component\Dpd\Definition;
use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model;
use App\Eden\Librairies\Ekyna\Component\Dpd\RequestInterface;

/**
 * Class TerminateCollectionRequestRequest
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property Model\Parcel   $parcel
 * @property Model\Customer $customer
 */
class TerminateCollectionRequestRequest extends AbstractInput implements RequestInterface
{
    /**
     * @inheritdoc
     */
    protected function buildDefinition(Definition\Definition $definition): void
    {
        $definition
            ->addField(new Definition\Model('parcel', false, Model\Parcel::class))
            ->addField(new Definition\Model('customer', true, Model\Customer::class));
    }
}
