<?php


namespace ES\Bundle\BaseBundle\LoadMore;

use Doctrine\ORM\Query;
use ES\Bundle\BaseBundle\LoadMore\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;

class LoadMore implements \IteratorAggregate
{
	const QUERY_PARAM = 'loadmore';

	/**
	 * @var AdapterInterface
	 */
	protected $adapter;

	/**
	 * @var array
	 */
	protected $unsetParams = [];

	/**
	 * @var int
	 */
	protected $maxPerPage;

	/**
	 * @var string
	 */
	protected $idField = 'id';

	/**
	 * @var string
	 */
	protected $dateField = 'createdAt';

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var boolean
	 */
	protected $reverse = false;

	protected $iterator;

	public function __construct(AdapterInterface $adapter, Request $request, $maxPerPage = 5)
	{
		$adapter->setIdField($this->idField);
		$adapter->setDateField($this->dateField);
		$this->adapter    = $adapter;
		$this->request    = $request;
		$this->maxPerPage = $maxPerPage;
	}

	/**
	 * @param int $maxPerPage
	 * @return $this
	 */
	public function setMaxPerPage($maxPerPage)
	{
		$this->maxPerPage = $maxPerPage;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getMaxPerPage()
	{
		return $this->maxPerPage;
	}

	/**
	 * @param string $idField
	 */
	public function setIdField($idField)
	{
		$this->idField = $idField;
		$this->adapter->setIdField($idField);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getIdField()
	{
		return $this->idField;
	}

	/**
	 * @param string $dateField
	 */
	public function setDateField($dateField)
	{
		$this->dateField = $dateField;
		$this->adapter->setDateField($dateField);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateField()
	{
		return $this->dateField;
	}

	/**
	 * @param boolean $reverse
	 */
	public function setReverse($reverse)
	{
		$this->reverse = $reverse;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getReverse()
	{
		return $this->reverse;
	}

	public function getMoreUri()
	{
		$lastValue = $this->getIterator()->getLastValue();
		$loadMore  = $this->request->query->get(self::QUERY_PARAM);
		foreach ($lastValue as $field => $value) {
			$loadMore[$field] = $value;
		}

		$params                    = $this->request->query->all();
		$params[self::QUERY_PARAM] = $loadMore;

		foreach ($this->unsetParams as $unsetParam) {
			if (array_key_exists($unsetParam, $params)) {
				unset($params[$unsetParam]);
			}
		}

		$requestUri = $this->request->getRequestUri();
		if (strpos($requestUri, '?') !== false) {
			list($path) = explode('?', $requestUri, 2);
			$requestUri = $path;
		}
		$request = Request::create($requestUri, 'GET', $params);

		return $request->getRequestUri();
	}

	public function hasMore()
	{
		$iterator = $this->getIterator();
		if (null === $iterator->getLastValue()) {
			foreach ($iterator as $row) {
				;
			}
		}

		return count($iterator) === $this->maxPerPage;
	}

	/**
	 * @return LoadMoreIterator
	 */
	public function getIterator()
	{
		if ($this->iterator) {
			return $this->iterator;
		}

		$loadMore = $this->request->query->get(self::QUERY_PARAM);
		$result   = $this->adapter->getResult($this->maxPerPage, $loadMore['id'], isset($loadMore['date']) ? $loadMore['date'] : null);

		$this->iterator = new LoadMoreIterator($result, $this->idField, $this->dateField, $this->reverse);

		return $this->iterator;
	}
}