<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Controller;

use Cspray\ArchDemo\HttpStatusCodes;
use League\Fractal;
use League\Fractal\TransformerAbstract as FractalTransformer;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Zend\Diactoros\Response\JsonResponse;

abstract class ApplicationController {

    private $fractal;

    public function __construct(Fractal\Manager $fractal) {
        $this->fractal = $fractal;
    }

    public function rescueFrom(Throwable $error) : ResponseInterface {
        return new JsonResponse(['message' => 'Not Found'], HttpStatusCodes::NOT_FOUND);
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