<?php

namespace LibBundle\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\Common\Collections\Expr\ExpressionVisitor;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Value;

use Doctrine\ORM\Query\Parameter;

class QueryExpressionVisitor extends ExpressionVisitor
{
    private static $operatorMap = array(
        Comparison::GT => \Doctrine\ORM\Query\Expr\Comparison::GT,
        Comparison::GTE => \Doctrine\ORM\Query\Expr\Comparison::GTE,
        Comparison::LT  => \Doctrine\ORM\Query\Expr\Comparison::LT,
        Comparison::LTE => \Doctrine\ORM\Query\Expr\Comparison::LTE
    );

    private $rootAlias;
    private $expr;
    private $parameters = array();

    public function __construct($rootAlias)
    {
        $this->rootAlias = $rootAlias;
        $this->expr = new \Doctrine\ORM\Query\Expr();
    }

    public function getParameters()
    {
        return new ArrayCollection($this->parameters);
    }

    public function clearParameters()
    {
        $this->parameters = array();
    }

    private static function convertComparisonOperator($criteriaOperator)
    {
        return isset(self::$operatorMap[$criteriaOperator]) ? self::$operatorMap[$criteriaOperator] : null;
    }

    public function walkCompositeExpression(CompositeExpression $expr)
    {
        $expressionList = array();

        foreach ($expr->getExpressionList() as $child) {
            $expressionList[] = $this->dispatch($child);
        }

        switch($expr->getType()) {
            case CompositeExpression::TYPE_AND:
                return new \Doctrine\ORM\Query\Expr\Andx($expressionList);

            case CompositeExpression::TYPE_OR:
                return new \Doctrine\ORM\Query\Expr\Orx($expressionList);

            default:
                throw new \RuntimeException("Unknown composite " . $expr->getType());
        }
    }

    public function walkComparison(Comparison $comparison)
    {
        $field = str_replace('{}', $this->rootAlias, $comparison->getField());
        $expression = ($field[0] === ':') ? trim($field, ':') : preg_replace('/\b([a-z]\w*)\b/', $this->rootAlias . '.$0', $field);
        $parameterName = trim(preg_replace('/[^\w]/', '_', $expression) . count($this->parameters), '_');
        $parameter = new Parameter($parameterName, $this->walkValue($comparison->getValue()));
        $placeholder = ':' . $parameterName;

        switch ($comparison->getOperator()) {
            case Comparison::IN:
                $this->parameters[] = $parameter;
                return $this->expr->in($expression, $placeholder);

            case Comparison::NIN:
                $this->parameters[] = $parameter;
                return $this->expr->notIn($expression, $placeholder);

            case Comparison::EQ:
            case Comparison::IS:
                if ($this->walkValue($comparison->getValue()) === null) {
                    return $this->expr->isNull($expression);
                }
                $this->parameters[] = $parameter;
                return $this->expr->eq($expression, $placeholder);

            case Comparison::NEQ:
                if ($this->walkValue($comparison->getValue()) === null) {
                    return $this->expr->isNotNull($expression);
                }
                $this->parameters[] = $parameter;
                return $this->expr->neq($expression, $placeholder);

            case \LibBundle\Doctrine\Comparison::CONTAINS:
                $parameter->setValue('%' . $parameter->getValue() . '%', $parameter->getType());
                $this->parameters[] = $parameter;
                return $this->expr->like($expression, $placeholder);

            case \LibBundle\Doctrine\Comparison::NCONTAINS:
                $parameter->setValue('%' . $parameter->getValue() . '%', $parameter->getType());
                $this->parameters[] = $parameter;
                return $this->expr->notLike($expression, $placeholder);

            case \LibBundle\Doctrine\Comparison::STARTS_WITH:
                $parameter->setValue($parameter->getValue() . '%', $parameter->getType());
                $this->parameters[] = $parameter;
                return $this->expr->like($expression, $placeholder);

            case \LibBundle\Doctrine\Comparison::N_STARTS_WITH:
                $parameter->setValue($parameter->getValue() . '%', $parameter->getType());
                $this->parameters[] = $parameter;
                return $this->expr->notLike($expression, $placeholder);

            case \LibBundle\Doctrine\Comparison::ENDS_WITH:
                $parameter->setValue('%' . $parameter->getValue(), $parameter->getType());
                $this->parameters[] = $parameter;
                return $this->expr->like($expression, $placeholder);

            case \LibBundle\Doctrine\Comparison::N_ENDS_WITH:
                $parameter->setValue('%' . $parameter->getValue(), $parameter->getType());
                $this->parameters[] = $parameter;
                return $this->expr->notLike($expression, $placeholder);

            default:
                $operator = self::convertComparisonOperator($comparison->getOperator());
                if ($operator) {
                    $this->parameters[] = $parameter;
                    return new \Doctrine\ORM\Query\Expr\Comparison($expression, $operator, $placeholder);
                }

                throw new \RuntimeException("Unknown comparison operator: " . $comparison->getOperator());
        }
    }

    public function walkValue(Value $value)
    {
        return $value->getValue();
    }
}
