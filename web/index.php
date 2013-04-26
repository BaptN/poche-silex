<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

use poche\Functions;
use poche\Readability;

$functions = new Functions();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/poche.sqlite',
    ),
));

# home
$app->get('/', function() use ($app) {

    $sql = "SELECT * FROM entries ORDER BY id";
    $entries = $app['db']->fetchAll($sql);

    return $app['twig']->render('index.twig', array(
        'entries' => $entries,
    ));
});

# view article
$app->get('view/{id}', function($id) use ($app) {

    $sql = "SELECT * FROM entries WHERE id = ?";
    $entry = $app['db']->fetchAssoc($sql, array(intval($id)));

    error_log(print_r($entry, true));

    return $app['twig']->render('view.twig', array(
        'entry' => $entry,
    ));

});

$app['debug'] = true;
$app->run();
