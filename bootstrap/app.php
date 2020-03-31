<?php defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\User\User;
use Concrete\Core\Support\Facade\Application;

$app = Application::getFacadeApplication();
$app->bindIf(User::class, null, true);
$aliases = [
    'user' => User::class,
    'http/request' => 'Concrete\Core\Http\Request',
    'database/connection' => 'Concrete\Core\Database\Connection\Connection',
];

foreach ($aliases as $alias => $class) {
    $app->alias($class, $alias);
}

$app->bind('site/active', function ($app) {
    return $app['site']->getSite();
});
