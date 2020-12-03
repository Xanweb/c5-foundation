<?php
namespace Xanweb\Foundation\Request;

use Concrete\Core\Http\Request;
use Concrete\Core\Support\Facade\Application;

class Page
{
    private static $instance;

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
        $app = Application::getFacadeApplication();
        $this->request = $app->make(Request::class);
    }

    public static function getLocale(): string
    {
        $rp = self::get();

        return $rp->cache['locale'] ?? $rp->cache['locale'] = \get_active_locale();
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

    /**
     * Gets a singleton instance of this class.
     *
     * @return Page
     */
    public static function get(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}