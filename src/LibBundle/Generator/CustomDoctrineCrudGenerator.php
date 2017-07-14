<?php

namespace LibBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\DoctrineCrudGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;

class CustomDoctrineCrudGenerator extends DoctrineCrudGenerator
{
    protected function getTwigEnvironment()
    {
        $twig = parent::getTwigEnvironment();
        $twig->addExtension(new FormExtension(new TwigRenderer(new TwigRendererEngine())));
        return $twig;
    }

    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $format, $routePrefix, $needWriteActions, $forceOverwrite)
    {
        $this->routePrefix = $routePrefix;
        $this->routeNamePrefix = self::getRouteNamePrefix($routePrefix);
        $this->actions = $needWriteActions ? array('index', 'show', 'new', 'edit', 'delete') : array('index', 'show');

        if (count($metadata->identifier) != 1) {
            throw new \RuntimeException('The CRUD generator does not support entity classes with multiple or no primary keys.');
        }

        $this->entity = $entity;
        $entity = str_replace('\\', '/', $entity);
        $entityParts = explode('/', $entity);
        $entityName = end($entityParts);
        $this->entitySingularized = lcfirst(Inflector::singularize($entityName));
        $this->entityPluralized = lcfirst(Inflector::pluralize($entityName));
        $this->bundle = $bundle;
        $this->metadata = $metadata;
        $this->setFormat($format);

        $this->generateControllerClass($forceOverwrite);

        $dir = sprintf('%s/Resources/views/%s', $this->rootDir, $this->routePrefix);

        if (!file_exists($dir)) {
            self::mkdir($dir);
        }

        $this->generateIndexView($dir);
        $this->generateGridView($dir);

        if (in_array('show', $this->actions)) {
            $this->generateShowView($dir);
        }

        if (in_array('new', $this->actions)) {
            $this->generateNewView($dir);
        }

        if (in_array('edit', $this->actions)) {
            $this->generateEditView($dir);
        }

        if (in_array('delete', $this->actions)) {
            $this->generateDeleteView($dir);
        }

        $this->generateTestClass();
        $this->generateConfiguration();
    }

    protected function getRecordActions()
    {
        return array_filter($this->actions, function ($item) {
            return in_array($item, array('show', 'edit', 'delete'));
        });
    }

    protected function generateDeleteView($dir)
    {
        $this->renderFile('crud/views/delete.html.twig.twig', $dir.'/delete.html.twig', array(
            'route_prefix' => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'identifier' => $this->metadata->identifier[0],
            'entity' => $this->entity,
            'entity_singularized' => $this->entitySingularized,
            'fields' => $this->metadata->fieldMappings,
            'bundle' => $this->bundle->getName(),
            'actions' => $this->actions,
        ));
    }

    protected function generateGridView($dir)
    {
        $fields = array();
        foreach ($this->metadata->fieldMappings as $field => $metadata) {
            if ($field !== $this->metadata->identifier[0]) {
                $fields[$field] = $metadata;
            }
        }

        $this->renderFile('crud/views/grid.html.twig.twig', $dir.'/grid.html.twig', array(
            'bundle' => $this->bundle->getName(),
            'entity' => $this->entity,
            'entity_pluralized' => $this->entityPluralized,
            'entity_singularized' => $this->entitySingularized,
            'identifier' => $this->metadata->identifier[0],
            'fields' => $fields,
            'actions' => $this->actions,
            'record_actions' => $this->getRecordActions(),
            'route_prefix' => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
        ));
    }
}
