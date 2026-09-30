<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Method;

use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Request\ReverseShipmentLabelRequest;

/**
 * Class CreateReverseInverseShipmentWithLabels
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreateReverseInverseShipmentWithLabels extends AbstractMethod
{
    /**
     * @inheritdoc
     */
    protected function getMethodName(): string
    {
        return 'CreateReverseInverseShipmentWithLabels';
    }

    /**
     * @inheritdoc
     */
    protected function getRequestClass(): string
    {
        return ReverseShipmentLabelRequest::class;
    }
}
