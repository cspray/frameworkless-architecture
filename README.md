# PHP Frameworkless Architecture

[![Build Status](https://travis-ci.org/cspray/frameworkless-architecture.svg?branch=master)](https://travis-ci.org/cspray/frameworkless-architecture)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cspray/frameworkless-architecture/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cspray/frameworkless-architecture/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/cspray/frameworkless-architecture/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/cspray/frameworkless-architecture/?branch=master)
[![GitHub release](https://img.shields.io/github/release/frameworkless-architecture/core.svg?style=flat-square)](https://github.com/cspray/frameworkless-architecture/releases/latest)
[![Dependency Status](https://www.versioneye.com/user/projects/5a8b0a820fb24f0a2d3fa03e/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/5a8b0a820fb24f0a2d3fa03e)

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

More docs to come!