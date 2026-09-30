<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Request;

use App\Eden\Librairies\Ekyna\Component\Dpd\Definition;
use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model;

/**
 * Class MultiShipmentRequest
 * @package Ekyna\Component\Dpd\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property Model\MultiServices $services
 * @property Model\Contact       $contact
 */
class MultiShipmentRequest extends Model\ShipmentRequestBase
{
    /**
     * @var Model\SlaveRequest[]
     */
    public $slaves;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->slaves = [];
    }

    /**
     * Adds the slave shipment request.
     *
     * @param Model\SlaveRequest $slave
     */
    public function addSlave(Model\SlaveRequest $slave)
    {
        $this->slaves[] = $slave;
    }

    /**
     * @inheritdoc
     */
    protected function buildDefinition(Definition\Definition $definition): void
    {
        parent::buildDefinition($definition);

        $definition
            ->addField(new Definition\Model('services', false, Model\MultiServices::class))
            ->addField(new Definition\Model('contact', false, Model\Contact::class))
            ->addField(new Definition\ArrayOfModel('slaves', false, Model\SlaveRequest::class));
    }
}
