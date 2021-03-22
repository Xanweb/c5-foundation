<?php
namespace Xanweb\Foundation\Route;

use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;
use Xanweb\Foundation\Controller\JavascriptAssetDefaults;

class RouteList implements RouteListInterface
{
    public function loadRoutes(Router $router): void
    {
        $router->get('/xw/js/defaults.js', JavascriptAssetDefaults::class . '::getJavascript');
    }
}
