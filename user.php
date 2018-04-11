<?php
/**
 * Created by NasskalteJuni
 * DataObject-Class for interacting with persistent users
 * Exposes typical DataObject-Methods like save and delete
 */

class User{

    private static $file = __DIR__.'/users.csv';
    public $id;
    public $name;
    public $password;
    public $created;

    /**
     * create a new user (perhaps not persistent, call save for that)
     * @param $attrArray array [id, name, password]
     */
    function __construct($attrArray = array(null, null, null, null)){
        if( count(func_get_args()) >= 4) $attrArray = func_get_args();
        $this->id = $attrArray[0];
        $this->name = $attrArray[1];
        $this->password = $attrArray[2];
        $this->created = $attrArray[3];
    }

    /**
     * converts this user object to a string
     * @return String id,name,password,created
     */
    function __toString() {
        return $this->id.",".$this->name.",".$this->password.",".$this->created;
    }

    private static function getAll(){
        $data = array_map('str_getcsv', file(User::$file));
        if(!$data) $data = array();
        return $data;
    }

    private static function setAll($data){
        $handle = fopen(User::$file, 'wr+');
        foreach ($data as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);
        $contents = "";
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }
        fclose($handle);
        return $contents;
    }

    private static function indexInAllOf($id, $all){
        $i = 0;
        foreach($all as $user){
            if($user && $user[0] == $id) return $i;
            $i++;
        }
        return -1;
    }

    /**
    * get a user with the given ID
    * @param $id integer the id of the user
    * @return User the found user or null
    */
    public static function getById($id){
        foreach(User::getAll() as $user){
            if($user[0] == $id){
                return new User($user);
            }
        }
        return null;
    }

    /**
    * get user by name
    * @param $name string the name of the searched user
    * @return User the found User or null
    */
    public static function getByName($name){
        foreach(User::getAll() as $user){
            if($user && $user[1] == $name){
                return new User($user);
            }
        }
        return null;
    }

    /**
    * persistently delete this user
    * @return boolean indicating if the user could be deleted (true: success)
    */
    function delete(){
        $this->complete();
        // try autocompleting, if successful, delete
        if($this->isComplete()){
            $all = User::getAll();
            $i = User::indexInAllOf($this->id, $all);
            if($i >= 0){
                array_splice($all, $i , 1);
                User::setAll($all);
                return true;
            }
            return false;
        }else{
            return false;
        }
    }

    /**
    * persist this user in its current state
    * @return boolean indicating if the user was saved (true: success)
    */
    function save(){
        $this->complete();
        // if auto completion could find values or the object is now complete, save
        if($this->isComplete()){
            $all = User::getAll();
            $i = User::indexInAllOf($this->id, $all);
            if($i === -1){
                array_push($all,[$this->id, $this->name, $this->password, $this->created]);
            }else{
                $all[$i] = [$this->id, $this->name, $this->password, $this->created];
            }
            User::setAll($all);
            return true;
        }else{
            return false;
        }
    }

    /**
    * tells if the user has set every attribute set
    * @return boolean every attribute set with a not falsely value equals true
    */
    function isComplete(){
        return $this->id && $this->name && $this->password && $this->created;
    }

    /**
    * tries to complete missing fields of this user by the duplicated unity of name and id and checking the saved state
    */
    function complete(){
        // try to complete the user ID by username or assigning next ID
        if(!$this->id){
            $isNew = true;
            if($this->name){
                $foundUser = User::getByName($this->name);
                if($foundUser && $foundUser->id){
                    $this->id = $foundUser->id;
                    $isNew = false;
                }
            }
            if($isNew){
                $all = User::getAll();
                $userCount = count($all);
                if($userCount === 0) $this->id = 1;
                else $this->id = intval($all[$userCount-1][0])+1;
            }
        }
        if(!$this->name){
            $foundUser = User::getById($this->id);
            if($foundUser && $foundUser->name){
                $this->name = $foundUser->name;
            }
        }
        if(!$this->created){
            $foundUser = User::getById($this->id);
            if($foundUser && $foundUser->created){
                $this->created = $foundUser->created;
            }else{
                $this->created = date(DATE_ATOM);
            }
        }
        if(!$this->password){
            $foundUser = User::getById($this->id);
            if($foundUser && $foundUser->password){
                $this->password = $foundUser->password;
            }
        }
    }


}