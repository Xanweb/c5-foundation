<?php
namespace Xanweb\Foundation\Request;

use Concrete\Core\Page\Page as ConcretePage;
use Xanweb\Foundation\Request\Page as RequestPage;
use Xanweb\Foundation\Request\Traits\AttributesTrait;
use Xanweb\Foundation\Traits\SingletonTrait;

class Site
{
    use SingletonTrait;
    use AttributesTrait;

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
        $this->site = c5app('site/active');
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
        $rs = self::get();

        return $rs->cache['siteHomePageID'] ?? $rs->cache['siteHomePageID'] = $rs->site->getSiteHomePageID();
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
        $rs = self::get();
        if (!isset($rs->cache['localeHomePageID'])) {
            $localeHomePageID = 0;
            $activeLocale = RequestPage::getLocale();
            foreach ($rs->site->getLocales() as $locale) {
                if ($locale->getLocale() === $activeLocale) {
                    $localeHomePageID = $locale->getSiteTreeObject()->getSiteHomePageID();
                    break;
                }
            }

            $rs->cache['localeHomePageID'] = $localeHomePageID;
        }

        return $rs->cache['localeHomePageID'];
    }

    public static function getDisplaySiteName(): string
    {
        return tc('SiteName', self::getSiteName());
    }

    public static function getSiteName(): string
    {
        $rs = self::get();

        return $rs->cache['siteName'] ?? $rs->cache['siteName'] = $rs->site->getSiteName();
    }

    public static function getAttribute($ak, $mode = false)
    {
        $rs = self::get();
        if ($rs->site === null) {
            return null;
        }

        return self::_getAttribute($rs->site, $ak, $mode);
    }

    public function __call($name, $arguments)
    {
        return $this->site->$name(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return self::get()->site->$name(...$arguments);
    }
}