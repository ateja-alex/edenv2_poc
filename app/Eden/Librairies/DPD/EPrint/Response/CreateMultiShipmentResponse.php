<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Response;

use App\Eden\Librairies\Ekyna\Component\Dpd\OutputInterface;
use App\Eden\Librairies\Ekyna\Component\Dpd\ResponseInterface;

/**
 * Class CreateMultiShipmentResponse
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreateMultiShipmentResponse implements ResponseInterface, OutputInterface
{
    /**
     * @var \Ekyna\Component\Dpd\EPrint\Model\MultiShipment
     */
    public $CreateMultiShipmentResult;


    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        if ($this->CreateMultiShipmentResult) {
            $this->CreateMultiShipmentResult->initialize();
        }
    }
}
