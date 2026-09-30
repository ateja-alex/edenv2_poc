<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model;

use App\Eden\Librairies\Ekyna\Component\Dpd\OutputInterface;

/**
 * Class MultiShipment
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MultiShipment implements OutputInterface
{
    /**
     * @var Shipment
     */
    public $mastershipment;

    /**
     * @var ArrayOfShipment
     */
    public $shipments;


    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        if ($this->shipments) {
            $this->shipments->initialize();
        }
    }
}
