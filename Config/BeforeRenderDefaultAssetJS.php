<?php

namespace Xanweb\Foundation\Config;

use Symfony\Component\EventDispatcher\Event;

class BeforeRenderDefaultAssetJS extends Event
{
    public const NAME = 'on_before_render_default_assets_js';

    /**
     * @var JavascriptAssetDefaults
     */
    private $javascriptAssetDefaults;

    public function __construct(JavascriptAssetDefaults $defaults)
    {
        $this->javascriptAssetDefaults = $defaults;
    }

    /**
     * @return JavascriptAssetDefaults
     */
    public function getJavascriptAssetDefaults(): JavascriptAssetDefaults
    {
        return $this->javascriptAssetDefaults;
    }
}