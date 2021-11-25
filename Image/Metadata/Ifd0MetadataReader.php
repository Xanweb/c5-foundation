<?php

namespace Xanweb\C5\Foundation\Image\Metadata;

use Imagine\Exception\NotSupportedException;
use Imagine\Image\Metadata\AbstractMetadataReader;
use Imagine\Image\Metadata\MetadataBag;

class Ifd0MetadataReader extends AbstractMetadataReader
{
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
        if (!function_exists('exif_read_data')) {
            return 'The PHP EXIF extension is required to use the ExifMetadataReader';
        }

        if (!in_array('data', stream_get_wrappers(), true)) {
            return 'The data:// stream wrapper must be enabled';
        }

        if (in_array(ini_get('allow_url_fopen'), ['', '0', 0], true)) {
            return 'The allow_url_fopen php.ini configuration key must be set to 1';
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

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\Metadata\AbstractMetadataReader::readData()
     */
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
        $ifd0Data = [];
        $metadata = $this->doReadData($data);
        foreach ($this->getKeys() as $key => $name) {
            if (isset($metadata[$key])) {
                $val = trim($metadata[$key]);
                $ifd0Data[$name] = mb_convert_encoding($val, 'UTF-8', mb_detect_encoding($val, 'auto'));
            }
        }

        return $ifd0Data;
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
    private function getKeys(): array
    {
        return [
            'Title' => 'document_title',
            'Artist' => 'author_byline',
            'Author' => 'author_title',
            'Keywords' => 'keywords',
            'ImageDescription' => 'image_description',
            'Comments' => 'caption',
            'Copyright' => 'copyright',
        ];
    }

    /**
     * Extracts metadata from raw data, merges with existing metadata.
     *
     * @param string $data
     *
     * @return array
     */
    private function doReadData(string $data): array
    {
        if (strpos($data, 'II') === 0) {
            $mime = 'image/tiff';
        } else {
            $mime = 'image/jpeg';
        }

        return $this->extract("data://$mime;base64," . base64_encode($data));
    }

    /**
     * Performs the exif data extraction given a path or data-URI representation.
     *
     * @param string $path the path to the file or the data-URI representation
     *
     * @return array
     */
    private function extract(string $path): array
    {
        try {
            $metadata = exif_read_data($path, 'IFD0', true);
        } catch (\Throwable $e) {
            $metadata = false;
        }

        return $metadata ? $metadata['IFD0'] : [];
    }
}
