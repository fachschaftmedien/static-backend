<?php

    require_once __DIR__.'/../vendor/autoload.php';
    require_once __DIR__.'/../folder.php';

    $app->get('/resources', function($request, $response){
        $result = null;
        $error = null;
        if($request->getQueryParam("name", null) !== null){
            $match = $request->getQueryParam("name");
            $limit = $request->getQueryParam("limit", null);
            $offset = $request->getQueryParam("offset", 0);
            $strict = $request->getQueryParam("strict",true);
            $result = Folder::search($match, $strict);
            $result = $limit === null ? array_slice($result, $offset) : array_slice($result, $offset, $limit);
        }else{
            $level = $request->getQueryParam("level", -1);
            $result = Folder::structure($level);
        }

        if(!$error){
            $response = $response
                ->withStatus(count($result) ? 200 : 404)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode($result));
        }else{
            $response = $response
                ->withStatus(400)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("error" => $error)));
        }
        return $response;
    });

    $app->get('/resource/{id}', function ($request, $response){
        $id = $request->getAttribute('route')->getArgument('id');
        $found = Folder::get($id);
        if($found){
            $response = $response
                ->withStatus(200)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode($found));
        }else{
            $response = $response
                ->withStatus(404)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("error" => "NOT_FOUND")));
        }
        return $response;
    });

    $app->post('/resource/', function ($request, $response) {
        $config = \Zend\Config\Factory::fromFile(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.json');
        $body = $request->getParsedBody();
        $error = "";
        $creation = "";
        if (!array_key_exists("path", $body)) {
            $error = "NO_PATH";
        } else if (!array_key_exists("upload", $body)) {
            $error = "NO_UPLOAD_ID";
        } else if (strpos($body["path"], "..")) {
            $error = "INVALID_PATH_CONTROLS";
        } else if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $config["rest"]["uploadFolder"] . DIRECTORY_SEPARATOR . $body["upload"])) {
            $error = "NO_SUCH_UPLOAD";
        } else if(in_array(pathinfo($body["path"], PATHINFO_EXTENSION),array(".php",".php3",".php4",".php5",".phtml",".htm",".html",".xhtml",".exe",".sh",".bin",".bat",".rb",".perl"))){
            $error = "INVALID_FILE_TYPE";
        }else{
            $creation = array(
                "link" => realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$config["rest"]["uploadFolder"].DIRECTORY_SEPARATOR.$body["upload"]),
                "path" => $body["path"]
            );
        }

        if($error){
            $response = $response
                ->withStatus(400)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("error" => $error)));
        }else if(Folder::create($creation)){
            $response = $response
                ->withStatus(200)
                ->withHeader("Content-type","application/json");
        }else{
            $response = $response
                ->withStatus(400)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("error" => "NO_UPDATE")));
        }
        return $response;
    });

    $app->put('/resource/{id}', function ($request, $response){
        $id = $request->getAttribute('route')->getArgument('id');
        $body = $request->getParsedBody();
        $update = array();
        if(array_key_exists("data", $body)) $update["data"] = $body["data"];
        if(array_key_exists("upload", $body)) $update["link"] = $body["upload"];
        if(array_key_exists("path", $body)){
            $update["path"] = $body["path"];
        }else if(array_key_exists("name",$body)){
            $update["name"] = $body["name"];
        }
        $changed = Folder::update($id, $update);
        if($changed){
            $response = $response
                ->withStatus(200)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("id" => $changed)));
        }else{
            $response = $response
                ->withStatus(400)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("error" => "NO_UPDATE")));
        }
        return $response;
    });

    $app->delete('/resource/{id}', function ($request, $response){
        $id = $request->getAttribute('route')->getArgument('id');
        if(Folder::remove($id)){
            $response = $response
                ->withStatus(200)
                ->withHeader("Content-type","application/json");
        }else{
            $response = $response
                ->withStatus(400)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("error" => "NOT_DELETED")));
        }
        return $response;
    });
