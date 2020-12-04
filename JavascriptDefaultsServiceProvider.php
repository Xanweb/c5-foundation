<?php

namespace Xanweb\Foundation;

use Concrete\Core\Asset\AssetList;
use Concrete\Core\Support\Facade\Route;
use Xanweb\Foundation\Route\RouteList;
use Xanweb\Foundation\Service\Provider as FoundationProvider;

class JavascriptDefaultsServiceProvider extends FoundationProvider
{
    protected function _register(): void
    {
        $router = Route::getFacadeRoot();
        /** @noinspection PhpUnhandledExceptionInspection */
        $router->loadRouteList($this->app->build(RouteList::class));

        $this->registerAssets();
    }

    private function registerAssets(): void
    {
        $al = AssetList::getInstance();
        $al->register('javascript-localized', 'xw/defaults', '/xw/js/defaults.js');
    }
}
