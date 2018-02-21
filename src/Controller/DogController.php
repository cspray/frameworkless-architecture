<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller;

use Cspray\ArchDemo\Entity\Dog;
use Cspray\ArchDemo\HttpStatusCodes;
use Cspray\ArchDemo\Repository\DogRepository;
use Cspray\ArchDemo\Transformer\DogTransformer;
use League\Fractal;
use League\Fractal\TransformerAbstract as FractalTransformer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class DogController extends ApplicationController {

    private $dogRepository;
    private $transformer;

    public function __construct(DogRepository $dogRepository, Fractal\Manager $fractal) {
        parent::__construct($fractal);
        $this->dogRepository = $dogRepository;
    }

    public function index() : ResponseInterface {
        $dogs = $this->dogRepository->findAll();
        return new JsonResponse($this->serialize($dogs));
    }

    public function show(ServerRequestInterface $request) : ResponseInterface {
        $uuid = Uuid::fromString($request->getAttribute('id'));
        $dog = $this->dogRepository->find($uuid);
        return new JsonResponse($this->serialize($dog));
    }

    public function create(ServerRequestInterface $request) : ResponseInterface {
        $data = $request->getParsedBody()['dog'];
        $dog = (new Dog())->withName($data['name'])->withBreed($data['breed'])->withIncrementedAge($data['age']);
        $this->dogRepository->save($dog);
        return new JsonResponse($this->serialize($dog), HttpStatusCodes::CREATED);
    }

    public function delete(ServerRequestInterface $request) : ResponseInterface {
        $this->dogRepository->delete(Uuid::fromString($request->getAttribute('id')));
        return new EmptyResponse();
    }

    public function getTransformer(): FractalTransformer {
        if (!$this->transformer) {
            $this->transformer = new DogTransformer();
        }

        return $this->transformer;
    }

}