<?php

namespace Xanweb\Foundation;

use Concrete\Core\Foundation\ClassAliasList;
use Concrete\Core\Http\Request as HttpRequest;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\User\User;
use Xanweb\Foundation\Request;
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
            'MultilingualSection' => Section::class,
        ]);
    }

    public function provides(): array
    {
        return ['user', 'http/request', 'site/active'];
    }
}
