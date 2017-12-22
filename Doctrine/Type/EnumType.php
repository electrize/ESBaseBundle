<?php

namespace ES\Bundle\BaseBundle\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class EnumType extends Type
{
	const ENUM = 'enum';

	/**
	 * @return array
	 */
	static public function getValues()
	{
		return array();
	}

	public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return sprintf('ENUM("%s") COMMENT "(DC2Type:%s)"',
			implode('","', $this->getValues()),
			$this->getName()
		);
	}

	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return $value;
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		$values = $this->getValues();
		if (!in_array($value, $values)) {
			throw new \InvalidArgumentException(sprintf('Invalid enum value "%s". Available values are ("%s")',
				$value,
				implode('", "', $values)
			));
		}

		return $value;
	}
} 