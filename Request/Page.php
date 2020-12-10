<?php
namespace Xanweb\Foundation\Request;

use Concrete\Core\Http\Request;
use Xanweb\Foundation\Traits\SingletonTrait;

class Page
{
    use SingletonTrait;
    use AttributesTrait;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct()
    {
        $this->request = c5app(Request::class);
    }

    public static function getLocale(): string
    {
        $rp = self::get();

        return $rp->cache['locale'] ?? $rp->cache['locale'] = \current_locale();
    }

    public static function getLanguage(): string
    {
        return \current(\explode('_', self::getLocale()));
    }

    public static function isEditMode(): bool
    {
        $rp = self::get();

        return $rp->cache['isEditMode'] ?? ($rp->cache['isEditMode'] = (($c = $rp->request->getCurrentPage()) !== null && $c->isEditMode()));
    }

    public static function getAttribute($ak, $mode = false)
    {
        $rp = self::get();
        $c = $rp->request->getCurrentPage();
        if ($c === null) {
            return null;
        }

        return self::_getAttribute($c, $ak, $mode);
    }

    public function __call($name, $arguments)
    {
        $c = $this->request->getCurrentPage();
        if ($c !== null) {
            return $c->$name(...$arguments);
        }
    }

    public static function __callStatic($name, $arguments)
    {
        return self::get()->$name(...$arguments);
    }
}