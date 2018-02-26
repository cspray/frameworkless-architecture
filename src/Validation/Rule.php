<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Validation;

interface Rule
{

    public function passesCheck($value, string $attributeName = null) : Results;
}
