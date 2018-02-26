<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Entity;

use Cspray\ArchDemo\Validation\Validatable;

interface Entity extends Validatable
{

    public function getId();
}
