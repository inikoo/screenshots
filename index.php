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
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;

require 'keyring/screenshots.dns.php';
require 'vendor/autoload.php';
require 'screenshot_functions.php';


$app = AppFactory::create();

$app->addRoutingMiddleware();

$customErrorHandler = function (Request $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) use ($app) {
    $payload = [
        'status'  => 'error',
        'message' => $exception->getMessage()
    ];

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(
        json_encode($payload, JSON_UNESCAPED_UNICODE)
    );

    return $response;
};

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

$validate_url = function (Request $request, RequestHandler $handler) {


    $params   = $request->getQueryParams();
    $response = $handler->handle($request);

    if (empty($params['url'])) {
        throw new RuntimeException('GET URL Parameter url needed. e.g. ?url=https://www.example.com');
    }

    if (filter_var($params['url'], FILTER_VALIDATE_URL) == false) {
        throw new RuntimeException('Invalid url, provide a valid RFC 2396 URL. e.g. ?url=https://www.example.com');
    }

    return $response;


};

$app->add(
    new Tuupola\Middleware\JwtAuthentication(
        [
            "secret" => SCREENSHOTS_KEY,
            "secure" => false,
            "error"  => function ($response, $arguments) {
                $data["status"]  = "error";
                $data["message"] = $arguments["message"];

                return $response->withHeader("Content-Type", "application/json")->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            }
        ]
    )
);

$app->any(
    '/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Welcome to web screenshots");

    return $response;
}
);

$app->get(
    '/take[/{device}[/{type}]]', function (Request $request, Response $response, $args) {
    $params = $request->getQueryParams();


    if (!empty($args['device'])) {

        if (!preg_match('/^(desktop|tablet|mobile)$/i', $args['device'])) {
            throw new RuntimeException('desktop|tablet|mobile are the valid values for device. e.g. take/desktop ');

        }
        $device = ucfirst($args['device']);

    } else {
        $device = 'Desktop';
    }

    if (!empty($args['type'])) {
        if (!preg_match('/^(Full|Cropped)$/i', $args['type'])) {
            throw new RuntimeException('full|view are the valid values for the screenshot type. e.g. take/desktop/full ');

        }
        $type = ucfirst($args['type']);
    } else {
        $type = 'Cropped';
    }


    // list($width,$height)=get_size($device,$type);

    $resized_image_filename = take_screenshots($params['url'], $device, $type);


    $image = @file_get_contents($resized_image_filename);
    if ($image === false) {
        $handler = $this->notFoundHandler;

        return $handler($request, $response);
    }


    $response->getBody()->write($image);
    unlink($resized_image_filename);

    return $response->withHeader('Content-Type', 'image/jpeg');

}
)->add($validate_url);

$app->run();
