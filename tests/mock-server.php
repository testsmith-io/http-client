<?php

include_once __DIR__. '/../vendor/autoload.php';

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$response = new \Symfony\Component\HttpFoundation\JsonResponse([
    'method' => $request->getMethod(),
    'uri' => $request->getUri()
]);
$response->send();