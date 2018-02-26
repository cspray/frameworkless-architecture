<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller;

use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Entity\Trainer;
use Cspray\ArchDemo\Repository\TrainerRepository;
use Cspray\ArchDemo\Transformer\TrainerTransformer;
use League\Fractal;
use League\Fractal\TransformerAbstract as FractalTransformer;
use Psr\Http\Message\ServerRequestInterface;

class TrainerController extends ApplicationController
{

    use Mixin\IndexAwareMixin;
    use Mixin\ShowAwareMixin;
    use Mixin\CreateAwareMixin;
    use Mixin\UpdateAwareMixin;
    use Mixin\DeleteAwareMixin;

    public function __construct(TrainerRepository $repository, Fractal\Manager $fractal)
    {
        parent::__construct($repository, $fractal);
    }

    protected function createNewEntity(ServerRequestInterface $request): Entity
    {
        $data = $request->getParsedBody()['trainer'];
        return new Trainer($data['name'], $data['specialty']);
    }

    protected function updateExistingEntity(Trainer $entity, ServerRequestInterface $request): Entity
    {
        $data = $request->getParsedBody()['trainer'];
        if (isset($data['name'])) {
            $entity = $entity->withName($data['name']);
        }

        if (isset($data['specialty'])) {
            $entity = $entity->withSpecialty($data['specialty']);
        }

        return $entity;
    }

    protected function getTransformer(): FractalTransformer
    {
        return new TrainerTransformer();
    }
}
