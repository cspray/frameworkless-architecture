<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller;

use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Entity\Exercise;
use Cspray\ArchDemo\Repository\ExerciseRepository;
use Cspray\ArchDemo\Repository\Repository;
use Cspray\ArchDemo\Transformer\ExerciseTransformer;
use League\Fractal;
use League\Fractal\TransformerAbstract as FractalTransformer;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stdlib\ResponseInterface;

class ExerciseController extends ApplicationController
{

    use Mixin\IndexAwareMixin;
    use Mixin\ShowAwareMixin;
    use Mixin\CreateAwareMixin;
    use Mixin\UpdateAwareMixin;
    use Mixin\DeleteAwareMixin;

    public function __construct(ExerciseRepository $repository, Fractal\Manager $fractal)
    {
        parent::__construct($repository, $fractal);
    }

    protected function createNewEntity(ServerRequestInterface $request): Entity
    {
        $data = $request->getParsedBody()['exercise'];
        return new Exercise($data['name'], $data['description']);
    }

    protected function updateExistingEntity(Exercise $entity, ServerRequestInterface $request): Entity
    {
        $data = $request->getParsedBody()['exercise'];
        if (isset($data['name'])) {
            $entity = $entity->withName($data['name']);
        }

        if (isset($data['description'])) {
            $entity = $entity->withDescription($data['description']);
        }

        return $entity;
    }

    protected function getTransformer(): FractalTransformer
    {
        return new ExerciseTransformer();
    }
}
