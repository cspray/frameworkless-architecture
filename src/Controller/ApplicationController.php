<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Controller;

use Cspray\ArchDemo\Exception\NotFoundException;
use Cspray\ArchDemo\HttpStatusCodes;
use Cspray\ArchDemo\Repository\Repository;
use Cspray\ArchDemo\Validation\Results as ValidationResults;
use League\Fractal;
use League\Fractal\TransformerAbstract as FractalTransformer;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;
use Throwable;

abstract class ApplicationController {

    private $repository;
    private $fractal;

    public function __construct(Repository $repository, Fractal\Manager $fractal) {
        $this->repository = $repository;
        $this->fractal = $fractal;
    }

    public function rescueFrom(Throwable $error) : ResponseInterface {
        if (!$error instanceof NotFoundException) {
            throw $error;
        }
        return new JsonResponse(['message' => 'Not Found'], HttpStatusCodes::NOT_FOUND);
    }

    protected function getRepository(): Repository {
        return $this->repository;
    }

    protected function responseForValidationError(ValidationResults $results) : ResponseInterface {
        return new JsonResponse(['data' => $results->getErrorMessages()], HttpStatusCodes::UNPROCESSABLE_ENTITY);
    }

    protected function serialize($entity) : array {
        if ($entity instanceof \Traversable) {
            $fractalResource = new Fractal\Resource\Collection($entity, $this->getTransformer());
        } else {
            $fractalResource = new Fractal\Resource\Item($entity, $this->getTransformer());
        }

        return $this->fractal->createData($fractalResource)->toArray();
    }

    abstract protected function getTransformer() : FractalTransformer;

}