<?php


namespace ES\Bundle\BaseBundle\LoadMore\Adapter;

use Symfony\Component\PropertyAccess\PropertyAccess;

class ArrayAdapter extends AbstractAdapter
{
	/**
	 * @var array
	 */
	private $array;

	function __construct(array $array)
	{
		$this->array = $array;
	}

	function getResult($limit, $date, $id = null)
	{
		$propertyAccessor = PropertyAccess::createPropertyAccessor();

		return array_slice(array_filter($this->array, function ($item) use ($propertyAccessor, $date, $id) {
			if ($propertyAccessor->getValue($item, $this->dateField) < $date
				&& (null === $id || $propertyAccessor->getValue($item, $this->idField) < $id)
			) {
				$this->lastValue = [
					$this->dateField => $date,
					$this->idField   => $id,
				];

				return true;
			}

			return false;
		}), 0, $limit);
	}
}