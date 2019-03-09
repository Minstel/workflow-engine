<?php declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Jasny\HttpMessage\ServerRequest;
use Jasny\HttpMessage\Response;

return [
    ServerRequestInterface::class => static function() {
        return (new ServerRequest())->withGlobalEnvironment();
    },
    ResponseInterface::class => static function() {
        return new Response();
    }
];
