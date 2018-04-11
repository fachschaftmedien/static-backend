<?php
    require_once  __DIR__.'/vendor/autoload.php';

    $config = \Zend\Config\Factory::fromFile(__DIR__ . DIRECTORY_SEPARATOR . 'config.json');

    $slimSettings = [
        'settings' => [
            'displayErrorDetails' => $config['mode'] === 'dev',
        ],
    ];

    $app = new Slim\App($slimSettings);

    include_once __DIR__.'/middleware.php';

    include_once __DIR__.'/routes/auth.php';
    include_once __DIR__.'/routes/resource.php';
    include_once __DIR__.'/routes/upload.php';
    include_once __DIR__.'/routes/download.php';

    $app->run();
