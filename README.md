# PHP Frameworkless Architecture

While large frameworks have a variety of benefits, especially in the beginning 
life of an application, they come with a cost and overhead. This repo is
meant to show that you can design and create a cleanly separated, well-tested 
application without incurring the cost of the framework or locking yourself into 
any one framework's idea of how things should be done.

## Installation

The only absolute requirement that you must have installed is Docker and 
docker-compose. If you wish to install the demo app from Composer you will 
also need to have PHP 7.2 and, obviously, Composer installed.

We suggest that you use Composer to install this demo application.

```
composer require cspray/frameworkless-architecture@~0.1
```

Alternatively you can simply clone this repo.

```
git clone git@github.com:cspray/frameworkless-architecture.git
```

Once you have the code installed on your machine go through the initial setup 
process:

1. Run `docker-compose build` to build your containers.
1. Get the app running with the PHP development server `docker-compose up`
3. Create the initial PostgreSQL databases for both development and test environments
    
    ```$xslt
    docker-compose run postgres psql -h postgres -U postgres
    > CREATE DATABASE archdemo;
    > \q
    ```
    
    ```$xslt
    docker-compose -e APP_ENV=test run postgres psql -h postgres -U postgres
    > CREATE DATABASE archdemo_test;
    > \q
    ```
    
 4. Ensure tests runs and you can connect to interactive shell.
 
    ```$xslt
    docker-compose run app vendor/bin/phpunit
    docker-compose run app bin/arch-demo
    ```
## Documentation

More docs to come!