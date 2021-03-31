<?php

namespace Xanweb\Foundation;

use Concrete\Core\Application\Application;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Support\Facade\Route;
use RuntimeException;

abstract class ApplicationBooter
{
    /**
     * Class to be used Statically.
     */
    private function __construct()
    {
    }

    /**
     * @param Application $app
     *
     * @throws RuntimeException
     * @noinspection PhpDocMissingThrowsInspection
     */
    final public static function boot(Application $app): void
    {
        static::_boot($app);

        $routeListClasses = static::getRoutesClasses();
        if ($routeListClasses !== []) {
            /**
             * @var \Concrete\Core\Routing\Router $router
             */
            $router = Route::getFacadeRoot();
            foreach ($routeListClasses as $routeListClass) {
                if (is_subclass_of($routeListClass, RouteListInterface::class)) {
                    /** @noinspection PhpUnhandledExceptionInspection */
                    $router->loadRouteList($app->build($routeListClass));
                } else {
                    throw new RuntimeException(t(static::class . ':getRoutesClass: RoutesClass should be instanceof Concrete\Core\Routing\RouteListInterface'));
                }
            }
        }
    }

    abstract protected static function _boot(Application $app): void;

    /**
     * Get Class name for RouteList, must be instance of \Concrete\Core\Routing\RouteListInterface.
     *
     * @return array
     */
    protected static function getRoutesClasses(): array
    {
        return [];
    }
}
