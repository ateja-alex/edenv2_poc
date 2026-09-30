<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model;

use App\Eden\Librairies\Ekyna\Component\Dpd\OutputInterface;

/**
 * Class ShipmentWithLabels
 * @package Ekyna\Component\Dpd\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentWithLabels implements OutputInterface
{
    /**
     * Le n° de colis DPD
     *
     * @var Shipment
     */
    public $shipment;

    /**
     * Le/les étiquette(s) DPD
     *
     * @var ArrayOfLabel
     */
    public $labels;


    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        if ($this->labels) {
            $this->labels->initialize();
        }
    }
}
