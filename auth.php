<?php
require_once('vendor/autoload.php');
require_once('./config.php');
require_once('./user.php');

$name = $_POST['name'];
$password = $_POST['password'];


class Auth{

    private function genPw($plain, $salt){
        $pw = $plain;
        for($i = 0; $i < 5; $i++){
            $pw = hash('sha512', $salt . $pw);
        }
        return $pw;
    }

    function __construct(){

    }

    function check($name, $password){
        $reference = User::getByName($name);
        return $reference && $reference->password == $this->genPw($password,$reference->created);
    }

    function login($name, $password){
        if ($this->check($name, $password)) {
            $reference = User::getByName($name);
            $tokenId    = base64_encode(random_bytes(32));        //TODO: think about something when no valid random source is present
            $issuedAt   = time();
            $notBefore  = $issuedAt + $config->get('rest.startDelay');  //Add seconds after issuing until the token is valid
            $expire     = $notBefore + $config->get('rest.validity');   // Adding 60 seconds
            $serverName = $config->get('serverName');                   // Retrieve the server name from config file

            /*
             * Create the token as an array
             */
            $data = [
                'iat'  => $issuedAt,                                    // Issued at: time when the token was generated
                'jti'  => $tokenId,                                     // Json Token Id: an unique identifier for the token
                'iss'  => $serverName,                                  // Issuer
                'nbf'  => $notBefore,                                   // Not before
                'exp'  => $expire,                                      // Expire
                'data' => [                                             // Data related to the signer user
                    'userId'   => $reference->id,                       // userid from the users table
                    'userName' => $reference->name                      // User name
                ]
            ];
        }
    }

    function register($name, $password){
        if(!$name){
            return 'NO_NAME';
        }
        if(!$password){
            return 'NO_PASSWORD';
        }
        $user = User::getByName($name);
        if(!$user){
            $user = new User([null, null, null, null]);
            $user->complete();
            $user->name = $name;
            $user->password = $this->genPw($password, $user->created);
            $user->save();
            return '';
        }else{
            return 'NAME_IN_USE';
        }
    }

    function unregister($name, $password){
        if(!$name){
            return 'NO_NAME';
        }
        if(!$password){
            return 'NO_PASSWORD';
        }
        $user = User::getByName($name);
        if($user){
            if($this->check($user->name, $user->password)){
                $user->delete();
                return '';
            }else{
                return 'INVALID_CREDENTIALS';
            }
        }else{
            return 'NAME_IN_USE';
        }
    }

}

