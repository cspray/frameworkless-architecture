<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Validation;

interface Results
{

    public function isValid() : bool;

    public function getErrorMessages() : array;
}
