<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Response;

use App\Eden\Librairies\Ekyna\Component\Dpd\OutputInterface;
use App\Eden\Librairies\Ekyna\Component\Dpd\ResponseInterface;

/**
 * Class CreateShipmentResponse
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreateShipmentResponse implements ResponseInterface, OutputInterface
{
    /**
     * @var \Ekyna\Component\Dpd\EPrint\Model\ArrayOfShipment
     */
    public $CreateShipmentResult;


    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        if ($this->CreateShipmentResult) {
            $this->CreateShipmentResult->initialize();
        }
    }
}
