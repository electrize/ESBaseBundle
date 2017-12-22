<?php

namespace ES\Bundle\BaseBundle\Translation;

interface FallbackTranslatorInterface
{
	/**
	 * @param array  $keys
	 * @param array  $params
	 * @param string $domain
	 * @param string $locale
	 * @return string
	 */
	public function translate(array $keys, array $params = array(), $domain = null, $locale = null);

	/**
	 * @param array  $keys
	 * @param   int  $count
	 * @param array  $params
	 * @param string $domain
	 * @param string $locale
	 * @return string
	 */
	public function translateChoice(array $keys, $count, array $params = array(), $domain = null, $locale = null);
} 