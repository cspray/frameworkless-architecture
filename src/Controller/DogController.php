<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller;

use Cspray\ArchDemo\Entity\Dog;
use Cspray\ArchDemo\HttpStatusCodes;
use Cspray\ArchDemo\Model\DogModel;
use Cspray\ArchDemo\Transformer\DogTransformer;
use League\Fractal;
use League\Fractal\TransformerAbstract as FractalTransformer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;

class DogController extends ApplicationController {

    private $dogModel;
    private $transformer;

    public function __construct(DogModel $dogModel, Fractal\Manager $fractal) {
        parent::__construct($fractal);
        $this->dogModel = $dogModel;
    }

    public function index() : ResponseInterface {
        $dogs = $this->dogModel->all();
        return new JsonResponse($this->serialize($dogs));
    }

    public function show(ServerRequestInterface $request) : ResponseInterface {
        $dog = $this->dogModel->find(Uuid::fromString($request->getAttribute('id')));
        return new JsonResponse($this->serialize($dog));
    }

    public function create(ServerRequestInterface $request) : ResponseInterface {
        $data = $request->getParsedBody()['dog'];
        $dog = (new Dog())->withName($data['name'])->withBreed($data['breed'])->withIncrementedAge($data['age']);
        $this->dogModel->save($dog);
        return new JsonResponse($this->serialize($dog), HttpStatusCodes::CREATED);
    }

    public function delete(ServerRequestInterface $request) : ResponseInterface {
        $this->dogModel->delete(Uuid::fromString($request->getAttribute('id')));
        return new EmptyResponse();
    }

    public function getTransformer(): FractalTransformer {
        if (!$this->transformer) {
            $this->transformer = new DogTransformer();
        }

        return $this->transformer;
    }

}