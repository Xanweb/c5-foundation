<?php

namespace Xanweb\C5\Foundation\File\Import\Processor;

use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Attribute\Category\FileCategory;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\File\Import\ImportingFile;
use Concrete\Core\File\Import\ImportOptions;
use Concrete\Core\File\Import\Processor\PostProcessorInterface;
use Concrete\Core\Support\Facade\Application;
use Imagine\Image\Metadata\DefaultMetadataReader;
use Imagine\Image\Metadata\MetadataBag;
use Imagine\Image\Metadata\MetadataReaderInterface;
use League\Flysystem\FileNotFoundException;
use Xanweb\C5\Foundation\Image\Metadata\Ifd0MetadataReader;
use Xanweb\C5\Foundation\Image\Metadata\IptcMetadataReader;

class IptcDataExtractor implements PostProcessorInterface
{
    protected CategoryService $categoryService;
    private MetadataReaderInterface $reader;
    private FileCategory $fakc;
    private string $data;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * {@inheritDoc}
     *
     * @see PostProcessorInterface::getPostProcessPriority()
     */
    public function getPostProcessPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     *
     * @see PostProcessorInterface::shouldPostProcess()
     */
    public function shouldPostProcess(ImportingFile $file, ImportOptions $options, Version $importedVersion): bool
    {
        return (IptcMetadataReader::isSupported() || Ifd0MetadataReader::isSupported()) && $file->getFileType()->getName() === 'JPEG';
    }

    /**
     * {@inheritDoc}
     *
     * @see PostProcessorInterface::postProcess()
     */
    public function postProcess(ImportingFile $file, ImportOptions $options, Version $importedVersion): void
    {
        $metadataBag = $this->getMetadataBag($importedVersion);
        $category = $this->getFileAttributeKeyCategory();

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

    /**
     * {@inheritDoc}
     *
     * @see ProcessorInterface::readConfiguration()
     */
    public function readConfiguration(Repository $config)
    {
        return $this;
    }

    protected function getMetadataBag(Version $importedVersion): MetadataBag
    {
        $this->loadData($importedVersion);

        return $this->reader->readData($this->data);
    }

    private function loadData(Version $importedVersion): void
    {
        if (isset($this->reader, $this->data)) {
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
            try {
                $fr = $importedVersion->getFileResource();
                $this->reader = new Ifd0MetadataReader();
                $this->data = $fr->read();
                return;
            } catch (FileNotFoundException $e) {
            }
        }

        $this->reader = new DefaultMetadataReader();
        $this->data = '';
    }

    /**
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected function getFileAttributeKeyCategory(): FileCategory
    {
        return $this->fakc ??= $this->categoryService->getByHandle('file')->getController();
    }
}