<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Method;

use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Request\MultiShipmentRequest;

/**
 * Class CreateMultiShipment
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreateMultiShipment extends AbstractMethod
{
    /**
     * @inheritdoc
     */
    protected function getMethodName(): string
    {
        return 'CreateMultiShipment';
    }

    /**
     * @inheritdoc
     */
    protected function getRequestClass(): string
    {
        return MultiShipmentRequest::class;
    }
}
