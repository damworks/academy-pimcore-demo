<?php

namespace App\Mapping\Operator\Simple;

use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
use Pimcore\Bundle\DataImporterBundle\Mapping\Operator\AbstractOperator;
use Pimcore\Bundle\DataImporterBundle\Mapping\Type\TransformationDataTypeService;
use Pimcore\Db;
use Pimcore\File;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Folder;

/**
 * Load or create related articles from comma-separated string or single value.
 * Handles: "OW-B023", "OW-B023,OW-B026", etc.
 * Returns: Array of DataObjects for manyToManyRelation
 */
class LoadOrCreateRelatedArticles extends AbstractOperator
{
    protected ?string $delimiter = ',';
    protected bool $createIfMissing = true;
    protected string $folderPath = '/prdOway';
    protected string $attributeName = 'codiceArt';
    protected string $className = 'PrdOway';
    protected bool $loadUnpublished = true;

    // Campi obbligatori per pubblicare un prodotto
    protected array $requiredFieldsForPublish = [
        'codiceArt',
        'descrizione',
        'gruppoArticoli'
    ];

    public function setSettings(array $settings): void
    {
        $this->delimiter = $settings['delimiter'] ?? ',';
        $this->createIfMissing = $settings['createIfMissing'] ?? true;
        $this->folderPath = $settings['folderPath'] ?? '/prdOway';
        $this->attributeName = $settings['attributeName'] ?? 'codiceArt';
        $this->className = $settings['className'] ?? 'PrdOway';
        $this->loadUnpublished = $settings['loadUnpublished'] ?? true;
        $this->requiredFieldsForPublish = $settings['requiredFieldsForPublish'] ?? [
            'codiceArt',
            'descrizione',
            'gruppoArticoli'
        ];
    }

    public function process($inputData, bool $dryRun = false)
    {
        // Gestisci input singolo o array
        if (!is_array($inputData)) {
            $inputData = [$inputData];
        }

        $result = [];

        foreach ($inputData as $data) {
            if (empty($data)) {
                continue;
            }

            // Split sulla virgola
            $codes = array_map('trim', explode($this->delimiter, $data));

            foreach ($codes as $code) {
                if (empty($code)) {
                    continue;
                }

                $object = $this->loadOrCreateArticle($code, $dryRun);
                if ($object instanceof DataObject) {
                    $result[] = $object;
                }
            }
        }

        return $result;
    }

    public function evaluateReturnType(string $inputType, int $index = null): string
    {
        // Restituisce sempre DATA_OBJECT_ARRAY per manyToMany
        return TransformationDataTypeService::DATA_OBJECT_ARRAY;
    }

    private function loadOrCreateArticle(string $code, bool $dryRun): ?DataObject
    {
        if (empty($code)) {
            return null;
        }

        $object = $this->loadExistingArticle($code);

        if ($object instanceof DataObject) {
            return $object;
        }

        if (!$this->createIfMissing || $dryRun) {
            return null;
        }

        return $this->createArticle($code);
    }

    private function loadExistingArticle(string $code): ?DataObject
    {
        try {
            $class = ClassDefinition::getById(ucfirst($this->className));
            if (empty($class)) {
                return null;
            }

            $className = '\\Pimcore\\Model\\DataObject\\' . ucfirst($class->getName());

            $prevHideUnpublished = DataObject::getHideUnpublished();
            if ($this->loadUnpublished) {
                DataObject::setHideUnpublished(false);
            }

            // Query diretta con Pimcore API
            $list = $className::getList([
                'condition' => $this->attributeName . ' = ' . \Pimcore\Db::get()->quote($code),
                'limit' => 1
            ]);

            $objects = $list->load();

            if ($this->loadUnpublished) {
                DataObject::setHideUnpublished($prevHideUnpublished);
            }

            return !empty($objects) ? $objects[0] : null;
        } catch (\Exception $e) {
            $this->applicationLogger->error(
                "Failed to load article '{$code}': {$e->getMessage()}",
                ['component' => 'DataImporter']
            );
            return null;
        }
    }

    private function createArticle(string $code): ?DataObject
    {
        try {
            $folder = $this->findOrCreateFolder($this->folderPath);
            if (!$folder) {
                return null;
            }

            $class = ClassDefinition::getById(ucfirst($this->className));
            if (empty($class)) {
                return null;
            }

            $className = '\\Pimcore\\Model\\DataObject\\' . ucfirst($class->getName());

            $object = new $className();
            $object->setParentId($folder->getId());
            $object->setKey(File::getValidFilename($code));

            $setter = 'set' . ucfirst($this->attributeName);
            if (method_exists($object, $setter)) {
                $object->$setter($code);
            }

            $canPublish = $this->hasRequiredFields($object);

            $object->setPublished($canPublish);
            $object->save();

            if (!$canPublish) {
                $this->applicationLogger->info(
                    "Created article '{$code}' as UNPUBLISHED (missing required fields)",
                    ['component' => 'DataImporter']
                );
            }

            return $object;
        } catch (\Exception $e) {
            $this->applicationLogger->error(
                "Failed to create article '{$code}': {$e->getMessage()}",
                ['component' => 'DataImporter']
            );
            return null;
        }
    }

    /**
     * Verifica se l'oggetto ha tutti i campi obbligatori valorizzati
     */
    private function hasRequiredFields(DataObject $object): bool
    {
        foreach ($this->requiredFieldsForPublish as $fieldName) {
            $getter = 'get' . ucfirst($fieldName);

            if (!method_exists($object, $getter)) {
                continue;
            }

            $value = $object->$getter();

            // Se il campo è vuoto, non può essere pubblicato
            if (empty($value)) {
                return false;
            }
        }

        return true;
    }
    private function findOrCreateFolder(string $path): ?DataObject
    {
        $path = ltrim($path, '/');

        if (empty($path)) {
            return DataObject::getById(1);
        }

        $existingFolder = DataObject::getByPath('/' . $path);
        if ($existingFolder instanceof Folder) {
            return $existingFolder;
        }

        $parts = explode('/', $path);
        $currentPath = '';
        $parentId = 1;

        foreach ($parts as $part) {
            $currentPath .= '/' . $part;
            $folder = DataObject::getByPath($currentPath);

            if (!$folder instanceof Folder) {
                $newFolder = new Folder();
                $newFolder->setParentId($parentId);
                $newFolder->setKey(File::getValidFilename($part));
                $newFolder->setPublished(true);
                $newFolder->save();
                $parentId = $newFolder->getId();
            } else {
                $parentId = $folder->getId();
            }
        }

        return DataObject::getByPath('/' . $path);
    }

}
