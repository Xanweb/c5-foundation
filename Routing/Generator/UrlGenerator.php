<?php

namespace Xanweb\Foundation\Routing\Generator;

use Concrete\Core\Http\Request;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlGenerator implements UrlGeneratorContract
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ResolverManagerInterface
     */
    protected $url;

    /**
     * The root namespace being applied to controller actions.
     *
     * @var string
     */
    protected $assetRoot;

    /**
     * The root namespace being applied to controller actions.
     *
     * @var string
     */
    protected $rootNamespace;

    /**
     * UrlGenerator constructor.
     *
     * @param Request $request
     * @param ResolverManagerInterface $urlResolver
     * @param string $assetRoot
     */
    public function __construct(Request $request, ResolverManagerInterface $urlResolver, string $assetRoot)
    {
        $this->request = $request;
        $this->url = $urlResolver;
        $this->assetRoot = rtrim($assetRoot, '/') . '/';

        if ($c = $request->getCurrentPage()) {
            $this->setRootControllerNamespace($c->getPageController()->getControllerActionPath());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->request->getUri();
    }

    /**
     * {@inheritdoc}
     */
    public function to($path, $extra = [], $secure = null)
    {
        return $this->url->resolve([$path] + $extra);
    }

    /**
     * {@inheritdoc}
     */
    public function secure($path, $parameters = [])
    {
        return $this->to($path, $parameters, true);
    }

    /**
     * {@inheritdoc}
     */
    public function asset($path, $secure = null): string
    {
        return $this->assetRoot . ltrim($path, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        /**
         * @var \Concrete\Core\Url\Resolver\RouterUrlResolver $router
         */
        $router = $this->url->getResolver('concrete.route');
        if (($route = $router->getRouteList()->get($name)) !== null) {
            $refType = $absolute ? UrlGeneratorInterface::ABSOLUTE_PATH : UrlGeneratorInterface::RELATIVE_PATH;
            $generator = $router->getGenerator();
            if ($path = $generator->generate($name, $parameters, $refType)) {
                return $this->url->getResolver('concrete.path')->resolve([$path]);
            }
        }

        throw new RouteNotFoundException("Route [{$name}] not defined.");
    }

    /**
     * {@inheritdoc}
     */
    public function action($action, $parameters = [], $absolute = true)
    {
        array_unshift($parameters, $action);
        if ($this->rootNamespace !== null) {
            array_unshift($parameters, $this->rootNamespace);
        }

        if ($absolute) {
            return $this->url->getDefaultResolver()->resolve($parameters);
        }

        return $this->request->getPathInfo() . '/' . implode('/', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setRootControllerNamespace($rootNamespace)
    {
        $this->rootNamespace = $rootNamespace;
    }
}
