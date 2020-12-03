<?php
namespace Xanweb\Foundation;

use Concrete\Core\Asset\AssetList;
use Concrete\Core\Foundation\ClassAliasList;
use Concrete\Core\Http\Request as HttpRequest;
use Concrete\Core\User\User;
use Concrete\Core\Support\Facade\Route;
use Xanweb\Foundation\Request;
use Xanweb\Foundation\Route\RouteList;
use Xanweb\Foundation\Service\Provider as FoundationProvider;

class ServiceProvider extends FoundationProvider
{
    protected function _register(): void
    {
        $this->app->bindIf(User::class, null, true);
        $aliases = [
            'user' => User::class,
            'http/request' => HttpRequest::class,
        ];

        foreach ($aliases as $alias => $class) {
            $this->app->alias($class, $alias);
        }

        $this->app->bind('site/active', static function ($app) {
            return $app['site']->getSite();
        });

        $classAliasList = ClassAliasList::getInstance();
        $classAliasList->registerMultiple([
            'RequestUser' => Request\User::class,
            'RequestPage' => Request\Page::class,
            'RequestSite' => Request\Site::class,
        ]);

        $router = Route::getFacadeRoot();
        $router->loadRouteList($this->app->build(RouteList::class));

        $this->registerAssets();
    }

    public function provides(): array
    {
        return ['user', 'http/request', 'site/active'];
    }

    protected function registerAssets(): void
    {
        $al = AssetList::getInstance();
        $al->register('javascript-localized', 'xw/defaults', '/xw/js/defaults.js');
    }
}
