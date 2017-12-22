<?php

namespace ES\Bundle\BaseBundle\LoadMore;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class LoadMoreIterator extends \ArrayIterator
{
	/**
	 * @var array
	 */
	private $lastValue;

	/**
	 * @var string
	 */
	private $idField;

	/**
	 * @var string
	 */
	private $dateField;

	private $reverse;

	/**
	 * @var PropertyAccessorInterface
	 */
	protected $propertyAccessor;

	public function __construct($array, $idField = 'id', $dateField = 'createdAt', $reverse = false, $flags = 0)
	{
		$this->propertyAccessor = PropertyAccess::createPropertyAccessor();
		$this->reverse          = $reverse;
		if ($reverse) {
			$array = array_reverse($array);
		}
		parent::__construct($array, $flags);
		$this->idField   = $idField;
		$this->dateField = $dateField;
	}

	/**
	 * @return array
	 */
	public function getLastValue()
	{
		return $this->lastValue;
	}

	public function current()
	{
		$current = parent::current();

		$value = [
			'id' => $this->propertyAccessor->getValue($current, $this->idField)
		];
		if ($this->dateField) {
			$value['date'] = $this->propertyAccessor->getValue($current, $this->dateField);
			if ($value['date'] instanceof \DateTime) {
				$value['date'] = $value['date']->getTimestamp();
			}
		}

		if ($this->reverse) {
			if (null === $this->lastValue) {
				$this->lastValue = $value;
			}
		} else {
			if (null !== $this->lastValue) {
				if ($this->lastValue['id'] < $value['id']) {
					throw new \LogicException(sprintf('LoadMore: Next "%s" value should be less than "%s". Got "%s"', $this->idField, $this->lastValue['id'], $value['id']));
				}
				if ($this->dateField) {
					if ($this->lastValue['date'] < $value['date']) {
						throw new \LogicException(sprintf('LoadMore: Next "%s" value should be less than "%s". Got "%s"', $this->dateField, $this->lastValue['date'], $value['date']));
					}
				}
			}
			$this->lastValue = $value;
		}

		return $current;
	}
}