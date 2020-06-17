<?php
namespace Xanweb\Foundation;

use Concrete\Core\Http\Request;
use Concrete\Core\User\User;
use Xanweb\Foundation\Service\Provider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected function _register(): void
    {
        $this->app->bindIf(User::class, null, true);
        $aliases = [
            'user' => User::class,
            'http/request' => Request::class,
        ];

        foreach ($aliases as $alias => $class) {
            $this->app->alias($class, $alias);
        }

        $this->app->bind('site/active', static function ($app) {
            return $app['site']->getSite();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['user', 'http/request', 'site/active'];
    }
}
