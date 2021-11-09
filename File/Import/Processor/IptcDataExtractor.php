<?php

namespace Xanweb\C5\Foundation\File\Import\Processor;

use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\File\Import\ImportingFile;
use Concrete\Core\File\Import\ImportOptions;
use Concrete\Core\File\Import\Processor\PostProcessorInterface;
use Imagine\Image\Metadata\MetadataBag;
use Xanweb\C5\Foundation\Image\Metadata\IptcMetadataReader;

class IptcDataExtractor implements PostProcessorInterface
{

    /**
     * @var CategoryService
     */
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function getPostProcessPriority()
    {
        return 0;
    }

    public function shouldPostProcess(ImportingFile $file, ImportOptions $options, Version $importedVersion)
    {
        return IptcMetadataReader::isSupported() &&
            $file->getFileType()->getName() === 'JPEG';
    }

    public function postProcess(ImportingFile $file, ImportOptions $options, Version $importedVersion)
    {
        $metadataBag = $this->getMetadataBag($importedVersion);

        $categoryEntity = $this->categoryService->getByHandle('file');
        $category = $categoryEntity->getController();

        if ($title = $metadataBag->get('document_title')) {
            $importedVersion->updateTitle($title);
            $key = $category->getAttributeKeyByHandle('alt');
            if (is_object($key)) {
                $importedVersion->setAttribute($key, $title);
            }
        }

        if ($caption = $metadataBag->get('caption')) {
            $importedVersion->updateDescription($caption);
        }

        if ($keywords = $metadataBag->get('keywords')) {
            $importedVersion->updateTags($keywords);
        }



        if ($author = $metadataBag->get('author_byline')) {
            $key = $category->getAttributeKeyByHandle('author');
            if (is_object($key)) {
                $importedVersion->setAttribute($key, $author);
            }
        }

        if ($copyright = $metadataBag->get('copyright')) {
            $key = $category->getAttributeKeyByHandle('copyright');
            if (is_object($key)) {
                $importedVersion->setAttribute($key, $copyright);
            }
        }

    }

    public function readConfiguration(Repository $config)
    {
        return $this;
    }

    protected function getMetadataBag(Version $importedVersion)
    {
        $size = getimagesize(DIR_BASE . $importedVersion->getRelativePath(), $info);

        if (false == $size || !isset($info['APP13'])) {
            return new MetadataBag();
        }

        $metadataReader = new IptcMetadataReader();

        return $metadataReader->readData($info['APP13']);
    }
}