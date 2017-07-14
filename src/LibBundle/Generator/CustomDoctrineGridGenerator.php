<?php

namespace LibBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class CustomDoctrineGridGenerator extends Generator
{
    private $filesystem;
    private $className;
    private $classPath;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getClassPath()
    {
        return $this->classPath;
    }

    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $forceOverwrite = false)
    {
        $parts = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $this->className = $entityClass.'GridType';
        $dirPath = $bundle->getPath().'/Grid';
        $this->classPath = $dirPath.'/'.str_replace('\\', '/', $entity).'GridType.php';

        if (!$forceOverwrite && file_exists($this->classPath)) {
            throw new \RuntimeException(sprintf('Unable to generate the %s grid class as it already exists under the %s file', $this->className, $this->classPath));
        }

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The grid generator does not support entity classes with multiple primary keys.');
        }

        $parts = explode('\\', $entity);
        array_pop($parts);

        $fieldTypes = array();
        foreach ($metadata->fieldMappings as $items) {
            if (isset($items['type'])) {
                $fieldTypes[] = $items['type'];
            }
        }

        $this->renderFile('grid/GridType.php.twig', $this->classPath, array(
            'fields' => $this->getFieldsFromMetadata($metadata),
            'fields_mapping' => $metadata->fieldMappings,
            'fields_type' => $fieldTypes,
            'namespace' => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'entity_class' => $entityClass,
            'entity' => lcfirst($entityClass),
            'bundle' => $bundle->getName(),
            'grid_class' => $this->className,
            'grid_type_name' => strtolower(str_replace('\\', '_', $bundle->getNamespace()).($parts ? '_' : '').implode('_', $parts).'_'.substr($this->className, 0, -4)),
        ));
    }

    private function getFieldsFromMetadata(ClassMetadataInfo $metadata)
    {
        $fields = (array) $metadata->fieldNames;

        return array_diff($fields, $metadata->identifier);
    }
}
