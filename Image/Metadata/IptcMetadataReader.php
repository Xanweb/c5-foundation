<?php

namespace Xanweb\C5\Foundation\Image\Metadata;

use Imagine\Exception\NotSupportedException;
use Imagine\Image\Metadata\AbstractMetadataReader;
use Imagine\Image\Metadata\MetadataBag;
use Imagine\Utils\ErrorHandling;

class IptcMetadataReader extends AbstractMetadataReader
{
    /**
     * @throws \Imagine\Exception\NotSupportedException
     */
    public function __construct()
    {
        $whyNot = static::getUnsupportedReason();
        if ($whyNot !== '') {
            throw new NotSupportedException($whyNot);
        }
    }

    /**
     * Get the reason why this metadata reader is not supported.
     *
     * @return string empty string if the reader is available
     */
    public static function getUnsupportedReason()
    {
        if (!function_exists('iptcparse')) {
            return 'The PHP iptc extension is required to use the IptcMetadataReader';
        }

        return '';
    }

    /**
     * Is this metadata reader supported?
     *
     * @return bool
     */
    public static function isSupported()
    {
        return static::getUnsupportedReason() === '';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\Metadata\AbstractMetadataReader::extractFromFile()
     */
    protected function extractFromFile($file)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\Metadata\AbstractMetadataReader::extractFromData()
     */
    protected function extractFromData($data)
    {
        $iptc = iptcparse($data);
        $iptcData = [];
        foreach ($this->getIptcKeys() as $key => $name)
        {
            if (isset($iptc[$key])) {
                $iptcData[$name] = $iptc[$key];
            }
        }
        return $iptcData;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\Metadata\AbstractMetadataReader::extractFromStream()
     */
    protected function extractFromStream($resource)
    {
        return array();
    }

    public function readData($data, $originalResource = null)
    {
        return new MetadataBag($this->extractFromData($data));
    }

    private function getIptcKeys(): array
    {
        return [
            '2#005'=>'document_title',
            '2#010'=>'urgency',
            '2#015'=>'category',
            '2#020'=>'subcategories',
            '2#040'=>'special_instructions',
            '2#055'=>'creation_date',
            '2#080'=>'author_byline',
            '2#085'=>'author_title',
            '2#090'=>'city',
            '2#095'=>'state',
            '2#101'=>'country',
            '2#103'=>'otr',
            '2#105'=>'headline',
            '2#110'=>'source',
            '2#115'=>'photo_source',
            '2#116'=>'copyright',
            '2#120'=>'caption',
            '2#122'=>'caption_writer',
            '2#025'=>'keywords'
        ];
    }
}