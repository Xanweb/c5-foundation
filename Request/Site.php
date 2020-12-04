<?php
namespace Xanweb\Foundation\Request;

use Concrete\Core\Page\Page as ConcretePage;
use Concrete\Core\Support\Facade\Application;
use Xanweb\Foundation\Request\Page as RequestPage;

class Site
{
    private static $instance;

    /**
     * @var \Concrete\Core\Entity\Site\Site
     */
    private $site;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct()
    {
        $app = Application::getFacadeApplication();
        $this->site = $app->make('site/active');
    }

    public static function getSiteHomePageObject(): ?Page
    {
        $rs = self::get();
        if (!isset($rs->cache['siteHomePageObject']) && $homePageID = self::getSiteHomePageID()) {
            $homePage = ConcretePage::getByID($homePageID, 'ACTIVE');
            if (is_object($homePage) && !$homePage->isError()) {
                $rs->cache['siteHomePageObject'] = $homePage;
            }
        }

        return $rs->cache['siteHomePageObject'];
    }

    public static function getSiteHomePageID(): ?int
    {
        $rp = self::get();

        return $rp->cache['siteHomePageID'] ?? $rp->cache['siteHomePageID'] = $rp->site->getSiteHomePageID();
    }

    public static function getLocaleHomePageObject(): ?Page
    {
        $rs = self::get();
        if (!isset($rs->cache['localeHomePageObject']) && $homePageID = self::getLocaleHomePageID()) {
            $homePage = ConcretePage::getByID($homePageID, 'ACTIVE');
            if (is_object($homePage) && !$homePage->isError()) {
                $rs->cache['localeHomePageObject'] = $homePage;
            }
        }

        return $rs->cache['localeHomePageObject'];
    }

    public static function getLocaleHomePageID(): ?int
    {
        $rp = self::get();
        if (!isset($rp->cache['localeHomePageID'])) {
            $localeHomePageID = 0;
            $activeLocale = RequestPage::getLocale();
            foreach ($rp->site->getLocales() as $locale) {
                if ($locale->getLocale() === $activeLocale) {
                    $localeHomePageID = $locale->getSiteTreeObject()->getSiteHomePageID();
                    break;
                }
            }

            $rp->cache['localeHomePageID'] = $localeHomePageID;
        }

        return $rp->cache['localeHomePageID'];
    }

    public static function getDisplaySiteName(): string
    {
        return tc('SiteName', self::getSiteName());
    }

    public static function getSiteName(): string
    {
        $rp = self::get();

        return $rp->cache['siteName'] ?? $rp->cache['siteName'] = $rp->site->getSiteName();
    }

    public static function getAttribute($ak, $mode = false)
    {
        $cacheKey = 'ak_';
        $cacheKey .= is_object($ak) ? $ak->getAttributeKeyHandle() : (string)$ak;
        $cacheKey .= $mode ? "_{$mode}" : '';

        $rp = self::get();

        return $rp->cache[$cacheKey] ?? $rp->cache[$cacheKey] = $rp->site->getAttribute($ak, $mode);
    }

    public function __call($name, $arguments)
    {
        return $this->site->$name(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return self::get()->site->$name(...$arguments);
    }

    /**
     * Gets a singleton instance of this class.
     *
     * @return Site
     */
    public static function get(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}