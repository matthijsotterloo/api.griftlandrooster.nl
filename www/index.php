<?php
/*
 * Copyright 2015 Matthijs Otterloo.
 */

require __DIR__ . '/vendor/autoload.php';

$app = new Bullet\App();

$app->path('/zermelo', function() use ($app) {
    $handler = new \Zermelo\Handler();
    require('lib/Core/DefaultRouter.php');
});

// Run the app
echo $app->run(new Bullet\Request());
