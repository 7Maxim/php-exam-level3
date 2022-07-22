<?php

// Start a Session
if (!session_id()) @session_start();


require '../vendor/autoload.php';

use DI\ContainerBuilder;
use League\Plates\Engine;
use Delight\Auth\Auth;
use Aura\SqlQuery\QueryFactory;


define('USERS_TABLE_NAME', 'users');
define('USER_IMAGE_DIR', 'img/demo/authors/');
define('USER_IMAGE_DEFAULT', 'avatar-m.png');


$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    Engine::class => function () {
        return new Engine('../app/views');
    },

    PDO::class => function () {
        $driver = 'mysql';
        $host = 'localhost';
        $database_name = 'level_3_project-auth';
        $username = 'root';
        $password = '';

        return new PDO("$driver:host=$host;dbname=$database_name", $username, $password);
    },

    Auth::class => function ($container) {
        return new Auth($container->get('PDO'));
    },

    QueryFactory::class => function () {
        return new QueryFactory('mysql');
    },
]);

$container = $containerBuilder->build();


$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {

    $r->addRoute('GET', '/', ['App\controllers\HomeController', 'users']);
    $r->addRoute('GET', '/users', ['App\controllers\HomeController', 'users']);

    // Регистрация
    $r->addRoute('GET', '/register', ['App\controllers\HomeController', 'register']);
    $r->addRoute('POST', '/user-register', ['App\controllers\HomeController', 'user_register']);


    $r->addRoute('GET', '/verification', ['App\controllers\HomeController', 'email_verification']);

    // Логин
    $r->addRoute('GET', '/login', ['App\controllers\HomeController', 'login']);


    $r->addRoute('POST', '/user-login', ['App\controllers\HomeController', 'user_login']);

    // logOut
    $r->addRoute('GET', '/logout', ['App\controllers\HomeController', 'logOut']);


    $r->addRoute('GET', '/create-user', ['App\controllers\HomeController', 'create_user']);


    $r->addRoute('POST', '/create-user-handler', ['App\controllers\HomeController', 'create_user_handler']);


    $r->addRoute('GET', '/delete-user/{id:\d+}', ['App\controllers\HomeController', 'delete_user']);


// Редактировать пользователя
    $r->addRoute('GET', '/edit/{id:\d+}', ['App\controllers\HomeController', 'edit']);
    $r->addRoute('POST', '/edit-user/{id:\d+}', ['App\controllers\HomeController', 'edit_user']);


    // Безопасность
    $r->addRoute('GET', '/security/{id:\d+}', ['App\controllers\HomeController', 'security']);
    $r->addRoute('POST', '/security-handler/{id:\d+}', ['App\controllers\HomeController', 'security_handler']);


    // Статус
    $r->addRoute('GET', '/status/{id:\d+}', ['App\controllers\HomeController', 'status']);
    $r->addRoute('POST', '/status-handler/{id:\d+}', ['App\controllers\HomeController', 'status_handler']);


    // Загрузить аватар
    $r->addRoute('GET', '/media/{id:\d+}', ['App\controllers\HomeController', 'media']);
    $r->addRoute('POST', '/media-handler/{id:\d+}', ['App\controllers\HomeController', 'media_handler']);


    $r->addRoute('GET', '/profile/{id:\d+}', ['App\controllers\HomeController', 'profile']);


});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo 404;
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed

        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:


        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        $container->call($handler, $vars);

        break;
}