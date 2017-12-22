<?php


namespace ES\Bundle\BaseBundle\LoadMore\Adapter;

use Doctrine\ORM\Query;
use ES\Bundle\BaseBundle\LoadMore\Query\SqlWalker\LoadMoreWalker;

class DoctrineORMAdapter extends AbstractAdapter
{
	/**
	 * @var \Doctrine\ORM\Query
	 */
	private $query;

	function __construct(Query $query)
	{
		$this->query = $query;
	}

	function getResult($limit, $id, $date = null)
	{
		$conditions = [];

		$conditions[$this->idField] = $id;
		if ($this->dateField) {
			$conditions[$this->dateField] = $date;
		}

		$query = $this->cloneQuery($this->query)
			->setMaxResults($limit)
			->setHint(Query::HINT_CUSTOM_TREE_WALKERS, array('ES\Bundle\BaseBundle\LoadMore\Query\SqlWalker\LoadMoreWalker'))
			->setHint(LoadMoreWalker::HINT_LOADMORE_CONDITIONS, array($this->idField, $this->dateField, $conditions))
			->useQueryCache(false);

		return $query->getResult($query->getHydrationMode());

		return $iterator;
	}

	private function cloneQuery(Query $query)
	{
		/* @var $cloneQuery Query */
		$cloneQuery = clone $query;

		$cloneQuery->setParameters(clone $query->getParameters());
		foreach ($query->getHints() as $name => $value) {
			$cloneQuery->setHint($name, $value);
		}

		return $cloneQuery;
	}
}