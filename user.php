<?php
/**
 * Created by NasskalteJuni
 * DataObject-Class for interacting with persistent users
 * Exposes typical DataObject-Methods like save and delete
 */

class User{

    private static $file = 'users.csv';
    public $id;
    public $name;
    public $password;
    public $created;

    private static function getAll(){
        $handle = fopen(User::$file,'r+');
        $data = fgetcsv($handle);
        fclose($handle);
        return $data;
    }

    private static function setAll($data){
        $handle = fopen(User::$file, 'w+');
        fputcsv($handle, $data);
        fclose($handle);
    }

    private static function indexInAllOf($id, $all){
        $i = 0;
        foreach($all as $user){
            if($user->id == $id) return $i;
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
            if($user[1] == $name){
                return new User($user);
            }
        }
        return null;
    }

    /**
    * create a new user (perhaps not persistent, call save for that)
    * @param $attrArray array [id, name, password]
    */
    function __construct($attrArray){
        if( count(func_get_args()) >= 3) $attrArray = func_get_args();
        $this->id = $attrArray[0];
        $this->name = $attrArray[1];
        $this->password = $attrArray[2];
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
                array_splice($all,$i , 1);
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
                array_push($all,[$this->id, $this->name, $this->password]);
            }else{
                $all[$i] = [$this->id, $this->name, $this->password];
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
            if($this->name){
                $foundId = User::getByName($this->name)->id;
                if($foundId){
                    $this->id = $foundId;
                }else{
                    $all = User::getAll();
                    $userCount = count($all);
                    if($userCount === 0) $this->id = 1;
                    else $this->id = $all[$userCount-1][0]++;
                }
            }
        }
        if(!$this->name){
            $foundName = User::getById($this->id)->name;
            if($foundName){
                $this->name = $foundName;
            }
        }
        if(!$this->created){
            $foundCreated = User::getById($this->id)->created;
            if($foundCreated){
                $this->created = $foundCreated;
            }else{
                $this->created = date(DATE_ATOM);
            }
        }
        if(!$this->password){
            $foundPassword = User::getById($this->id)->password;
            if($foundPassword){
                $this->password = $foundPassword;
            }
        }
    }


}