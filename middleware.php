<?php

    $auth = new \Tuupola\Middleware\JwtAuthentication([
        "path" => "/resource/",
        "secure" => false, // TODO: set up https, for now, allow insecure
        "secret" => base64_decode($config['rest']['secret']),   // the secret is base64 encoded (shouldn't be a human readable string, but if it is and someone looks over my shoulder...)
        "error" => function ($response, $arguments) {
            $data["error"] = strtoupper(str_replace(" ", "_", $arguments["message"]));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(401)
                ->getBody()->write(json_encode($data));
        }
    ]);

    $auth = $auth->withRules([
        new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
            "path" => "/resource/{id}",
            "ignore" => ["GET"]
        ]),
        new Tuupola\Middleware\JwtAuthentication\RequestPathRule([
            "path" => "/auth/",
            "ignore" => ["/auth/login"]
        ])
    ]);

    $app->add($auth);

    $app->add(function($request, $response, $next){
        $config = \Zend\Config\Factory::fromFile(__DIR__ . DIRECTORY_SEPARATOR . 'config.json');
        $response = $next($request, $response);
        return $response
            ->withHeader("X-Hello-There","General Kenobi!")
            ->withHeader("Access-Control-Allow-Origin", $config['mode'] === 'dev' ? '*' : $config['webhost']);
    });