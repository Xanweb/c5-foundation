<?php

namespace Xanweb\Foundation\Config;

use Concrete\Core\Localization\Localization;
use Punic\Data as PunicData;

class Site
{
    /**
     * Set default short date format for site.
     *
     * @param string $format Example 'dd.MM.y'
     * @param string $locale Example 'de_DE'
     *
     * @see https://punic.github.io/
     */
    public static function setDefaultDateFormat(string $format, string $locale = Localization::BASE_LOCALE): void
    {
        PunicData::setOverrides([
            'calendar' => [
                'dateFormats' => ['short' => $format]
            ]
        ], $locale);
    }
}