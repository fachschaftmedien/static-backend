<?php

    require_once "../authManager.php";

    echo "authManageer tests:".PHP_EOL.PHP_EOL;
    echo "register new user without name and password fails".PHP_EOL;
    echo AuthManager::register("","");
    echo PHP_EOL;

    echo "register new user without name fails".PHP_EOL;
    echo AuthManager::register("","123");
    echo PHP_EOL;

    echo "register new user without password fails".PHP_EOL;
    echo AuthManager::register("abc","");
    echo PHP_EOL;

    echo "register new user with name and password works.".PHP_EOL;
    echo AuthManager::register("test","test");
    echo PHP_EOL;

    echo "register new user with already existing name fails.".PHP_EOL;
    echo AuthManager::register("test", "123");
    echo PHP_EOL;

    echo "loggin in with the new user returns JWT.".PHP_EOL;
    echo AuthManager::login("test","test");
    echo PHP_EOL;

    echo "deleting non-existing-user fails".PHP_EOL;
    echo AuthManager::unregister("nonexisting-user-name","none-existing");
    echo PHP_EOL;

    echo "deleting the new user without valid password fails.".PHP_EOL;
    echo AuthManager::unregister("test","abc");
    echo PHP_EOL;

    echo "deleting the new user with valid password works.".PHP_EOL;
    echo AuthManager::unregister("test","test");
    echo PHP_EOL;