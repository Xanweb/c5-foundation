<?php

namespace Xanweb\C5\Foundation\Image\Metadata;

use Imagine\Exception\NotSupportedException;
use Imagine\Image\Metadata\AbstractMetadataReader;
use Imagine\Image\Metadata\MetadataBag;

class IptcMetadataReader extends AbstractMetadataReader
{
    private const IPTC_CODE_CHARACTER_SET = '1#090';
    private static bool $isSupported;

    /**
     * @throws NotSupportedException
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
    public static function getUnsupportedReason(): string
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
    public static function isSupported(): bool
    {
        return self::$isSupported ??= static::getUnsupportedReason() === '';
    }

    public function readData($data, $originalResource = null): MetadataBag
    {
        return new MetadataBag($this->extractFromData($data));
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\Metadata\AbstractMetadataReader::extractFromFile()
     */
    protected function extractFromFile($file): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\Metadata\AbstractMetadataReader::extractFromData()
     */
    protected function extractFromData($data): array
    {
        $iptc = iptcparse($data);
        $isUtf8Encoded = isset($iptc[self::IPTC_CODE_CHARACTER_SET]) && $iptc[self::IPTC_CODE_CHARACTER_SET][0] === "\x1b%G";

        $iptcData = [];
        foreach ($this->getIptcKeys() as $key => $name) {
            if (isset($iptc[$key])) {
                $value = $iptc[$key];
                if ($key === '2#120' && is_array($value)) {
                    $value = array_values($value)[0] ?? null;
                }
                $iptcData[$name] = ($isUtf8Encoded && is_string($value)) ? utf8_encode($value) : $value;
            }
        }

        return $iptcData;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\Metadata\AbstractMetadataReader::extractFromStream()
     */
    protected function extractFromStream($resource): array
    {
        return [];
    }

    /**
     * Get Supported Keys.
     *
     * @return array<string, string>
     */
    private function getIptcKeys(): array
    {
        return [
            '2#005' => 'image_title',
            '2#010' => 'urgency',
            '2#015' => 'category',
            '2#020' => 'subcategories',
            '2#040' => 'special_instructions',
            '2#055' => 'creation_date',
            '2#080' => 'artist',
            '2#085' => 'author_title',
            '2#090' => 'city',
            '2#095' => 'state',
            '2#101' => 'country',
            '2#103' => 'otr',
            '2#105' => 'headline',
            '2#110' => 'source',
            '2#115' => 'photo_source',
            '2#116' => 'copyright',
            '2#120' => 'comments',
            //'2#122' => 'comments_writer',
            '2#025' => 'keywords',
        ];
    }
}
