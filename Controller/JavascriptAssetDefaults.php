<?php

namespace Xanweb\Foundation\Controller;

use Concrete\Core\Controller\Controller;
use Concrete\Core\Http\ResponseFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Xanweb\Foundation\Config\BeforeRenderDefaultAssetJS;
use Xanweb\Foundation\Config\JavascriptAssetDefaults as JavascriptAssetDefaultConfigs;

class JavascriptAssetDefaults extends Controller
{
    /**
     * @var JavascriptAssetDefaultConfigs
     */
    private $jsAssetDefaults;

    public function on_start()
    {
        $config = new JavascriptAssetDefaultConfigs();

        $event = new BeforeRenderDefaultAssetJS($config);
        $this->app['director']->dispatch(BeforeRenderDefaultAssetJS::NAME, $event);

        $this->jsAssetDefaults = $event->getJavascriptAssetDefaults();
    }

    public function getJavascript(): Response
    {
        $content = 'window.xanweb=' . $this->jsAssetDefaults->toJson() . ';';

        return $this->createJavascriptResponse($content);
    }

    private function createJavascriptResponse(string $content): Response
    {
        $rf = $this->app->make(ResponseFactoryInterface::class);

        return $rf->create(
            $content,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/javascript; charset=' . APP_CHARSET,
                'Content-Length' => strlen($content),
            ]
        );
    }
}
