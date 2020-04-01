<?php
namespace Xanweb\Foundation;

use Concrete\Core\User\User;
use Concrete\Core\Foundation\Service\Provider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->bindIf(User::class, null, true);
        $aliases = [
            'user' => User::class,
            'http/request' => 'Concrete\Core\Http\Request',
        ];

        foreach ($aliases as $alias => $class) {
            $this->app->alias($class, $alias);
        }

        $this->app->bind('site/active', function ($app) {
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
