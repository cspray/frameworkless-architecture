<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Transformer;

use Cspray\ArchDemo\Entity\Exercise;
use League\Fractal\TransformerAbstract;

class ExerciseTransformer extends TransformerAbstract {

    public function transform(Exercise $exercise) {
        return [
            'id' => (string) $exercise->getId(),
            'name' => $exercise->getName(),
            'description' => $exercise->getDescription()
        ];
    }

}