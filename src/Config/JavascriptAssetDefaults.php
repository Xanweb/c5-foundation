<?php
namespace Xanweb\Foundation\Config;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;

class JavascriptAssetDefaults implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    private $items = [];

    public function __construct()
    {
        $this->items = [
            'i18n' => [
            ],
        ];
    }

    public function set($key, $value)
    {
        $this->items = array_set($this->items, $key, $value);
    }

    public function get($key = null)
    {
        if ($key) {
            return array_get($this->items, $key);
        }
        return $this->items;
    }
}