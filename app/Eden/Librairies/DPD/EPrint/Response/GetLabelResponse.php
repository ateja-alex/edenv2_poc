<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Response;

use App\Eden\Librairies\Ekyna\Component\Dpd\OutputInterface;
use App\Eden\Librairies\Ekyna\Component\Dpd\ResponseInterface;

/**
 * Class GetLabelResponse
 * @package Ekyna\Component\Dpd
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GetLabelResponse implements ResponseInterface, OutputInterface
{
    /**
     * @var \Ekyna\Component\Dpd\EPrint\Model\LabelResponse
     */
    public $GetLabelResult;


    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        if ($this->GetLabelResult) {
            $this->GetLabelResult->initialize();
        }
    }
}
