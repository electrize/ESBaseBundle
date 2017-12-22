<?php


namespace ES\Bundle\BaseBundle\Doctrine\Mapping;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

abstract class AbstractMappingListener implements EventSubscriber
{
	public function getSubscribedEvents()
	{
		return array(
			Events::loadClassMetadata,
		);
	}

	abstract public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs);

	protected function setConcrete(ClassMetadataInfo $metadata, $tableName)
	{
		$metadata->setPrimaryTable(array(
			'name' => $tableName,
		));
		$metadata->isMappedSuperclass = false;
	}
}