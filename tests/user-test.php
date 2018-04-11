<?php
    require_once '../user.php';
    require_once '../authManager.php';

    // rewrite this to PHP Unit some time
    echo "user tests:".PHP_EOL.PHP_EOL;


    echo "expect new user without parameter-array to have just null values for every attribute:".PHP_EOL;
    $u = new User();
    echo $u;
    echo PHP_EOL;


    echo "expect new user without attributes to have attributes id and created after calling complete".PHP_EOL;
    $u->complete();
    echo $u;
    echo PHP_EOL;


    echo "expect save to fail with false, when not all attributes are present".PHP_EOL;
    echo $u->save();
    echo PHP_EOL;


    echo "expect save to persist the user, when all attributes are present".PHP_EOL;
    $u->name = "test";
    $u->password = "test";
    echo $u->save();
    echo PHP_EOL;


    echo "expect to get the user by its id from persistent state".PHP_EOL;
    echo User::getById($u->id);
    echo PHP_EOL;


    echo "expect to get the user by its name from persisten state".PHP_EOL;
    echo User::getByName($u->name);
    echo PHP_EOL;


    echo "expect the user to remove itself on delete".PHP_EOL;
    echo $u->delete();
    echo PHP_EOL;


    echo "expect the user to be not found after deletion".PHP_EOL;
    echo User::getById($u->id);
