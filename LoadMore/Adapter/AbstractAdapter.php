<?php


namespace ES\Bundle\BaseBundle\LoadMore\Adapter;

abstract class AbstractAdapter implements AdapterInterface
{
	/**
	 * Array containing the date (and id if needed) of the last item
	 *
	 * @var array ['id' => null|string, 'date' => \DateTime]
	 */
	protected $lastValue;

	/**
	 * @var string
	 */
	protected $idField = 'id';

	/**
	 * @var string
	 */
	protected $dateField;

	/**
	 * @param string $dateField
	 */
	public function setDateField($dateField)
	{
		$this->dateField = $dateField;

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
	 * @param string $idField
	 */
	public function setIdField($idField)
	{
		$this->idField = $idField;

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
	 * @return array
	 */
	public function getLastValue()
	{
		return $this->lastValue;
	}
} 