<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Stub;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\TextResponse;

class ControllerStub {

    private $request;

    public function action(ServerRequestInterface $request) : ResponseInterface {
        $this->request = $request;

        return new TextResponse('From ControllerStub');
    }

    public function getRequest() : ServerRequestInterface {
        return $this->request;
    }

}