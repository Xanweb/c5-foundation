<?php

namespace Xanweb\Foundation;

use Concrete\Core\Foundation\ClassAliasList;
use Concrete\Core\Http\Request as HttpRequest;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\User\User;
use Xanweb\Common\Service\Provider as FoundationProvider;
use Xanweb\C5\Request\ServiceProvider as RequestServiceProvider;

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

        ClassAliasList::getInstance()->registerMultiple([
            'MultilingualSection' => Section::class,
        ]);

        $requestProvider = new RequestServiceProvider($this->app);
        $requestProvider->register();
    }

    public function provides(): array
    {
        return ['user', 'http/request', 'site/active'];
    }
}
