<?php

namespace Xanweb\Foundation;

use Concrete\Core\Application\Application;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Support\Facade\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class ApplicationBooter
{
    /**
     * Class to be used Statically.
     */
    private function __construct()
    {
    }

    /**
     * Boot up Application.
     *
     * @param Application $app
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    final public static function boot(Application $app): void
    {
        static::_boot($app);

        if (($routeListClasses = static::getRoutesClasses()) !== []) {
            /**
             * @var \Concrete\Core\Routing\Router $router
             */
            $router = Route::getFacadeRoot();
            foreach ($routeListClasses as $routeListClass) {
                if (is_subclass_of($routeListClass, RouteListInterface::class)) {
                    /** @noinspection PhpUnhandledExceptionInspection */
                    $router->loadRouteList($app->build($routeListClass));
                } else {
                    self::throwInvalidClassRuntimeException('getRoutesClass', $routeListClass, RouteListInterface::class);
                }
            }
        }

        // Register Event Subscribers
        if (($evtSubscriberClasses = static::getEventSubscribers()) !== []) {
            $director = $app->make(EventDispatcherInterface::class);
            foreach ($evtSubscriberClasses as $evtSubscriberClass) {
                if (is_subclass_of($evtSubscriberClass, EventSubscriberInterface::class)) {
                    /** @noinspection PhpUnhandledExceptionInspection */
                    $director->addSubscriber($app->build($evtSubscriberClass));
                } else {
                    self::throwInvalidClassRuntimeException('getEventSubscribers', $evtSubscriberClass, EventSubscriberInterface::class);
                }
            }
        }
    }

    private static function throwInvalidClassRuntimeException(string $relatedMethod, $targetClass, string $requiredClass): void
    {
        throw new \RuntimeException(t('%s:%s - `%s` should be an instance of `%s`', static::class, $relatedMethod, (string) $targetClass, $requiredClass));
    }

    abstract protected static function _boot(Application $app): void;

    /**
     * Get Class name for RouteList, must be instance of \Concrete\Core\Routing\RouteListInterface.
     *
     * @return string[]
     */
    protected static function getRoutesClasses(): array
    {
        return [];
    }

    /**
     * Event Subscribers should be instance of \Symfony\Component\EventDispatcher\EventSubscriberInterface.
     *
     * @return string[]
     */
    protected static function getEventSubscribers(): array
    {
        return [];
    }
}
