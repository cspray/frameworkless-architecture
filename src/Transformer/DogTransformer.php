<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Transformer;

use Cspray\ArchDemo\Entity\Dog;
use League\Fractal\TransformerAbstract;

class DogTransformer extends TransformerAbstract
{

    public function transform(Dog $dog)
    {
        return [
            'id' => $dog->getId(),
            'name' => $dog->getName(),
            'breed' => $dog->getBreed(),
            'age' => $dog->getAge()
        ];
    }
}
