<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Validation;

interface Validatable {

    public function validate() : Results;

}