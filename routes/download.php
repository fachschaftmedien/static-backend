<?php

    require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'folder.php');

    $app->post('/download', function($request, $response){
        $config = \Zend\Config\Factory::fromFile(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.json');
        $body = $request->getParsedBody();
        if(array_key_exists("ids",$body)){
            $ids = is_string($body["ids"]) ? json_decode($body["ids"]) : $body["ids"];
            $files = array();
            foreach($ids as $id){
                $found = Folder::get($id);
                if($found){
                    $path =  realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $found["path"]);
                    array_push( $files, $path);
                }
            }
            $zipname = 'file.zip';
            $zip = new ZipArchive();
            $zip->open($zipname, ZipArchive::CREATE);
            foreach ($files as $file) {
                $zip->addFile($file);
            }
            $zip->close();
            $stream = new \Slim\Http\Stream($zip);

            $response = $response
                ->withStatus(200)
                ->withHeader('Content-type','application/zip')
                ->withHeader('Content-description', 'File Transfer')
                ->withHeader('Content-transfer-encoding', 'binary')
                ->withHeader('Content-disposition','attachment; filename='.$zipname)
                ->withHeader('Content-length',filesize($zipname))
                ->withHeader('Expires', 0)
                ->withBody($stream);
            unlink($zipname);
        }else{
            $response = $response
                ->withStatus(400)
                ->withHeader("Content-type","application/json");
            $response->getBody()->write(json_encode(array("error" => "NO_IDS")));
        }

        return $response;
    });