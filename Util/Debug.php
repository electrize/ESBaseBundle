<?php

namespace ES\Bundle\BaseBundle\Util;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\Common\Util\ClassUtils;

class Debug
{
	public static function debug($title, $var, $maxDepth = 2, $stripTags = true)
	{
		echo '<pre style="background:#CCC;margin-bottom:5px;">';
		echo $title . '<br>';
		echo static::dump($var, $maxDepth, $stripTags);
		echo '</pre>';
	}

	/**
	 * Prints a dump of the public, protected and private properties of $var.
	 *
	 * @link http://xdebug.org/
	 *
	 * @param mixed $var         The variable to dump.
	 * @param integer $maxDepth  The maximum nesting level for object properties.
	 * @param boolean $stripTags Whether output should strip HTML tags.
	 *
	 * @return string
	 */
	public static function dump($var, $maxDepth = 2, $stripTags = true)
	{
		ini_set('html_errors', 'On');

		if (extension_loaded('xdebug')) {
			ini_set('xdebug.var_display_max_depth', $maxDepth);
		}

		$var = self::export($var, $maxDepth++);

		ob_start();
		var_dump($var);
		$dump = ob_get_contents();
		ob_end_clean();

		$result = $stripTags ? strip_tags(html_entity_decode($dump)) : $dump;

		ini_set('html_errors', 'Off');

		return $result;
	}

	/**
	 * @param mixed $var
	 * @param int $maxDepth
	 *
	 * @return mixed
	 */
	public static function export($var, $maxDepth)
	{
		$return = null;
		$isObj = is_object($var);

		if ($isObj && in_array('Doctrine\Common\Collections\Collection', class_implements($var))) {
			$var = $var->toArray();
		}

		if ($maxDepth) {
			if (is_array($var)) {
				$return = array();

				foreach ($var as $k => $v) {
					$return[$k] = self::export($v, $maxDepth - 1);
				}
			} else {
				if ($isObj) {
					$return = new \stdclass();
					if ($var instanceof \DateTime) {
						$return->__CLASS__ = "DateTime";
						$return->date = $var->format('c');
						$return->timezone = $var->getTimeZone()->getName();
					} else {
						$reflClass = ClassUtils::newReflectionObject($var);
						$return->__CLASS__ = ClassUtils::getClass($var);

						if ($var instanceof Proxy) {
							$return->__IS_PROXY__ = true;
							$return->__PROXY_INITIALIZED__ = $var->__isInitialized();
						}

						if ($var instanceof \ArrayObject || $var instanceof \ArrayIterator) {
							$return->__STORAGE__ = self::export($var->getArrayCopy(), $maxDepth - 1);
						}

						foreach ($reflClass->getProperties() as $reflProperty) {
							$name = $reflProperty->getName();

							$reflProperty->setAccessible(true);
							$return->$name = self::export($reflProperty->getValue($var), $maxDepth - 1);
						}
					}
				} else {
					$return = $var;
				}
			}
		} else {
			$return = is_object($var) ? get_class($var)
				: (is_array($var) ? 'Array(' . count($var) . ')' : $var);
		}

		return $return;
	}

	/**
	 * Returns a string representation of an object.
	 *
	 * @param object $obj
	 *
	 * @return string
	 */
	public static function toString($obj)
	{
		return method_exists($obj, '__toString') ? (string)$obj : get_class($obj) . '@' . spl_object_hash($obj);
	}
} 