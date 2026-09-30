<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Request;

use App\Eden\Librairies\Ekyna\Component\Dpd\Definition;
use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model\Bic3LabelData;
use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model\LabelType;

/**
 * Class StdShipmentLabelRequest
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property string        $customLabelText Commentaire de livraison
 * @property LabelType     $labelType
 * @property Bic3LabelData $bic3data
 * @property bool          $refnrasbarcode
 */
class StdShipmentLabelRequest extends StdShipmentRequest
{
    /**
     * @inheritdoc
     */
    protected function buildDefinition(Definition\Definition $definition): void
    {
        parent::buildDefinition($definition);

        $definition
            ->addField(new Definition\AlphaNumeric('customLabelText', false, 400))
            ->addField(new Definition\Model('labelType', false, LabelType::class))
            ->addField(new Definition\Model('bic3data', false, Bic3LabelData::class))
            ->addField(new Definition\Boolean('refnrasbarcode', false));
    }
}
