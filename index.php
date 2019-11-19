<?php
/*

 About:
 Author: Raul Perusquia <raul@inikoo.com>
 Created: 2019-11-19T16:40:46+01:00, Malaga Spain

 Copyright (c) 2019, Inikoo

 Version 1.0
*/

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->run();
