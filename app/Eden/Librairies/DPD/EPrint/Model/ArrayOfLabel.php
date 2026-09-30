<?php
declare (strict_types=1);

namespace App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Model;

use App\Eden\Librairies\Ekyna\Component\Dpd\OutputInterface;

/**
 * Class ArrayOfLabel
 * @package Ekyna\Component\Dpd\Response
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ArrayOfLabel extends \ArrayObject implements OutputInterface
{
    /**
     * @var Label|Label[]
     */
    private $Label;


    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        if ($this->Label) {
            $data = is_array($this->Label) ? array_values($this->Label) : [$this->Label];
            $this->__construct($data);
            unset($this->Label);
        }
    }
}
