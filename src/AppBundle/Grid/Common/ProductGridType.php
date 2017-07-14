<?php

namespace AppBundle\Grid\Common;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectRepository;
use LibBundle\Grid\DataGridType;
use LibBundle\Grid\WidgetsBuilder;
use LibBundle\Grid\DataBuilder;
use LibBundle\Grid\SortOperator\BlankType as SortBlankType;
use LibBundle\Grid\SortOperator\AscendingType;
use LibBundle\Grid\SortOperator\DescendingType;
use LibBundle\Grid\SearchOperator\EqualNonEmptyType;
use LibBundle\Grid\SearchOperator\ContainNonEmptyType;
use LibBundle\Grid\Transformer\EntityTransformer;
use AppBundle\Entity\Master\Brand;
use AppBundle\Entity\Master\ProductCategory;
use AppBundle\Entity\Master\Product;

class ProductGridType extends DataGridType
{
    /**
     * @param WidgetsBuilder $builder
     * @param array $options
     */
    public function buildWidgets(WidgetsBuilder $builder, array $options)
    {
        $em = $options['em'];
        $brands = $em->getRepository(Brand::class)->findAll();
        $productCategories = $em->getRepository(ProductCategory::class)->findAll();
        $brandLabelModifier = function($brand) { return $brand->getName(); };
        $productCategoryLabelModifier = function($productCategory) { return $productCategory->getName(); };

        $builder->searchWidget()
            ->addGroup('product')
                ->setEntityName(Product::class)
                ->addField('code')
                    ->addOperator(ContainNonEmptyType::class)
                ->addField('name')
                    ->addOperator(ContainNonEmptyType::class)
                ->addField('size')
                    ->addOperator(EqualNonEmptyType::class)
                ->addField('brand')
                    ->setDataTransformer(new EntityTransformer($em, Brand::class))
                    ->addOperator(EqualNonEmptyType::class)
                        ->getInput(1)
                            ->setListData($brands, $brandLabelModifier)
                ->addField('productCategory')
                    ->setDataTransformer(new EntityTransformer($em, ProductCategory::class))
                    ->addOperator(EqualNonEmptyType::class)
                        ->getInput(1)
                            ->setListData($productCategories, $productCategoryLabelModifier)
        ;

        $builder->sortWidget()
            ->addGroup('product')
                ->addField('code')
                    ->addOperator(SortBlankType::class)
                    ->addOperator(AscendingType::class)
                    ->addOperator(DescendingType::class)
                ->addField('name')
                    ->addOperator(SortBlankType::class)
                    ->addOperator(AscendingType::class)
                    ->addOperator(DescendingType::class)
                ->addField('size')
                    ->addOperator(SortBlankType::class)
                    ->addOperator(AscendingType::class)
                    ->addOperator(DescendingType::class)
        ;

        $builder->pageWidget()
            ->addPageSizeField()
                ->addItems(10, 25, 50, 100)
            ->addPageNumField()
        ;
    }

    /**
     * @param DataBuilder $builder
     * @param ObjectRepository $repository
     * @param array $options
     */
    public function buildData(DataBuilder $builder, ObjectRepository $repository, array $options)
    {
        $criteria = Criteria::create();

        $builder->processSearch(function($values, $operator, $field) use ($criteria) {
            $operator::search($criteria, $field, $values);
        });

        $builder->processSort(function($operator, $field) use ($criteria) {
            $operator::sort($criteria, $field);
        });

        $builder->processPage($repository->count($criteria), function($offset, $size) use ($criteria) {
            $criteria->setMaxResults($size);
            $criteria->setFirstResult($offset);
        });
        
        $objects = $repository->match($criteria);

        $builder->setData($objects);
    }
}
