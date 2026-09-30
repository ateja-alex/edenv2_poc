<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model;

use App\Eden\Librairies\Ekyna\Component\Dpd\Definition;
use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Api;
use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Enum\EType;
use App\Eden\Librairies\Ekyna\Component\Dpd\Exception\RuntimeException;
use App\Eden\Librairies\Ekyna\Component\Dpd\AbstractInput;

/**
 * Class Shipment
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property int $countrycode  Code pays (250 = France)
 * @property int $centernumber Code agence
 * @property int $parcelnumber N° de colis
 * @property int $barcode      Contenu du code à barres DPD
 * @property int $type         Type d’expédition
 */
class Shipment extends AbstractInput
{
    /**
     * Returns the tracking url.
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function getTrackingUrl()
    {
        if (!isset($this->parcelnumber)) {
            throw new RuntimeException("Shipment has no parcel number.");
        }

        return sprintf(Api::TRACKING_URL, $this->parcelnumber);
    }

    /**
     * @inheritdoc
     */
    protected function buildDefinition(Definition\Definition $definition): void
    {
        $definition
            ->addField(new Definition\Numeric('countrycode', true, 3))
            ->addField(new Definition\Numeric('centernumber', true, 3))
            ->addField(new Definition\Numeric('parcelnumber', true, 9))
            ->addField(new Definition\Numeric('barcode', true, 255))// TODO limit ?
            ->addField(new Definition\Enum('type', false, EType::class));
    }
}
