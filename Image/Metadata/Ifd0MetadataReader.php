<?php

namespace Xanweb\C5\Foundation\Image\Metadata;

use Imagine\Exception\NotSupportedException;
use Imagine\Image\Metadata\AbstractMetadataReader;
use Imagine\Image\Metadata\MetadataBag;
use Imagine\Utils\ErrorHandling;

class Ifd0MetadataReader extends AbstractMetadataReader
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
        if (!function_exists('exif_read_data')) {
            return 'The PHP EXIF extension is required to use the ExifMetadataReader';
        }
        if (!in_array('data', stream_get_wrappers(), true)) {
            return 'The data:// stream wrapper must be enabled';
        }
        if (in_array(ini_get('allow_url_fopen'), array('', '0', 0), true)) {
            return 'The allow_url_fopen php.ini configuration key must be set to 1';
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
        $metadata = $this->doReadData($data);
        $ifd0Data = [];
        foreach ($this->getKeys() as $key => $name)
        {
            if (isset($metadata[$key])) {
                $ifd0Data[$name] = $metadata[$key];
            }
        }
        return $ifd0Data;
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
    private function doReadData($data)
    {
        if (substr($data, 0, 2) === 'II') {
            $mime = 'image/tiff';
        } else {
            $mime = 'image/jpeg';
        }

        return $this->extract('data://' . $mime . ';base64,' . base64_encode($data));
    }

    /**
     * Performs the exif data extraction given a path or data-URI representation.
     *
     * @param string $path the path to the file or the data-URI representation
     *
     * @return array
     */
    private function extract($path)
    {
        try {
            $metadata = exif_read_data($path,  null, true);
        } catch (\Exception $e) {
            $metadata = false;
        } catch (\Throwable $e) {
            $metadata = false;
        }
        if (!is_array($metadata) || !isset($metadata['IFD0'])) {
            return array();
        }

        return $metadata['IFD0'];
    }
}