<?php

class Folder{

    public static $ignore_list = array('folder.php', 'manage.php', '.htaccess', '.htpasswd', 'static', 'index.html', 'vendor');

    private static function ignore($dir){
        foreach(Folder::$ignore_list as $start){
            if(strpos($start, $dir) === 0) return true;
        }
        return false;
    }


    private static function id($path){
        return md5($path);
    }

    private static function absolute($path){
        if(strpos($path,'/') === 0 || strpos($path, '\\')) $path = substr($path, 1);
        return __DIR__.DIRECTORY_SEPARATOR.$path;
    }

    private static function getChildIds($path){
        $results = scandir($path);
        return array_map(function($result) use ($path){
            return Folder::id($path . DIRECTORY_SEPARATOR . $result);
        }, $results);
    }

    private static function traverse($dir, $limit = -1) {
        $dh = scandir($dir);
        $return = array();

        foreach ($dh as $folder) {
            if ($folder != '.' && $folder != '..' && !Folder::ignore($folder) && $folder != null && $dir != null) {
                $path = ltrim($dir, '.') . DIRECTORY_SEPARATOR . $folder;
                if (is_dir($dir . DIRECTORY_SEPARATOR . $folder)) {
                    $return[] = array(
                        'id' => Folder::id($path),
                        'name' => $folder,
                        'path' => $path,
                        'type' => 'dir',
                        'children' => ($limit === 0) ? Folder::getChildIds($dir . DIRECTORY_SEPARATOR . $folder) : Folder::traverse($dir . DIRECTORY_SEPARATOR . $folder, $limit > 0 ? $limit-1 : $limit)
                    );
                } elseif (is_file($dir . DIRECTORY_SEPARATOR . $folder)) {
                    $return[] = array(
                        'id' => Folder::id($path),
                        'name' => $folder,
                        'path' => $path,
                        'type' => 'file',
                    );
                }
            }
        }
        return $return;
    }


    public static function structure($limit = -1) {
        return array(
            "id" => Folder::id(""),
            "name" => "",
            "path" => "",
            "type" => "dir",
            "children" => $limit == 0 ?  Folder::getChildIds(".") : Folder::traverse('.', $limit-1)
        );
    }


    public static function get($id, $ignoring = true, $tree = null) {
        // you can do this better giving callbacks to the traverse-function,
        // which handle how the data is processed but this is a pain with PHP
        if($tree === null) $tree = Folder::structure();
        if($tree["id"] === $id){
            return $tree;
        }else if(array_key_exists("children", $tree)){
            $found = null;
            foreach($tree["children"] as $child){
                if($ignoring ? !Folder::ignore($child["path"]) : true){
                    $tmp = Folder::get($id, $ignoring, $child);
                    if($tmp) $found = $tmp;
                }
            }
            return $found;
        }else{
            return null;
        }
    }


    public static function search($expression, $strict = true, $matches = array(), $tree = null) {
        if ($tree === null) $tree = Folder::structure();
        if ($strict ? ($tree["name"] === $expression) : (strpos(strtolower($tree["name"]), strtolower($expression)) !== false)) {
            array_push($matches, $tree);
        }
        if (array_key_exists("children", $tree)) {
            foreach ($tree["children"] as $child) {
                if(!Folder::ignore($child["path"])){
                    $matches = Folder::search($expression, $strict, $matches, $child);

                }
            }
        }
        return $matches;
    }

    public static function remove($id){
        $found = Folder::get($id);
        if($found != null){
            $path = Folder::absolute($found["path"]);
            if($found["type"] === "file"){
                return unlink($path);
            }else{
                return rmdir($path);
            }
        }
        return false;
    }


    public static function update($id, $update){
        if(!$id || !$update) return false;
        $found = Folder::get($id);
        if($found != null){
            $success = false;
            $id = Folder::id($found["path"]);
            if(array_key_exists("name", $update)){
                $update["path"] = dirname($found["path"]) . DIRECTORY_SEPARATOR .$update["name"];
            }
            if(array_key_exists("path",$update) && $update["path"] !== $found["path"]){
                set_error_handler(function(){});
                $success = rename(Folder::absolute($found["path"]), Folder::absolute($update["path"]));
                restore_error_handler();
                $newPath = str_replace('/',DIRECTORY_SEPARATOR, $update["path"]);
                $newPath = str_replace('\\', DIRECTORY_SEPARATOR, $newPath);
                if(strpos($newPath, DIRECTORY_SEPARATOR) !== 0) $newPath = DIRECTORY_SEPARATOR . $newPath;
                $id = Folder::id($newPath);
            }
            set_error_handler(function(){});
            if(array_key_exists("data", $update)){
                $success = $success && file_put_contents(Folder::absolute(array_key_exists("path", $update) ? $update["path"] : $found["path"]), $update["data"]);
            }else if(array_key_exists("link", $update)){
                $success = $success && rename(Folder::absolute($update["link"]), Folder::absolute(array_key_exists("path", $update) ? $update["path"] : $found["path"]));
            }
            restore_error_handler();
            return $success ? $id : null;
        }
        return null;
    }


    public static function create($creation){
        if($creation && array_key_exists("path",$creation)){
            if(!file_exists(Folder::absolute($creation["path"]))){
                set_error_handler(function(){});
                if(array_key_exists("data", $creation)){
                    return file_put_contents(Folder::absolute($creation["path"]), $creation["data"]);
                }else if(array_key_exists("link", $creation)){
                    return rename($creation["link"], Folder::absolute($creation["path"]));
                }
                restore_error_handler();
            }
        }
        return false;
    }
}
