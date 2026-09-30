<?php

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Response;

use App\Eden\Librairies\Ekyna\Component\Dpd\OutputInterface;
use App\Eden\Librairies\Ekyna\Component\Dpd\ResponseInterface;

/**
 * Class CreateCollectionRequestResponse
 * @package Ekyna\Component\Dpd\EPrint\Response
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreateCollectionRequestResponse implements ResponseInterface, OutputInterface
{
    /**
     * @var \Ekyna\Component\Dpd\EPrint\Model\ArrayOfShipment
     */
    public $CreateCollectionRequestResult;


    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        if ($this->CreateCollectionRequestResult) {
            $this->CreateCollectionRequestResult->initialize();
        }
    }
}
