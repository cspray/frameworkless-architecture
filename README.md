# PHP Frameworkless Architecture

[![Build Status](https://travis-ci.org/cspray/frameworkless-architecture.svg?branch=master)](https://travis-ci.org/cspray/frameworkless-architecture)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cspray/frameworkless-architecture/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cspray/frameworkless-architecture/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/cspray/frameworkless-architecture/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/cspray/frameworkless-architecture/?branch=master)
[![GitHub release](https://img.shields.io/github/release/frameworkless-architecture/core.svg?style=flat-square)](https://github.com/cspray/frameworkless-architecture/releases/latest)
[![Dependency Status](https://www.versioneye.com/user/projects/5a8b0a820fb24f0a2d3fa03e/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/5a8b0a820fb24f0a2d3fa03e)

A PHP 7.2+ application implementing a RESTful API for the management of a dog training business. It allows you to assign 
trainers you have on staff to perform various exercises with dogs your business is training. The actual functionality of 
the application is tertiary to the idea of presenting an application that is not tied to any specific framework _and_ 
also adheres to the following guidelines:

- Has a well-tested, cleanly-separated codebase that adheres to [S.O.L.I.D.][solid] principles.
- Utilizes PSR standards wherever applicable and practical.
- Present a more fully-featured "Hello World" app that involves database interactions and all CRUD operations on a database.
- Show solutions beyond mere code architecture to include a productive development environment.
- At times implement solutions exclusively meant to showcase a powerful PHP7+ feature.
 
## Installation

The only absolute requirement that you must have installed is git, Docker, and docker-compose.

We recommend that you clone this repository using git

```
git clone git@github.com:cspray/frameworkless-architecture.git
```

Once you have the code installed on your machine go through the initial setup 
process:

1. Run `docker-compose build` to build your containers.
2. Get the app running with the PHP development server `docker-compose up`
3. Create the initial PostgreSQL databases for both development and test environments
    
    ```
    docker-compose run postgres psql -h postgres -U postgres
    > CREATE DATABASE archdemo;
    > CREATE DATABASE archdemo_test;
    > \q
    ```
    
4. Ensure the appropriate tables are created in both environments

    ```
    docker-compose run app vendor/bin/doctrine orm:schema-tool:create
    docker-compose -e APP_ENV=test run app vendor/bin/doctrine orm:schema-tool:create
    ```

5. Ensure tests runs and you can connect to interactive shell.

    ```$xslt
    docker-compose run app vendor/bin/phpunit
    docker-compose run app bin/arch-demo
    ```
## Documentation

The application is divided into several namespaces, or modules, that perform discrete functions when tied together becomes 
a cohesive application capable of responding to RESTful HTTP requests with a persistent data store backing those 
operations. Below are a list of those modules and a link to more detailed documentation for each; alternatively you can 
view each module's documentation in the README file located under its root directory. The rest of this section deals with 
the code that ties the different modules together.

- [Config][config-readme]
- [Controller][controller-readme]
- [DoctrineAdapter][doctrine-adapter-readme]
- [Entity][entity-readme]
- [Exception][exception-readme]
- [Middleware][middleware-readme]
- [Repository][repository-readme]
- [Router][router-readme]
- [Transformer][transformer-readme]
- [Validation][validation-readme]

### `main.php`

The primary entry point for all requests is the `main.php` file that lives in the root of the application. You may think 
of this as the ["front controller"][wiki-front-controller] for the application and _all_ HTTP
requests are intended to be routed through this entrypoint. Primarily you should not need to interact or change this 
code directly, you will utilize the files in the config directory to customize the application's behavior.

### `config/environment.php`

The `environment.php` file defines the different configuratiion values for a given environment. Currently the environments 
supported include:

- development
- test
- production

This file is meant to return a callable that accepts 1 parameter, the string representing the environment, and returns 
an array of configuration values for that environment. Currently the supported configuration options appear below:

```php
[
    'db' => [
        'host' => 'XXX',
        'name' => 'XXX',
        'user' => 'XXX',
        'pass' => 'XXX',
        'driver' => 'XXX',
    ],
    'cors' => [
        'serverOrigin' => 'https://localhost:4300',
        'alloweOrigins' => [
            '*'
        ],
        'allowedMethods' => [
            'GET' => true,
            'POST' => true,
            'PUT' => null,
            'DELETE' => null
        ],
        'preflightCacheMaxAge' => 0,
        'forceHeadersPreflight' => false,
        'forceMethodsPreflight' => false,
        'forceCheckHost' => false,
        'requestCredentialsSupported' => false,
        'allowedHeaders' => [],
        'responseExposedHeaders' => []
    ]
]
```

### `config/middlewares.php`

Part of our guiding principles includes adhering to PSR where appropriate and we do so wiith our HTTP implementation by 
following both [PSR-7][psr7] and [PSR-15][psr15]. This file should return a callable that follows the below method
signature:

```php
<?php

use Equip\Dispatch\MiddlewareCollection;
use Auryn\Injector;

return function(MiddlewareCollection $middlewares, Injector $injector) : void {
    // attach your PSR-15 compliant Middleware to $middlewares
    $middlewares->append($injector->make('MyMiddlewareClass'));
};
```

And that's all there is to it! To truly understand the power of middleware and all the awesome things you can do with it 
make sure you read up on the PSR-15 documentation.

> It is highly recommended that you DO NOT remove the Middleware that are already configured in this file. Please add to 
> the list AFTER the last default Middleware has been added.

### `config/routes.php`

Likely of more immediate importance when just starting out is how you setup your routes to be something that can be 
turned into a Response. This file should return a callable that accepts 1 parameter which should be a Router instance.

> We're going to show some examples on how to set some routes but to get a full understanding ensure that you read the 
> documentation in the Router module.

The Router interface is very low level and isn't recommended for route configuration. Instead we suggest that you 
utilize the FriendlyRouter decorator object to use a, ahem, friendly API when configuring your routes.

```php
<?php

use Cspray\ArchDemo\Router\Router;
use Cspray\ArchDemo\Router\FriendlyRouter;

return function(Router $router) {
      $router = new FriendlyRouter($router);
      $router->get('/route/path', 'ControllerClassName#methodName');
      // Creates the following routes:
      // GET /entity EntityClass#index
      // GET /entity/{id} EntityClass#show
      // POST /entity EntityClass#create
      // PUT /entity/{id} EntityClass#update
      // DELETE /entity/{id} EntityClass#delete
      $router->resource('entity', 'EntityClass');
};

?>
```

This is just a brief glimpse at the routing functionality available to you. It is _highly_ recommended that you review the 
Routing documentation for more information.

## Development Tools



## Dependencies

Because you aren't relying on a framework to provide the majority of your dependencies for handling basic application 
functionality it is important that you know what your dependencies are and what purpose they provide. Below is a list 
of every dependency this application uses, both in production and development environments, as well as an explanation 
of what purpose they serve.

### [doctrine/orm][doctrine-orm]

A highly mature, robust database abstraction layer and ORM. It has a large community and many available open source packages 
providing reusable behavior; stuff like pagination, sorting, timestamping, etc. Please note that the Repository as 
Doctrine defines it is **NOT** the same as our implementation of the design pattern. Our persistence layer is completely 
abstracted from Doctrine and you are not tied into using this particular ORM or persistence implementation.

### [equip/dispatch][equip-dispatch]

A component of a larger framework that simply handles dispatching PSR-15 Middleware. Ultimately there were many possible 
dispatchers to choose from and this was chose for its simplicity and ease of use. Typically in the normal course of writing 
code for this application you will not need to make heavy use of this library.

### [filp/whoops][filp-whoops]

A powerful Exception and Error handling library that is used to ensure we can have useful error information in our Requests 
when an exceptional situation or error has occurred within our app.

### [league/fractal][league-fractal]

A simple, but powerful, JSON serialization library that turns our domain objects into responses suitable for HTTP responses.
The use of this library maps directly to the  [Transformer][transformer-readme] module and you should read up on that 
documentation for more details. 

### [middlewares/access-log][middlewares-access-log]

A PSR-15 Middleware that adds Apache Server Specification access logs to a PSR-3 compliant logger of your choice.

### [middlewares/cors][middlewares-cors]

A PSR-15 Middleware that handles all the complexities of managing cross origin requests. The use of this middleware 
is directly influenced by the ` config/environment.php` file.

### [middlewares/whoops][middlewares-whoops]

A PSR-15 Middleware that utilizes filp/whoops to ensure that uncaught exceptions or errors in Middleware still result 
in some response being sent to the user.

### [monolog/monolog][monolog-monolog]

An incredibly powerful, PSR-3 compliant logging solution primarily used by the access log middleware but can also be used 
to send logs to various handlers including files, databases, external services, and much more.

### [nikic/fast-route][nikic-fast-route]

A performance-oriented routing solution that provides a simple API for routing HTTP requests to arbitrary pieces of data. 
is the primary implementation for our Router module.

### [psr/http-message][psr-http-message]

An interface-only library that defines the API specification for PSR-7 defining how HTTP Requests and Responses are handled.
By adhering to these interfaces we open ourselves to a wealth of libraries and interoperabiity efforts leading to potentially 
high levels of code reuse.

### [psr/http-server-handler][psr-http-server-handler]



### [psr/http-server-middleware][psr-http-server-middleware]

### [ramsey/uuid][ramsey-uuid]

### [ramsey/uuid-doctrine][ramsey-uuid-doctrine]

### [rdlowrey/auryn][rdlowrey-auryn]

### [zendframework/zend-diactoros][zendframework-zend-diactoros]

### [zendframework/zend-validator][zendframework-zend-validator]

### [phpmetrics/phpmetrics][phpmetrics-phpmetrics]*

### [phpunit/dbunit][phpunit-dbunit]*

### [phpunit/phpunit][phpunit-phpunit]*

### [psy/psysh][psy-psysh]*

* Development only

[config-readme]: src/Config/README.md
[controller-readme]: src/Controller/README.md
[doctrine-adapter-readme]: src/DoctrineAdapter/README.md
[doctrine-orm]: https://github.com/doctrine/doctrine2
[entity-readme]: src/Entity/README.md
[equip-dispatch]: https://github.com/equip/dispatch
[exception-readme]: src/Exception/README.md
[filp-whoops]: https://github.com/filp/whoops
[league-fractal]: https://github.com/thephpleague/fractal
[middleware-readme]: src/Middleware/README.md
[middlewares-access-log]: https://github.com/middlewares/access-log
[middlewares-cors]: https://github.com/middlewares/cors
[middlewares-whoops]: https://github.com/middlewares/whoops
[monolog-monolog]: https://github.com/Seldaek/monolog
[nikic-fast-route]: https://github.com/nikic/FastRoute
[phpmetrics-phpmetrics]: https://github.com/phpmetrics/PhpMetrics
[phpunit-dbunit]: https://github.com/sebastianbergmann/dbunit
[phpunit-phpunit]: https://github.com/sebastianbergmann/phpunit
[psr-http-message]: https://github.com/php-fig/http-message
[psr-http-server-handler]: https://github.com/php-fig/http-server-handler
[psr-http-server-middleware]: https://github.com/php-fig/http-server-middleware
[psr7]: https://www.php-fig.org/psr/psr-7/
[psr15]: https://www.php-fig.org/psr/psr-15/
[ramsey-uuid]: https://github.com/ramsey/uuid
[ramsey-uuid-doctrine]: https://github.com/ramsey/uuid-doctrine
[rdlowrey-auryn]: https://github.com/rdlowrey/auryn
[repository-readme]: src/Repository/README.md
[router-readme]: src/Router/README.md
[solid]: https://en.wikipedia.org/wiki/SOLID_(object-oriented_design)
[transformer-readme]: src/Transformer/README.md
[validation-readme]: src/Validation/README.md
[wiki-front-controller]: https://en.wikipedia.org/wiki/Front_controller
[zendframework-zend-diactoros]: https://github.com/zendframework/zend-diactoros
[zendframework-zend-validator]: https://github.com/zendframework/zend-validator