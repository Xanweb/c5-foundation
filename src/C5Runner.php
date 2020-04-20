<?php
namespace Xanweb\Foundation;

use Concrete\Core\Foundation\ClassAliasList;
use Concrete\Core\Foundation\Service\ProviderList;
use Concrete\Core\Support\Facade\Route;

abstract class C5Runner
{
    use Traits\StaticApplicationTrait;

    /**
     * Class to be used Statically.
     */
    private function __construct()
    {
        return false;
    }

    public static function boot()
    {
        $aliases = static::getClassAliases();
        if (!empty($aliases)) {
            $aliasList = ClassAliasList::getInstance();
            $aliasList->registerMultiple($aliases);
        }

        $app = self::app();
        $providers = static::getServiceProviders();
        if (is_array($providers) && !empty($providers)) {
            $app->make(ProviderList::class)->registerProviders($providers);
        }

        $routeListClasses = static::getRoutesClasses();
        if (is_array($routeListClasses) && !empty($routeListClasses)) {
            /**
             * @var \Concrete\Core\Routing\Router
             */
            $router = Route::getFacadeRoot();
            foreach ($routeListClasses as $routeListClass) {
                if (is_subclass_of($routeListClass, 'Concrete\Core\Routing\RouteListInterface')) {
                    $router->loadRouteList($app->build($routeListClass));
                } else {
                    throw new \Exception(t(get_called_class() . ':getRoutesClass: RoutesClass should be instanceof Concrete\Core\Routing\RouteListInterface'));
                }
            }
        }
    }

    /**
     * Classes to be registered as aliases in \Concrete\Core\Foundation\ClassAliasList.
     *
     * @return array
     */
    protected static function getClassAliases()
    {
        return [];
    }

    /**
     * Get Service Providers Class Names.
     *
     * @return array
     */
    protected static function getServiceProviders()
    {
        return [];
    }

    /**
     * Get Class name for RouteList, must be instance of \Concrete\Core\Routing\RouteListInterface.
     *
     * @return array
     */
    protected static function getRoutesClasses()
    {
        return [];
    }
}
