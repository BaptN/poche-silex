<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

use poche\Functions;
use poche\Readability;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Silex\Route;
use Silex\Controller;

$functions = new Functions();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/../storage/poche.sqlite',
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

# view entry
$app->get('view/{id}', function($id) use ($app) {
    $sql = "SELECT * FROM entries WHERE id = ?";
    $entry = $app['db']->fetchAssoc($sql, array(intval($id)));
    if (empty($entry))
        $app->abort(404);
    else
        return $app['twig']->render('view.twig', array(
            'entry' => $entry,
        ));
});

# delete entry
$app->get('delete/{id}', function($id) use ($app) {
    $sql = "DELETE FROM entries WHERE id = ?";
    $app['db']->fetchAssoc($sql, array(intval($id)));
    return $app->redirect('/');
})->assert('id', '\d+');

# add entry
$app->get('add/{url}', function($url) use ($app, $functions) {
    $data = $functions->fetchContent($url);
    if (!empty($data)) {
        $sql = "INSERT INTO entries (url, title, content) VALUES (?, ?, ?) ";
        $entry = $app['db']->fetchAssoc($sql, array($url, $data['title'], $data['content']));
    }

    return $app->redirect('/');
})->assert('url', '.+');

$app['routes']->add('GET_add_url',$route);


$app['debug'] = true;
$app->run();