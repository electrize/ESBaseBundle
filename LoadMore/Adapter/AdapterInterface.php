<?php

namespace ES\Bundle\BaseBundle\LoadMore\Adapter;

interface AdapterInterface
{
	/**
	 * Returns an slice of the results.
	 *
	 * @param integer $limit The limit.
	 * @param string  $idField
	 * @param string  $dateField optional
	 *
	 * @return array|\Traversable The slice.
	 */
	function getResult($limit, $idField, $dateField = null);

	/**
	 * Array containing the date (and id if needed) of the last item
	 *
	 * @return array ['id' => null|string, 'date' => \DateTime]
	 */
	function getLastValue();

	/**
	 * @param string $dateField
	 */
	function setDateField($dateField);

	/**
	 * @param string $idField
	 */
	function setIdField($idField);
}
