<?php


namespace ES\Bundle\BaseBundle\Mapper;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Proxy;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Util\ClassUtils;

class ObjectMapper
{
	/**
	 * @var array
	 */
	protected $mapping;

	function __construct(array $mapping)
	{
		$this->mapping = $mapping;
	}

	/**
	 * @param string $objectKey
	 * @return string The FQCN
	 * @throws \InvalidArgumentException
	 */
	public function getClassName($objectKey)
	{
		if (!($className = array_search($objectKey, $this->mapping))) {
			throw new \InvalidArgumentException(sprintf('Undefined object "%s" in the object mapping', $objectKey));
		}

		return $className;
	}

	/**
	 * @param object $object An instance of object
	 * @return string the object key
	 * @throws \InvalidArgumentException
	 * @throws \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
	 */
	public function getObjectKey($object)
	{
		if (!is_object($object)) {
			throw new UnexpectedTypeException($object, 'object');
		}

		$objectClass = ClassUtils::getRealClass($object);
		if (!isset($this->mapping[$objectClass])) {
			throw new \InvalidArgumentException(sprintf('Class "%s" is not defined in the object mapping', $objectClass));
		}

		return $this->mapping[$objectClass];
	}
} 