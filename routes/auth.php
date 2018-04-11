<?php

    // include database and object files
    require_once __DIR__.'/../vendor/autoload.php';
    require_once __DIR__.'/../authManager.php';

    // Login request to get an auth-token
    $app->post('/auth/login', function($request, $response){
        $authHeader = $request->getHeader("Authorization");
        $basicAuth = $authHeader ? $authHeader[0] : null;
        $token = null;
        if($basicAuth){
            $basicAuth = str_replace("Basic","",$basicAuth);
            $basicAuth = trim($basicAuth);
            $credentials = explode(":",base64_decode($basicAuth));
            if($credentials && count($credentials) == 2){
                $token = AuthManager::login($credentials[0], $credentials[1]);
            }
        }else{
            $body = $request->getParsedBody();
            if(array_key_exists('name', $body) && array_key_exists('password', $body)){
                $token = AuthManager::login($body['name'], $body['password']);
            }
        }
        if($token){
            $response = $response
                ->withHeader("Content-type","application/json")
                ->withStatus(200)
                ->getBody()->write(json_encode(array("token" => $token)));
        }else{
            $response = $response
                ->withHeader("Content-type","application/json")
                ->withHeader("WWW-Authenticate","Basic")
                ->withStatus(401)
                ->getBody()->write(json_encode(array("error" => "INVALID_CREDENTIALS")));
        }
        return $response;
    });

    // Sign up request to create a new user
    $app->post('/auth/register', function($request, $response){
        $body = $request->getParsedBody();
        $name = $body->name;
        $password = $body->password;
        $err = AuthManager::register($name, $password);

        if($err){
            $response = $response
                ->withHeader("Content-type","application/json")
                ->withStatus(400)
                ->getBody()->write(json_encode(array("error" => $err)));
        }else{
            $response = $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(201);
        }
        return $response;
    });

    // Request to remove a user-account
    $app->post('/auth/unregister', function($request, $response){
        $body = $request->getParsedBody();
        $name = $body->name;
        $password = $body->password;

        $err = AuthManager::unregister($name, $password);

        if($err){
            $response = $response
                ->withHeader("Content-type","application/json")
                ->withStatus(400)
                ->getBody()->write(json_encode(array("error" => $err)));
        }else{
            $response = $response
                ->withHeader("Content-type", "application/json")
                ->withStatus(201);
        }
        return $response;
    });

