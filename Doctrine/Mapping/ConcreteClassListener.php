<?php


namespace ES\Bundle\BaseBundle\Doctrine\Mapping;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ConcreteClassListener extends AbstractMappingListener
{
	protected $class;
	protected $table;

	/**
	 * @param string $class
	 * @param string $table
	 */
	function __construct($class, $table)
	{
		$this->class = $class;
		$this->table = $table;
	}

	public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
	{
		/** @var ClassMetadataInfo $metadata */
		$metadata = $eventArgs->getClassMetadata();

		$className = $metadata->getName();
		if ($className === $this->class) {
			$this->setConcrete($metadata, $this->table);
		}
	}
}