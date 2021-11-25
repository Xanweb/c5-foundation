<?php

namespace Xanweb\C5\Foundation\File\Import\Processor;

use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Attribute\Category\FileCategory;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\File\Version;
use Concrete\Core\File\Import\ImportingFile;
use Concrete\Core\File\Import\ImportOptions;
use Concrete\Core\File\Import\Processor\PostProcessorInterface;
use Concrete\Core\Logging\Channels;
use Concrete\Core\Logging\LoggerAwareInterface;
use Concrete\Core\Logging\LoggerAwareTrait;
use Concrete\Core\Support\Facade\Application;
use Illuminate\Support\Str;
use Imagine\Image\Metadata\DefaultMetadataReader;
use Imagine\Image\Metadata\MetadataBag;
use Imagine\Image\Metadata\MetadataReaderInterface;
use League\Flysystem\FileNotFoundException;
use Xanweb\C5\Foundation\Image\Metadata\Ifd0MetadataReader;
use Xanweb\C5\Foundation\Image\Metadata\IptcMetadataReader;

class DataExtractor implements PostProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected CategoryService $categoryService;
    private FileCategory $fakc;
    private MetadataReaderInterface $reader;
    private array $fieldsMapping;
    private string $data;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * {@inheritdoc}
     *
     * @see PostProcessorInterface::getPostProcessPriority()
     */
    public function getPostProcessPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @see PostProcessorInterface::shouldPostProcess()
     */
    public function shouldPostProcess(ImportingFile $file, ImportOptions $options, Version $importedVersion): bool
    {
        return (IptcMetadataReader::isSupported() || Ifd0MetadataReader::isSupported()) && $file->getFileType()->getName() === 'JPEG';
    }

    /**
     * {@inheritdoc}
     *
     * @see PostProcessorInterface::postProcess()
     */
    public function postProcess(ImportingFile $file, ImportOptions $options, Version $importedVersion): void
    {
        $metadataBag = $this->getMetadataBag($importedVersion);
        $category = $this->getFileAttributeKeyCategory();

        foreach ($this->fieldsMapping as $key => $field) {
            // Check if the field is defined
            if (empty($value = $metadataBag->get($key))) {
                continue;
            }

            $fields = is_array($field) ? $field : [$field];
            foreach ($fields as $_field) {
                if (\str_starts_with($_field, 'ak_')) {
                    $key = $category->getAttributeKeyByHandle(Str::substr($_field, 3));
                    if ($key !== null) {
                        $importedVersion->setAttribute($key, $value);
                    } else {
                        $this->logger->error(t('%s > Undefined attribute key `%s`.', __METHOD__, Str::substr($_field, 3)));
                    }
                } else {
                    if (method_exists($importedVersion, $method = 'update' . Str::ucfirst($_field))) {
                        $importedVersion->{$method}($value);
                    } else {
                        $this->logger->error(t('%s > Unknown property `%s`, unable to run `%s()` method.', __METHOD__, $_field, $method));
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see ProcessorInterface::readConfiguration()
     */
    public function readConfiguration(Repository $config)
    {
        $this->fieldsMapping = $config->get('xanweb.file_manager.images.data_extractor', [
            'image_title' => 'title',
            'comments' => 'description',
            'keywords' => 'tags',
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LoggerAwareInterface::getLoggerChannel()
     */
    public function getLoggerChannel(): string
    {
        return Channels::CHANNEL_FILES;
    }

    protected function getMetadataBag(Version $importedVersion): MetadataBag
    {
        $this->loadData($importedVersion);

        return $this->reader->readData($this->data);
    }

    /**
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected function getFileAttributeKeyCategory(): FileCategory
    {
        return $this->fakc ??= $this->categoryService->getByHandle('file')->getController();
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
                $urlOrAbsolutePath = $configuration->getRootPath() . $cf->prefix($importedVersion->getPrefix(), $importedVersion->getFileName());
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
}
