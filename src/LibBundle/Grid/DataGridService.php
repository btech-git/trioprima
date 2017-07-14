<?php

namespace LibBundle\Grid;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

class DataGridService
{
    private $dataGridView;
    private $dataGridReference;
    private $widgetsBuilder;
    private $paramsBuilder;
    private $dataBuilder;
    
    public function __construct(DataGridView $dataGridView, DataGridReference $dataGridReference, WidgetsBuilder $widgetsBuilder, ParamsBuilder $paramsBuilder, DataBuilder $dataBuilder)
    {
        $this->dataGridView = $dataGridView;
        $this->dataGridReference = $dataGridReference;
        $this->widgetsBuilder = $widgetsBuilder;
        $this->paramsBuilder = $paramsBuilder;
        $this->dataBuilder = $dataBuilder;
    }
    
    public function build($type, ObjectRepository $repository, Request $request, array $options = array())
    {
        if (!is_string($type)) {
            throw new UnexpectedTypeException($type, 'string');
        }
        
        $dataGridType = new $type;
        if (!is_subclass_of($dataGridType, DataGridType::class)) {
            throw new \Exception(sprintf('%s must be a subclass of %s.', $type, DataGridType::class));
        }
        
        $dataGridType->buildWidgets($this->widgetsBuilder, $options);
        $this->createGridTransformer($this->getObjectManager($repository));
        $this->assignGridDefault();
        $this->transformGridSearch();
        
        $this->paramsBuilder->processRequest($request);
        
        $dataGridType->buildData($this->dataBuilder, $repository, $options);
    }
    
    public function createView()
    {
        return $this->dataGridView;
    }
    
    public function getCount()
    {
        return $this->dataGridView->count;
    }
    
    public function getData()
    {
        return $this->dataGridView->data;
    }
    
    private function getObjectManager(ObjectRepository $repository)
    {
        $methodName = '';
        if ($repository instanceof \Doctrine\ORM\EntityRepository) {
            $methodName = 'getEntityManager';
        }
        
        $refMethod = new \ReflectionMethod(get_class($repository), $methodName);
        $refMethod->setAccessible(true);
        
        return $refMethod->invoke($repository);
    }
    
    private function getFieldType(ObjectManager $om, $entityClass, $fieldName)
    {
        $fieldType = $om->getClassMetadata($entityClass)->getTypeOfField($fieldName);
        if ($om instanceof \Doctrine\ORM\EntityManager) {
            if ($fieldType === 'smallint') {
                $fieldType = 'integer';
            }
        }
        
        return $fieldType;
    }
    
    private function getDataTransformer($fieldType, &$list)
    {
        if (isset($list[$fieldType])) {
            $dataTransformer = $list[$fieldType];
        } else {
            switch ($fieldType) {
                case 'integer':
                    $dataTransformer = new Transformer\IntegerTransformer();
                    break;
                case 'date':
                    $dataTransformer = new Transformer\DateTimeTransformer('Y-m-d');
                    break;
                case 'time':
                    $dataTransformer = new Transformer\DateTimeTransformer('H:i:s');
                    break;
                case 'datetime':
                    $dataTransformer = new Transformer\DateTimeTransformer('Y-m-d H:i:s');
                    break;
                case 'boolean':
                    $dataTransformer = new Transformer\BooleanTransformer();
                    break;
                default:
                    $dataTransformer = null;
            }
            $list[$fieldType] = $dataTransformer;
        }
        
        return $dataTransformer;
    }
    
    private function createGridTransformer(ObjectManager $om)
    {
        $list = array();
        $searchItems = $this->dataGridView->searchWidget;
        foreach ($searchItems['groups'] as $groupName => $groupItems) {
            $entityClass = $groupItems['entity'];
            foreach ($groupItems['fields'] as $fieldName => $fieldItems) {
                if ($entityClass !== null) {
                    $searchFieldReferences = $this->dataGridReference->getSearchFieldReferences($groupName, $fieldName);
                    $dataTransformers = $this->dataGridReference->getDataTransformers($groupName, $fieldName);
                    foreach ($searchFieldReferences as $i => $searchFieldReference) {
                        if (!isset($dataTransformers[$i])) {
                            $fieldType = $this->getFieldType($om, $entityClass, $searchFieldReference);
                            $dataTransformers[$i] = $this->getDataTransformer($fieldType, $list);
                        }
                    }
                    $this->dataGridReference->setDataTransformers($groupName, $fieldName, $dataTransformers);
                }
            }
        }
    }
    
    private function assignGridDefault()
    {
        foreach ($this->dataGridView->searchVals as $groupName => &$items) {
            foreach ($items as $fieldName => &$values) {
                $defaultValues = $this->dataGridReference->getSearchDefaultValues($groupName, $fieldName);
                if ($defaultValues !== false) {
                    $operators = $this->dataGridView->searchWidget['groups'][$groupName]['fields'][$fieldName]['operators'];
                    $operatorNames = array_keys($operators);
                    if (($valid = in_array($defaultValues[0], $operatorNames, true))) {
                        $list = $operators[$defaultValues[0]]['list'];
                        $count = count($list);
                        foreach ($defaultValues as $i => $defaultValue) {
                            if ($i > 0) {
                                $choices = $list[$i - 1]['choices'];
                                $valid = $valid && (empty($choices) || in_array($defaultValue, $choices, true));
                            }
                        }
                    }
                    if ($valid) {
                        $values = $defaultValues;
                    }
                }
            }
        }
        foreach ($this->dataGridView->sortVals as $groupName => &$items) {
            foreach ($items as $fieldName => &$value) {
                $defaultValues = $this->dataGridReference->getSortDefaultValues($groupName, $fieldName);
                if ($defaultValues !== false) {
                    $operatorNames = array_keys($this->dataGridView->sortWidget['groups'][$groupName]['fields'][$fieldName]['operators']);
                    if (in_array($defaultValues[0], $operatorNames, true)) {
                        $value = $defaultValues[0];
                    }
                }
            }
        }
    }
    
    private function transformGridSearch()
    {
        $searchItems = &$this->dataGridView->searchWidget;
        foreach ($searchItems['groups'] as $groupName => &$groupItems) {
            foreach ($groupItems['fields'] as $fieldName => &$fieldItems) {
                $dataTransformers = $this->dataGridReference->getDataTransformers($groupName, $fieldName);
                foreach ($fieldItems['operators'] as $operatorName => &$operatorItems) {
                    $num = $operatorName::getNumberOfInput();
                    foreach ($operatorItems['list'] as $i => &$list) {
                        $index = intval($i / $num);
                        if (isset($dataTransformers[$index])) {
                            if ($list['choices'] !== null) {
                                array_walk($list['choices'], array($this->dataGridReference, 'toView'), $dataTransformers[$index]);
                            }
                            $list['value'] = $dataTransformers[$index]->toView($list['value']);
                        }
                    }
                }
            }
        }
        foreach ($this->dataGridView->searchVals as $groupName => &$items) {
            foreach ($items as $fieldName => &$values) {
                $dataTransformers = $this->dataGridReference->getDataTransformers($groupName, $fieldName);
                $operatorName = $values[0];
                $num = $operatorName::getNumberOfInput();
                foreach ($values as $i => &$value) {
                    if ($i > 0) {
                        $index = intval(($i - 1) / $num);
                        if (isset($dataTransformers[$index])) {
                            $value = $dataTransformers[$index]->toView($value);
                        }
                    }
                }
            }
        }
    }
}
