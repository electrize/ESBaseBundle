<?php
namespace ES\Bundle\BaseBundle\LoadMore\Query\SqlWalker;

use Doctrine\ORM\Query\AST;
use Doctrine\ORM\Query\TreeWalkerAdapter;
use ES\CameleonBundle\Util\Debug;

class LoadMoreWalker extends TreeWalkerAdapter
{
	const HINT_LOADMORE_CONDITIONS = 'LoadMore.conditions';

	public function walkSelectStatement(AST\SelectStatement $AST)
	{
		$parent     = null;
		$parentName = null;
		foreach ($this->_getQueryComponents() AS $dqlAlias => $qComp) {
			if (array_key_exists('parent', $qComp) && null === $qComp['parent'] && $qComp['nestingLevel'] === 0) {
				$parentName = $dqlAlias;
				break;
			}
		}

		if (!$AST->whereClause) {
			$AST->whereClause = new AST\WhereClause(null);
		}
		$whereClause   = $AST->whereClause;
		$orderByClause = $AST->orderByClause;
		$query         = $this->_getQuery();
		list($idField, $dateField, $conditions) = $query->getHint(self::HINT_LOADMORE_CONDITIONS);

		$factors = $orderBy = array();
		if ($whereClause->conditionalExpression) {
			$conditionalExpression = $whereClause->conditionalExpression;
			if ($conditionalExpression) {
				$primary                        = new AST\ConditionalPrimary();
				$primary->conditionalExpression = $conditionalExpression;
				$factors[]                      = $this->createConditionalPrimary(new AST\ConditionalExpression(array($primary)));
			}
		}

		$mapping = array(
			$idField => array(
				'conditionField' => 'id',
				'way'            => 'DESC',
				'strict'         => true,
			)
		);
		if (null !== $dateField) {
			$mapping[$dateField] = array(
				'conditionField' => 'date',
				'way'            => 'DESC',
				'strict'         => false,
				'date'           => true,
			);
		}

		foreach ($mapping as $field => $config) {
			$way                  = strtoupper($config['way']);
			$pathExpression       = new AST\PathExpression(
				AST\PathExpression::TYPE_STATE_FIELD, $parentName,
				$field
			);
			$pathExpression->type = AST\PathExpression::TYPE_STATE_FIELD;
			$conditionField       = $config['conditionField'];
			if (isset($conditions[$conditionField]) && null !== $conditions[$conditionField]) {
				$left                              = $pathExpression;
				$right                             = new AST\ArithmeticExpression();
				$right->simpleArithmeticExpression = new AST\InputParameter(':__' . $field);
				$operator                          = $way === 'ASC' ? '>' : '<';
				if (!$config['strict']) {
					$operator .= '=';
				}
				$factors[] = $this->createConditionalPrimary(new AST\ComparisonExpression($left, $operator, $right));
				if (isset($config['date']) && $config['date']) {
					$date = new \DateTime();
					$date->setTimestamp($conditions[$conditionField]);
					$conditions[$conditionField] = $date;
				}
				$query->setParameter('__' . $field, $conditions[$conditionField]);
			}

			$orderByItem       = new AST\OrderByItem($pathExpression);
			$orderByItem->type = $way;
			$orderBy[]         = $orderByItem;
		}

		if (count($orderBy)) {
			if ($orderByClause) {
				foreach ($orderBy as $o) {
					$orderByClause->orderByItems[] = $o;
				}
			} else {
				$AST->orderByClause = new AST\OrderByClause($orderBy);
			}
		}
		$whereClause->conditionalExpression = new AST\ConditionalExpression(array(new AST\ConditionalTerm($factors)));
	}

	protected function createConditionalPrimary(AST\Node $expression)
	{
		$primary                              = new AST\ConditionalPrimary();
		$primary->simpleConditionalExpression = $expression;

		return $primary;
	}
}