<?php


    require_once __DIR__.'/../vendor/autoload.php';

    $app->get('/uploads', function($request, $response){
        $config = \Zend\Config\Factory::fromFile(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.json');
        $uploadDir = $config['rest']['uploadFolder'];
        $result = scandir($uploadDir);
        if($result !== false){
            $response = $response
                ->withStatus(200)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode($result));
        }else{
            $response = $response
                ->withStatus(500)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("error" => "NO_SCAN")));
        }
        return $response;
    });

    $app->post('/upload/', function ($request, $response){
        $config = \Zend\Config\Factory::fromFile(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.json');
        $uploadedFiles = $request->getUploadedFiles();
        $data = null;
        $error = null;
        $id = null;
        if(array_key_exists("data", $uploadedFiles)){
            $data = $uploadedFiles["data"];
            if(!$data){
                $error = "NO_DATA";
            }else if($data->getSize() === null){
                $error = "UPLOAD_PROBLEM";
            }else if($data->getSize() === 0){
                $error = "FILE_EMPTY";
            }else if($data->getSize() > 10 * 1024 * 1024){
                $error = "FILE_OVER_10MB";
            }else {
                $id = md5(time());
                $data->moveTo(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$config['rest']['uploadFolder'].DIRECTORY_SEPARATOR.$id);
            }

        }else{
            $error = "NO_DATA_UPLOAD_FIELD";
        }

        if($error){
            $response = $response
                ->withStatus(400)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("error" => $error)));
        }else{
            $response = $response
                ->withStatus(201)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("id" => $id)));
        }

        return $response;
    });

