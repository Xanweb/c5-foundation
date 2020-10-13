<?php
namespace Xanweb\Foundation\Controller;

use Concrete\Core\Controller\Controller;
use Concrete\Core\Http\ResponseFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Xanweb\Foundation\Config\JavascriptAssetDefaults as JavascriptAssetDefaultConfigs;

class JavascriptAssetDefaults extends Controller
{
    private function getJsConfig(array $items)
    {
        $content = '{';
        $lastKey = array_keys($items)[count($items) - 1];
        foreach ($items as $key => $value) {
            $content .= '"' . $key . '": ';
            if (is_array($value)) {
                $content .= $this->getJsConfig($value);
            } else {
                if (substr(str_replace(' ', '', $value), 0, 8) == 'function') {
                    $content .= $value;
                }else {
                    $content .= json_encode($value);
                }
            }
            if ($lastKey != $key) {
                $content .= ',';
            }
        }
        $content .= '}';
        return $content;
    }

    public function getJavascript(): Response
    {
        $items = $this->app->make(JavascriptAssetDefaultConfigs::class)->get();
        $content = 'window.xanweb = '. $this->getJsConfig($items) .';';

        return $this->createJavascriptResponse($content);
    }

    private function createJavascriptResponse(string $content): Response
    {
        $rf = $this->app->make(ResponseFactoryInterface::class);

        return $rf->create(
            $content,
            200,
            [
                'Content-Type' => 'application/javascript; charset=' . APP_CHARSET,
                'Content-Length' => strlen($content),
            ]
        );
    }
}
