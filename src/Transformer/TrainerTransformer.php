<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Transformer;

use Cspray\ArchDemo\Entity\Trainer;
use League\Fractal\TransformerAbstract;

class TrainerTransformer extends TransformerAbstract
{

    public function transform(Trainer $trainer)
    {
        return [
            'id' => $trainer->getId(),
            'name' => $trainer->getName(),
            'specialty' => $trainer->getSpecialty()
        ];
    }
}
