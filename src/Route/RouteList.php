<?php
namespace Xanweb\Foundation\Route;

use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class RouteList implements RouteListInterface
{
    public function loadRoutes(Router $router): void
    {
        $router->buildGroup()
            ->setNamespace('Xanweb\Foundation\Controller')
            ->routes(function (Router $r) {
                $r->get('/xw/js/defaults.js', 'JavascriptAssetDefaults::getJavascript');
            });
    }
}