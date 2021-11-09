<?php

namespace Xanweb\C5\Foundation\File\Import\Processor;

use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\File\Import\ImportingFile;
use Concrete\Core\File\Import\ImportOptions;
use Concrete\Core\File\Import\Processor\PostProcessorInterface;
use Concrete\Core\File\StorageLocation\Configuration\LocalConfiguration;
use Concrete\Core\Support\Facade\Application;
use Imagine\Image\Metadata\DefaultMetadataReader;
use Imagine\Image\Metadata\ExifMetadataReader;
use Imagine\Image\Metadata\MetadataBag;
use Imagine\Image\Metadata\MetadataReaderInterface;
use Xanweb\C5\Foundation\Image\Metadata\Ifd0MetadataReader;
use Xanweb\C5\Foundation\Image\Metadata\IptcMetadataReader;

class IptcDataExtractor implements PostProcessorInterface
{

    private $data;

    private MetadataReaderInterface $reader;

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
        return (IptcMetadataReader::isSupported() ||
            Ifd0MetadataReader::isSupported()) &&
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



        if ($author = $metadataBag->get('author_title')) {
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
        $this->loadData($importedVersion);

        return $this->reader->readData($this->data);
    }

    private function loadData(Version $importedVersion)
    {
        if (isset($this->reader) && isset($this->data)) {
            return;
        }
        $info = [];
        $urlOrAbsolutePath = null;
        $configuration = null;
        $fsl = $importedVersion->getFile()->getFileStorageLocationObject();
        if ($fsl !== null) {
            $configuration = $fsl->getConfigurationObject();
        }

        $app = Application::getFacadeApplication();
        $cf = $app->make('helper/concrete/file');
        if ($configuration !== null) {
            if ($configuration->hasRelativePath()) {
                $root = $configuration->getRootPath();
                $path = $cf->prefix($importedVersion->getPrefix(), $importedVersion->getFileName());
                $urlOrAbsolutePath = $root . '/' . $path;
            } elseif ($configuration->hasPublicURL()) {
                $urlOrAbsolutePath = $configuration->getPublicURLToFile($cf->prefix($importedVersion->getPrefix(), $importedVersion->getFileName()));
            }
        }

        $size = false;
        if ($urlOrAbsolutePath !== null) {
            $size = getimagesize($urlOrAbsolutePath, $info);
        }

        if ($size !== false && isset($info['APP13']) && IptcMetadataReader::isSupported()) {
            $this->reader = new IptcMetadataReader();
            $this->data = $info['APP13'];
            return;
        }
        if (Ifd0MetadataReader::isSupported()) {
            $fr = $importedVersion->getFileResource();
            $this->reader = new Ifd0MetadataReader();
            $this->data = $fr->read();
            return;
        }

        $this->reader = new DefaultMetadataReader();
        $this->data = '';
    }
}