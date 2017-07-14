<?php

namespace LibBundle\Doctrine;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\CountOutputWalker;
use Doctrine\ORM\Tools\Pagination\LimitSubqueryOutputWalker;
use Doctrine\ORM\Tools\Pagination\WhereInWalker;

class EntityRepositoryReader implements RepositoryReaderInterface
{
    private $repository;
    private $criteria = null;
    private $associations = array();

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function setCriteria(Criteria $criteria)
    {
        $this->criteria = $criteria;
    }

    public function setAssociations(array $associations)
    {
        $this->associations = $associations;
    }

    public function match()
    {
        list($qb, $params, $associations, $entityName, $entityAlias) = $this->initialize();

        $this->indexing($associations);

        if (empty($this->associations)) {
            $this->traverseSearch($qb, $params, $associations);
            $this->traverseSort($qb, $associations);
            $this->traversePage($qb, $this->criteria);

            return $qb->getQuery()->getResult();
        } else {
            $this->traverseSearch($qb, $params, $associations);
            $this->traversePage($qb, $this->criteria);
            $this->traverseSort($qb, $associations, $this->orderSubqueryList($associations, $entityName));
            $qb->addOrderBy($entityAlias . '.id', Criteria::ASC);
            $subQuery = $this->cloneQuery($qb->getQuery());
            $subQuery->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, LimitSubqueryOutputWalker::class);

            $ids = array_map('intval', array_map('current', $subQuery->getScalarResult()));
            if (count($ids) === 0) {
                return array();
            } else {
                $qb->resetDQLPart('orderBy');

                $this->traverseSort($qb, $associations);
                $whereInQuery = $this->cloneQuery($qb->getQuery());
                $whereInQuery->setHint(Query::HINT_CUSTOM_TREE_WALKERS, array(WhereInWalker::class));
                $whereInQuery->setHint(WhereInWalker::HINT_PAGINATOR_ID_COUNT, count($ids));
                $whereInQuery->setParameter(WhereInWalker::PAGINATOR_ID_ALIAS, $ids);
                $whereInQuery->setFirstResult(null);
                $whereInQuery->setMaxResults(null);

                return $whereInQuery->getResult();
            }
        }
    }

    public function count()
    {
        list($qb, $params, $associations) = $this->initialize();

        $this->indexing($associations);

        $this->traverseSearch($qb, $params, $associations);
        $countQuery = $qb->getQuery();
        $countQuery->setHint(CountWalker::HINT_DISTINCT, true);

        if (empty($this->associations)) {
            $countQuery->setHint(Query::HINT_CUSTOM_TREE_WALKERS, array(CountWalker::class));
        } else {
            $platform = $qb->getEntityManager()->getConnection()->getDatabasePlatform();
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult($platform->getSQLResultCasing('dctrn_count'), 'count');
            $countQuery->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, CountOutputWalker::class);
            $countQuery->setResultSetMapping($rsm);
        }

        return $countQuery->getSingleScalarResult();
    }

    private function initialize()
    {
        $entityName = lcfirst((new \ReflectionClass($this->repository->getClassName()))->getShortName());
        $entityAlias = $entityName . 0;
        $associations = array($entityName => array('criteria' => $this->criteria, 'associations' => $this->associations));
        $qb = $this->repository->createQueryBuilder($entityAlias);
        $params = $qb->getParameters();

        return array($qb, $params, $associations, $entityName, $entityAlias);
    }

    private function indexing(&$associations, $index = 0)
    {
        foreach ($associations as &$item) {
            $item['index'] = $index;
            if (!empty($item['associations'])) {
                $this->indexing($item['associations'], ++$index);
            }
        }
    }

    private function traverseSearch($qb, $params, $associations, $list = null)
    {
        if ($list === null) {
            $list = array();
            $this->searchFilter($associations, null, null, $list);
        }
        foreach ($list as $items) {
            list($index, $name, $criteria, $merge, $parentIndex, $parentName) = $items;
            $alias = $name . $index;
            $parentAlias = ($index === 0) ? null : $parentName . $parentIndex;
            $visitor = new QueryExpressionVisitor($alias);
            if ($parentAlias === null) {
                if ($criteria !== null) {
                    if (($whereExpression = $criteria->getWhereExpression())) {
                        $qb->andWhere($visitor->dispatch($whereExpression));
                        foreach ($visitor->getParameters() as $parameter) {
                            $params->add($parameter);
                        }
                    }
                }
            } else {
                $join = empty($merge) ? 'leftJoin' : 'innerJoin';
                if ($criteria !== null) {
                    if (($whereExpression = $criteria->getWhereExpression())) {
                        $qb->$join($parentAlias . '.' . $name, $alias, Join::WITH, $visitor->dispatch($whereExpression));
                        foreach ($visitor->getParameters() as $parameter) {
                            $params->add($parameter);
                        }
                    } else {
                        $qb->$join($parentAlias . '.' . $name, $alias);
                    }
                } else {
                    $qb->$join($parentAlias . '.' . $name, $alias);
                }
                $qb->addSelect($alias);
            }
        }
    }

    private function traverseSort($qb, $associations, $list = null)
    {
        if ($list === null) {
            $list = array();
            $this->sortOrder($associations, $list);
            ksort($list);
        }
        foreach ($list as $rows) {
            foreach ($rows as $items) {
                list($index, $name, $criteria) = $items;
                $alias = $name . $index;
                if ($criteria !== null) {
                    if (($orderings = $criteria->getOrderings())) {
                        foreach ($orderings as $field => $order) {
                            $qb->addOrderBy($alias . '.' . $field, $order);
                        }
                    }
                }
            }
        }
    }

    private function traversePage($qb, $criteria)
    {
        if ($criteria !== null) {
            if (($firstResult = $criteria->getFirstResult()) !== null) {
                $qb->setFirstResult($firstResult);
            }
            if (($maxResults = $criteria->getMaxResults()) !== null) {
                $qb->setMaxResults($maxResults);
            }
        }
    }

    private function searchFilter($associations, $parentIndex, $parentName, &$list)
    {
        foreach ($associations as $name => $item) {
            $index = isset($item['index']) ? $item['index'] : 0;
            $criteria = isset($item['criteria']) ? $item['criteria'] : null;
            $merge = isset($item['merge']) ? $item['merge'] : false;
            $list[] = array($index, $name, $criteria, $merge, $parentIndex, $parentName);
            if (!empty($item['associations'])) {
                $this->searchFilter($item['associations'], $index, $name, $list);
            }
        }
    }

    private function sortOrder($associations, &$list)
    {
        foreach ($associations as $name => $item) {
            $index = isset($item['index']) ? $item['index'] : 0;
            $criteria = isset($item['criteria']) ? $item['criteria'] : null;
            $order = isset($item['order']) ? $item['order'] : 10;
            $list[$order][] = array($index, $name, $criteria);
            if (!empty($item['associations'])) {
                $this->sortOrder($item['associations'], $list);
            }
        }
    }

    private function orderSubqueryList($associations, $entityName)
    {
        $list = array();
        $this->sortOrder($associations, $list);
        ksort($list);
        foreach (array_keys($list) as $i) {
            if ($i > 10) {
                unset($list[$i]);
            } else if ($i === 10) {
                foreach (array_keys($list[$i]) as $j) {
                    if ($list[$i][$j][1] !== $entityName) {
                        unset($list[$i][$j]);
                    }
                }
            }
        }

        return $list;
    }

    private function cloneQuery(Query $query)
    {
        $cloneQuery = clone $query;

        $cloneQuery->setParameters(clone $query->getParameters());

        foreach ($query->getHints() as $name => $value) {
            $cloneQuery->setHint($name, $value);
        }

        return $cloneQuery;
    }
}
