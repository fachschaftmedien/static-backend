<?php
require_once('vendor/autoload.php');
require_once('user.php');


class AuthManager{

    private static function genPw($plain, $salt){
        $pw = $plain;
        for($i = 0; $i < 5; $i++){
            $pw = hash('sha512', $salt . $pw);
        }
        return $pw;
    }


    private static function checkCredentials($name, $password){
        $reference = User::getByName($name);
        return $reference && $reference->password == AuthManager::genPw($password,$reference->created);
    }


    public static function checkToken($jwt){
        if($jwt) return false;
        $token = null;
        try{
            $config = \Zend\Config\Factory::fromFile(__DIR__.'/config.json');
            $secretKey = base64_decode($config['rest']['secret']);
            $token = \Firebase\JWT\JWT::decode($jwt, $secretKey, array('HS512'));
        }catch(Exception $err){
            $token = null;
        }
        return $token != null;
    }


    public static function login($name, $password){
        // check the credentials
        if ($name && $password && AuthManager::checkCredentials($name, $password)) {
            $reference = User::getByName($name);
            try{
                $tokenId    = base64_encode(random_bytes(32));
            }catch(Exception $err){
                error_log('insecure token creation - could not use random bytes');
                $tokenId = base64_encode(rand(0,1000));
            }
            $issuedAt   = time();
            $config = \Zend\Config\Factory::fromFile(__DIR__.'/config.json');
            $notBefore  = $issuedAt + $config['rest']['startDelay'];
            $expire     = $notBefore + $config['rest']['validity'];
            $serverName = $config['webhost'];

            /*
             * Create the token as an array
             */
            $data = [
                'iat'  => $issuedAt,                                    // time when the token was generated
                'jti'  => $tokenId,                                     // an unique identifier for the token
                'iss'  => $serverName,                                  // is valid on domain
                'nbf'  => $notBefore,                                   // starts being valid
                'exp'  => $expire,                                      // ends being valid
                'data' => [                                             // Data related to the signer user
                    'userId'   => $reference->id,
                    'userName' => $reference->name
                ]
            ];

            $secretKey = base64_decode($config['rest']['secret']);

            $jwt = \Firebase\JWT\JWT::encode(
                $data,                                                  // encode the data for the JWT
                $secretKey,                                             // use the server secret key to sign the token
                'HS512'                                             // Algorithm used to sign the token
            );

            return $jwt;
        }
        return null;
    }


    public static function register($name, $password){
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
            $user->password = AuthManager::genPw($password, $user->created);
            $user->save();
            return '';
        }else{
            return 'NAME_IN_USE';
        }
    }

    public static function unregister($name, $password){
        if(!$name){
            return 'NO_NAME';
        }
        if(!$password){
            return 'NO_PASSWORD';
        }
        $user = User::getByName($name);
        if($user){
            if(AuthManager::checkCredentials($name, $password)){
                $user->delete();
                return '';
            }else{
                return 'INVALID_CREDENTIALS';
            }
        }else{
            return 'NO_SUCH_USER';
        }
    }

}

