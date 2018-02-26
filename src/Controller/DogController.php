<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller;

use Cspray\ArchDemo\Entity\Dog;
use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Repository\DogRepository;
use Cspray\ArchDemo\Repository\Repository;
use Cspray\ArchDemo\Transformer\DogTransformer;
use League\Fractal;
use League\Fractal\TransformerAbstract as FractalTransformer;
use Psr\Http\Message\ServerRequestInterface;

class DogController extends ApplicationController
{

    use Mixin\IndexAwareMixin;
    use Mixin\ShowAwareMixin;
    use Mixin\CreateAwareMixin;
    use Mixin\UpdateAwareMixin;
    use Mixin\DeleteAwareMixin;

    public function __construct(DogRepository $repository, Fractal\Manager $fractal)
    {
        parent::__construct($repository, $fractal);
    }

    protected function createNewEntity(ServerRequestInterface $request) : Entity
    {
        $data = $request->getParsedBody()['dog'];
        return new Dog($data['name'], $data['breed'], $data['age']);
    }

    protected function updateExistingEntity(Dog $entity, ServerRequestInterface $request): Entity
    {
        $data = $request->getParsedBody()['dog'];

        if (isset($data['name'])) {
            $entity = $entity->withName($data['name']);
        }

        if (isset($data['aged']) && $data['aged']) {
            $entity = $entity->withAgedOneYear();
        }

        return $entity;
    }



    public function getTransformer(): FractalTransformer
    {
        return new DogTransformer();
    }
}
