<?php

namespace Xanweb\Foundation\Config;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JavascriptAssetDefaults extends Collection
{
    public function __construct()
    {
        parent::__construct(['i18n' => []]);
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        $array = $this->jsonSerialize();

        $placeholders = [];
        array_walk_recursive($array, static function(&$value) use (&$placeholders) {
            // We don't want to encode passed js functions
            // So we will set placeholders before encoding to restore them after that.
            if (\str_starts_with(\str_replace(' ', '', $value), 'function')) {
                $placeholders[$placeholder = Str::quickRandom(8)] = $value;

                $value = $placeholder;
            }
        });

        $encoded = json_encode($array, $options);

        if (empty($placeholders)) {
            return $encoded;
        }

        // Restore js functions as they are
        return str_replace(array_keys($placeholders), array_values($placeholders), $encoded);
    }
}